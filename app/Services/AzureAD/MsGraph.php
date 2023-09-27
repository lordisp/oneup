<?php

namespace App\Services\AzureAD;

use App\Exceptions\MsGraphException;
use App\Traits\HttpRetryConditions;
use App\Traits\Token;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MsGraph
{
    use Token, HttpRetryConditions;

    protected array $props = [];
    protected array $query;

    public function __construct()
    {
        $this->props = [
            'endpoint' => 'v1.0',
            'select' => [],
            'filter' => [],
            'top' => null,
            'all' => false,
            'path' => '',
            'body' => [],
            'provider' => 'lhg_graph',
            'headers' => [],
        ];
        $this->query = [];
    }

    /**
     * @param string $path
     * @return MsGraph
     * @discription The GET method requests a representation of the specified resource. Requests using GET should only retrieve data.
     */
    public static function get(string $path): MsGraph
    {
        return (new static)->request('GET', $path);
    }

    /**
     * @param string $path
     * @param array $body
     * @return MsGraph
     * @discription The POST method is used to submit an entity to the specified resource, often causing a change in state or side effects on the server.
     */
    public static function post(string $path, array $body = []): MsGraph
    {
        return (new static)->request('POST', $path, $body);
    }

    /**
     * @param string $path
     * @param array $body
     * @return MsGraph
     * @discription The PUT method replaces all current representations of the target resource with the request payload.
     */
    public static function put(string $path, array $body = []): MsGraph
    {
        return (new static)->request('PUT', $path, $body);
    }

    /**
     * @param string $path
     * @param array $body
     * @return MsGraph
     * @discription The PATCH method is used to apply partial modifications to a resource.
     */
    public static function patch(string $path, array $body = []): MsGraph
    {
        return (new static)->request('PATCH', $path, $body);
    }

    /**
     * @param string $path
     * @param array $body
     * @return MsGraph
     * @discription The DELETE method deletes the specified resource.
     */
    public static function delete(string $path, array $body = []): MsGraph
    {
        return (new static)->request('DELETE', $path, $body);
    }

    /**
     * @param string $provider
     * @return $this
     * @discription The provider is the name of the token in the database
     */
    public function provider(string $provider): static
    {
        $this->props['provider'] = $provider;
        return $this;
    }

    /**
     * @return $this
     * @discription The beta method is used to change the endpoint to the beta version, by default the endpoint is v1.0
     * @see https://learn.microsoft.com/en-us/graph/api/overview?view=graph-rest-beta
     */
    public function beta(): static
    {
        $this->props['endpoint'] = 'beta';
        return $this;
    }

    /**
     * @param ...$fields
     * @return $this
     * @discription The select method is used to select the fields to be returned in the response
     * @see https://docs.microsoft.com/en-us/graph/query-parameters#select-parameter
     */
    public function select(...$fields): static
    {
        $this->props['select'] = $fields;
        return $this;
    }

    /**
     * @param int $top
     * @return $this
     * @discription The top method is used to limit the number of records to be returned, the maximum value is 999
     * @see https://docs.microsoft.com/en-us/graph/query-parameters#top-parameter
     */
    public function top(int $top): static
    {
        $this->props['top'] = $top;
        return $this;
    }

    /**
     * @param string $filter
     * @param string $operator
     * @return $this
     * @discription The filter method is used to filter the records to be returned, the default operator is 'and'. The filter parameter must be a valid OData filter expression. For more information, see OData query parameters.
     * @see https://docs.microsoft.com/en-us/graph/query-parameters#filter-parameter
     */
    public function filter(string $filter, string $operator = 'and'): static
    {
        $this->props['filter'][] = [$operator => $filter];
        return $this;
    }

    /**
     * @param string $property
     * @param string $searchString
     * @return $this
     * @discription The search method is used to search for a string in a property. The search parameter must be a valid search expression. For more information, see OData query parameters.
     * @see https://docs.microsoft.com/en-us/graph/query-parameters#search-parameter
     */
    public function search(string $property, string $searchString): static
    {
        $this->query['$search'] = "\"{$property}:{$searchString}\"";
        return $this;
    }

    /**
     * @param $method
     * @param $path
     * @param array $body
     * @return $this
     * @discription The request method is used to set the method, path and body of the request
     */
    protected function request($method, $path, array $body = []): static
    {
        $this->props['method'] = $method;
        $this->props['path'] = $path;
        $this->props['$body'] = $body;
        return $this;
    }

    /**
     * @param int $take
     * @return $this
     * ©description $take is the maximum number of records to return. If $take is 0, all records will be returned
     */
    public function all(int $take = 0): static
    {
        $this->props['take'] = $take;
        $this->props['all'] = true;

        return $this;
    }

    /**
     * @return array
     * @discription The call method is used to make the request to the Microsoft Graph API. The call method returns an array with the results of the request, if the all method is used, the results will be paginated.
     * @throws MsGraphException
     */
    public function call(): array
    {
        $results = $this->callApi()->json();
        $skipToken = $this->nextLink($results);

        $all = data_get($this->props, 'all', false);
        $take = data_get($this->props, 'take', 0);

        // Loop to get all results if 'all' property is set to true and
        // we either have no limit on 'take' or the limit is not reached yet.
        while ($all && !empty($skipToken)) {
            $currentCount = count($results['value']);

            // Break the loop if the number of results exceeds or matches the 'take' limit.
            if ($take !== 0 && $take <= $currentCount) {
                break;
            }

            $result = $this->callApi($skipToken)->json();
            $results['value'] = array_merge($results['value'], $result['value']);

            $skipToken = $this->nextLink($result);
        }

        // If the number of results exceeds 'take', remove the excess values from the end.
        if ($take > 0 && count($results['value']) > $take) {
            $excess = count($results['value']) - $take;
            $results['value'] = array_slice($results['value'], 0, -$excess);
        }

        unset($results['@odata.nextLink']);
        return $results;
    }

    /**
     * @throws MsGraphException
     */
    public function body()
    {
        return $this->callApi()->body();
    }

    /**
     * @param array $results
     * @return string
     * @discription The nextLink method is used to get the next link of the results, if the results are paginated. The next link is used to get the next page of results.
     * @see https://docs.microsoft.com/en-us/graph/paging
     */
    protected function nextLink(array $results): string
    {
        $skipToken = '';

        if (Arr::has($results, '@odata.nextLink')) {
            $url = parse_url($results['@odata.nextLink'], PHP_URL_QUERY);

            parse_str($url, $output);

            $skipToken = data_get($output, '$skiptoken') ?: data_get($output, '$skipToken');
        }

        return (string)$skipToken;
    }

    /**
     * @param null $skipToken
     * @return mixed
     * @throws MsGraphException
     * @discription The callApi method is used to make the request to the Microsoft Graph API. The callApi method returns an array with the results of the request.
     */
    public function callApi($skipToken = null): mixed
    {
        $apiProperties = $this->getApiProperties($this->props);

        $this->handleSkipToken($skipToken);

        $this->setConsistencyLevel();

        $this->setCount();

        $apiProperties['endpoint'] = sprintf("https://graph.microsoft.com/%s", $apiProperties['endpoint']);

        return Http::withToken(decrypt($this->token($this->props['provider'])))
            ->withBody(data_get($apiProperties, 'body'))
            ->withHeaders(data_get($this->props, 'headers'))
            ->retry(20, 0, function ($exception, $request) {
                $this->handleRequestExceptionConditions($exception, MsGraphException::class);
                return $this->handleRetryConditions($exception, $request, $this->props['provider']);
            }, throw: false)
            ->{$apiProperties['method']}($apiProperties['endpoint'] . $apiProperties['path'], $this->query)
            ->onError(function ($response) {
                if ($response->status() != 404)
                    throw new MsGraphException($response->json()['error']['message'], $response->status());
            });
    }

    /**
     * @return void
     * @discription The setConsistencyLevel method is used to set the ConsistencyLevel header to eventual if the filter parameter contains the endsWith operator.
     * @see https://docs.microsoft.com/en-us/graph/query-parameters#consistencylevel
     */
    protected function setConsistencyLevel(): void
    {
        if ($this->filterRequiresEventualConsistencyLevel()) {
            $this->props['headers'] = [
                'ConsistencyLevel' => 'eventual'
            ];
        }

        if (Arr::has($this->query, '$search')) {
            $this->props['headers'] = [
                'ConsistencyLevel' => 'eventual'
            ];
        }
    }

    /**
     * @return void
     * @discription The setCount method is used to set the $count query parameter to true if the filter parameter contains the endsWith operator.
     * @see https://docs.microsoft.com/en-us/graph/query-parameters#count-parameter
     */
    protected function setCount(): void
    {
        if ($this->filterRequiresEventualConsistencyLevel()) {
            $this->query['$count'] = 'true';
        };

        if (Arr::has($this->query, '$search')) {
            $this->query['$count'] = 'true';
        }
    }

    /**
     * @param $props
     * @return array
     * @discription The validateRequest method is used to validate the request parameters.
     */
    protected function validateRequest($props): array
    {
        return Validator::validate($props, [
            'method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
            'select' => 'nullable|array',
            'filter' => 'nullable|array',
            'top' => 'nullable|min:1|max:999|integer',
            'endpoint' => 'in:v1.0,beta',
            'path' => 'regex:/^\/[A-Za-z0-9\-_@.]+(\/[A-Za-z0-9\-_@.x$]+)*$/'
        ],
            [
                'method.required' => 'The method is required',
                'method.in' => 'The method must be one of the following types: GET, POST, PUT, PATCH or DELETE',
                'select.array' => 'The select parameter must be an array',
                'filter.array' => 'The filter parameter must be an array',
                'endpoint.in' => 'The endpoint must be one of the following types: v1.0 or beta',
                'path.regex' => 'The path parameter must be a valid path (e.g. /users/1f4db4e4-93c9-4f58-b060-6757b2e621a3/resetPassword, /users/rafael.camison@austrian.com)'
            ]);
    }

    /**
     * @return bool
     * @discription The filterRequiresEventualConsistencyLevel method is used to check if the filter parameter contains the endsWith operator.
     */
    protected function filterRequiresEventualConsistencyLevel(): bool
    {
        if (is_array(data_get($this->props, 'filter'))) {
            $eventual[] = Arr::where(data_get($this->props, 'filter'), fn($value) => Str::contains(Str::lower($value), 'endswith'));

            if (!empty(Arr::flatten($eventual))) return true;
        }

        if (is_string(data_get($this->props, 'filter'))) {
            $eventual = Str::contains(Str::lower(data_get($this->props, 'filter')), 'endswith');

            if ($eventual) return true;
        }

        return false;
    }

    /**
     * @param array $props
     * @return array
     * @discription The getApiProperties method is used to get the properties to be sent to the Microsoft Graph API.
     * @see https://docs.microsoft.com/en-us/graph/query-parameters
     */
    protected function getApiProperties(array $props): array
    {
        $props = $this->validateRequest($props);
        if (!empty($props['select'])) {
            $this->query['$select'] = implode(',', $props['select']);
        }

        if ($props['top']) {
            $this->query['$top'] = $props['top'];
        }

        if ($props['filter']) {

            $this->query['$filter'] = '';

            foreach ($props['filter'] as $item) {
                foreach ($item as $operator => $condition) {
                    if ($this->query['$filter'] != '') {
                        $this->query['$filter'] .= ' ' . $operator . ' ';
                    }
                    $this->query['$filter'] .= $condition;
                }
            }
            $this->props['filter'] = $this->query['$filter'];
        }
        return $props;
    }

    /**
     * @param string|null $skipToken
     * @return void
     * @discription The handleSkipToken method is used to set the $skipToken query parameter.
     * @see https://docs.microsoft.com/en-us/graph/paging
     */
    protected function handleSkipToken(null|string $skipToken): void
    {
        if ($skipToken) {
            $this->query['$skipToken'] = $skipToken;
        }
    }


}