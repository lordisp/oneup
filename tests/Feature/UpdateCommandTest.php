<?php

namespace Tests\Feature;

use Tests\TestCase;

class UpdateCommandTest extends TestCase
{
    /** @test */
    public function it_runs_container_os_updates()
    {
        $this->artisan('update:run')->assertExitCode(0);
    }
}