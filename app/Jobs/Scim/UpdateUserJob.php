<?php

namespace App\Jobs\Scim;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $member;
    public string $provider;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member, $provider)
    {
        $this->member = $member;
        $this->provider = $provider;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->validEmailAddress($this->member);

        $user = User::where('email', $email)->first();

        if (!isset($user) || $user === false) Log::error('Failed to update user ' . $this->member['id'], $this->member); else {
            $user->status = false;
            $user->save();
            Log::debug('Update or create user ' . $this->member['id']);
        }
    }

    protected function validEmailAddress(array $member)
    {
        foreach ($member as $item) {
            if (!str_contains($item, 'onmicrosoft') && filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
                $value = $item;
            }
        }
        if (empty($value)) Log::error('Scim: Email validation failed for user-import', $member); else {
            return $value;
        }
    }
}
