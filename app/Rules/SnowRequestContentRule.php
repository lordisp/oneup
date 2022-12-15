<?php

namespace App\Rules;

use App\Traits\ValidationRules;
use Arr;
use Illuminate\Contracts\Validation\InvokableRule;

class SnowRequestContentRule implements InvokableRule
{
    use ValidationRules;

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        $failed = $this->preValidateFirewallRequestFiles(json_decode(file_get_contents($value->path()), true));
        $failed = $failed ?? [['error']];
        $count = count($failed);

        foreach ($failed as $error) {
            $firstError = Arr::first($error);
        }

        $message = $firstError ?? 'Invalid format.';

        if ($count > 1) {
            $count = $count - 1;
            $message = rtrim($message, '.');
            $message .= " and $count other attributes.";
        }

        if ($count > 0) $fail(is_array($message) ? Arr::first($message) : $message);
    }
}
