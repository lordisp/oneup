<?php

namespace App\Http\Livewire\Components;

use App\Exceptions\MsGraphException;
use App\Services\AzureAD\MsGraph;
use App\Traits\HttpRetryConditions;
use App\Traits\Token;
use Illuminate\Support\Str;
use Livewire\Component;

class Avatar extends Component
{
    use Token, HttpRetryConditions;

    const PROVIDER = 'lhg_graph';

    public $userId;
    public string $class = '', $alt = '', $size = '48x48';
    public bool $isLoaded = false, $noCache = false;

    public function loadAvatar(): void
    {
        $this->isLoaded = true;
    }

    /**
     * Get the avatar image
     *
     * @return string
     */
    public function getAvatarProperty(): string
    {
        if (!$this->noCache) {
            $avatar = cache()->tags([$this->userId])->get('avatar');

            if (is_string($avatar)) {
                return $avatar;
            }
        }

        $avatar = $this->callAvatarApi();
        cache()->tags([$this->userId])->put('avatar', $avatar, now()->addHours(3));

        return $avatar;
    }

    /**
     * Call the API to fetch the avatar image.
     *
     * @return string
     * @throws MsGraphException
     */
    protected function callAvatarApi(): string
    {
        $this->isLoaded = true;

        if (!isset($this->userId)) {
            return $this->generateFallbackAvatar();
        }

        $body = MsGraph::get(sprintf("/users/%s/photos/%s/\$value", $this->userId, $this->size))->body();

        return Str::contains($body, 'error')
            ? $this->generateFallbackAvatar()
            : 'data:image/jpeg;base64,' . base64_encode($body);
    }

    /**
     * Generate a fallback avatar URL based on user's alternative text.
     *
     * @return string
     */
    protected function generateFallbackAvatar(): string
    {
        $alt = str_replace(['(', ')', '-', '#', '_', 'Extern'], '', $this->alt);
        return 'https://ui-avatars.com/api/?name=' . urlencode($alt) . '&color=7F9CF5&background=random';
    }

    /**
     * Render the component.
     *
     * @return string
     */
    public function render(): string
    {
        return <<<'blade'
            <div wire:init="loadAvatar" wire:key="avatar-{{md5($userId)}}">
            @if($isLoaded)
            @if($this->avatar)
                <img class="{{$this->class}}" src="{{$this->avatar}}" alt="{{$alt}}">
            @endif
            @else
            <x-spinner/>
            @endif
            </div>
        blade;
    }
}
