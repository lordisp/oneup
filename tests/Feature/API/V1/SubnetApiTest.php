<?php

namespace Tests\Feature\API\V1;

use App\Http\Resources\V1\SubnetResource;
use App\Models\Subnet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper;
use Tests\TestCase;

class SubnetApiTest extends TestCase
{
    use RefreshDatabase, Helper;

    /** @test */
    public function can_create_a_new_subnet()
    {
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-create');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );

        $subnetData = [
            'name' => '10.0.0.0',
            'size' => 24,
            'pci_dss' => now()->toDateTimeString(),
        ];

        $response = $this->withToken($token)->post('/api/v1/subnets', $subnetData);

        $response->assertStatus(201);

        $subnet = Subnet::first();

        $this->assertEquals($subnetData['name'], $subnet->name);
        $this->assertEquals($subnetData['size'], $subnet->size);
        $this->assertEquals('10000', $subnet->slug);
        $this->assertEquals($subnetData['pci_dss'], $subnet->pci_dss);

        $subnetResource = new SubnetResource($subnet);

        // Assert
        $response->assertExactJson([
            'data' => $subnetResource->toArray(null),
        ]);
    }


    /** @test */
    public function creating_new_subnets_fail_with_an_invalid_scope()
    {
        // Arrange
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-read');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );

        // Act
        $response = $this->withToken($token)->post('/api/v1/subnets');

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function can_update_a_subnet()
    {
        // Arrange
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-update');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );
        $subnet = Subnet::factory()->state([
            'name' => '10.0.2.0',
            'size' => 24,
            'pci_dss' => now()->toDateTimeString(),
        ])->create();

        $updatedSubnet = [
            'name' => '10.0.0.0',
            'size' => 24,
            'pci_dss' => now()->addHour()->toDateTimeString(),
        ];

        // Act
        $response = $this->withToken($token)->put("/api/v1/subnets/{$subnet->id}", $updatedSubnet);
        $response->assertStatus(200);
        $subnet = $subnet->fresh();

        // Assert
        $this->assertEquals($updatedSubnet['name'], $subnet->name);
        $this->assertEquals('10000', $subnet->slug);
        $this->assertEquals($updatedSubnet['size'], $subnet->size);
        $this->assertEquals($updatedSubnet['pci_dss'], $subnet->pci_dss);

        $subnetResource = new SubnetResource($subnet);
        $response->assertExactJson([
            'data' => $subnetResource->toArray(null),
        ]);

    }

    /** @test */
    public function updating_new_subnets_fail_with_an_invalid_scope()
    {
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-read');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );

        $subnet = Subnet::factory()->state([
            'name' => '10.0.2.0',
            'size' => 24,
            'pci_dss' => now()->toDateTimeString(),
        ])->create();

        $response = $this->withToken($token)->put("/api/v1/subnets/{$subnet->id}", $subnet->toArray());

        $response->assertStatus(403);
    }

    /** @test */
    public function can_delete_a_subnet()
    {
        // Arrange
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-delete');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );

        $subnet = Subnet::factory()->create();

        // Act
        $response = $this->withToken($token)->delete("/api/v1/subnets/{$subnet->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertNull(Subnet::find($subnet->id));
    }

    /** @test */
    public function deleting_a_subnet_fails_with_an_invalid_scope()
    {
        // Arrange
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-read');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );

        $subnet = Subnet::factory()->create();

        // Act
        $response = $this->withToken($token)->delete("/api/v1/subnets/{$subnet->id}");

        $response->assertStatus(403);
        // Assert
        $this->assertDatabaseCount(Subnet::class, 1);
    }

    /** @test */
    public function can_list_all_subnets()
    {
        // Arrange
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-read');

        $token = $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );

        Subnet::factory()->count(11)->create();

        // Act
        $first = $this->withToken($token)->get("/api/v1/subnets");

        // Assert
        $this->assertCount(10, $first->json('data'));
        $this->assertEquals(config('app.url') . '/api/v1/subnets?page=1', $first->json('links')['first']);
        $this->assertEquals(config('app.url') . '/api/v1/subnets?page=2', $first->json('links')['last']);
        $this->assertEquals(config('app.url') . '/api/v1/subnets?page=2', $first->json('links')['next']);
        $this->assertNull($first->json('links')['prev']);
    }

}
