<?php

namespace App\Jobs;

use App\Models\Audit;
use App\Models\ServiceNowRequest;
use App\Models\User;
use App\Notifications\DeveloperNotification;
use App\Notifications\UserActionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ServiceNowDeleteAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public User $user, protected $developers = null)
    {
        $this->getDevelopers();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $all = ServiceNowRequest::all();
        if ($all->count() > 0) {
            $all->map->delete();

            Audit::where('auditable_type', \App\Models\FirewallRule::class)
                ->delete();
            Log::info($this->user->email.' Deleted all Firewall-Rule Records');
            $message = [
                'titel' => 'Action completed',
                'message' => __('messages.all_requests_deleted'),
            ];
        } else {
            $message = [
                'titel' => 'Nothing to do',
                'message' => 'There were no records to delete.',
            ];
        }
        $this->user->notify(new UserActionNotification($message['titel'], $message['message']));

    }

    public function failed($exception)
    {
        Log::error('Failed to delete ServiceNowRequests', (array) $exception);
        foreach ($this->developers as $developer) {
            $developer->notify(new DeveloperNotification($exception));
        }
    }

    protected function getDevelopers()
    {
        $this->developers = User::whereRelation('groups', 'name', '=', 'Developers')->get();
    }
}
