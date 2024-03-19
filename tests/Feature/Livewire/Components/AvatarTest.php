<?php

namespace Tests\Feature\Livewire\Components;

use App\Http\Livewire\Components\Avatar;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TokenCacheProviderSeeder::class]);
    }

    public function testBasic(): void
    {
        $component = Livewire::test(Avatar::class);

        $component->assertStatus(200);
    }

    /** @test */
    public function it_loads_the_avatar(): void
    {
        Livewire::test(Avatar::class)
            ->call('loadAvatar')
            ->assertSet('isLoaded', true);
    }

    /** @test */
    public function it_returns_the_avatar_from_a_given_user(): void
    {
        Livewire::test(Avatar::class, ['userId' => 'rafael.camison@austrian.com', 'alt' => 'Camison, Rafael'])
            ->call('getAvatarProperty')
            ->assertSee('data:image/jpeg;base64,');
    }

    /** @test */
    public function it_returns_the_fallback_avatar_from_a_given_user(): void
    {
        Livewire::test(Avatar::class, ['userId' => 'A300250@dlh.de', 'alt' => 'ONEUP INTEGRATION TESTER'])
            ->call('getAvatarProperty')
            ->assertDontSee('data:image/jpeg;base64,');
    }

    /** @test
     * @throws \ReflectionException
     */
    public function it_fetches_fallback_avatar_when_no_user_id_is_set(): void
    {
        \cache()->flush();
        $avatarComponent = new Avatar();
        $method = (new \ReflectionClass($avatarComponent))->getMethod('callAvatarApi');
        $method->setAccessible(true);

        $avatarURL = $method->invoke($avatarComponent);

        $this->assertStringContainsString('https://ui-avatars.com/api/', $avatarURL);
    }
}
