<?php

namespace App\Rules;

use App\Traits\ValidationRules;
use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Arr;
use JsonException;

class FirewallRequestsRule implements InvokableRule
{
    use ValidationRules;

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $attachments
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $attachments, $fail): void
    {
        $contents = $this->getContents($attachments);

        if (is_string($contents)) {

            $rows = $this->getRows($contents, $fail, $attribute);

            foreach ($rows as $row) {
                if (! Arr::has((array) $row, [
                    'RITMNumber', 'Subject',
                    'opened_by', 'tag', 'rules',
                ])) {
                    $this->extracted($fail, $attribute, 'is missing mandatory attributes.');
                }
            }
        }
    }

    protected function getFileNumber(string $attribute): int
    {
        return (int) substr($attribute, strpos($attribute, '.') + 1) + 1;
    }

    protected function getContents(mixed $attachments): string|false
    {
        return file_get_contents($attachments->path());
    }

    protected function getRows(string $contents, Closure $fail, string $attribute): mixed
    {
        try {
            $rows = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->extracted($fail, $attribute, 'has an invalid file format.');
        }

        return $rows;
    }

    protected function extracted(Closure $fail, string $attribute, string $message): void
    {
        $fail("Attachment {$this->getFileNumber($attribute)} {$message}");
    }
}
