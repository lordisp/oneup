<?php

namespace Tests\Feature\Ui\Admin;

use Tests\TestCase;

class GroupsEditTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
