<?php

namespace App\Jobs\Scim;

use App\Services\Scim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportUserJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Scim $scim;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->scim = new Scim();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->scim->provider('oneup_graph')->groups('64a289f8-7430-40b4-830f-f64ffd6452fc');
    }
}
