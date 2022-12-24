<?php

namespace App\Http\Livewire\Components;

use App\Services\TokenCache;
use App\Traits\Token;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

/**
 * @property mixed $avatar
 */
class Avatar extends Component
{
    use Token;

    const PROVIDER = 'lhg_graph';
    public $userId;
    public string $class = '', $alt, $size = '48x48';
    public bool $isLoaded = false;
    public bool $noCache = false;
    public $key;

    public function loadAvatar()
    {
        $this->isLoaded = true;
    }

    protected function callAvatarApi(): string
    {
        $this->isLoaded = true;
        if (isset($this->userId)) {
            $token = decrypt($this->token(self::PROVIDER));
            $uri = 'https://graph.microsoft.com/v1.0/users/' . $this->userId . '/photos/' . $this->size . '/$value';
            $response = Http::withToken($token)->withHeaders(['Content-Type' => 'image/jpg'])
                ->retry(3, 0, function ($exception, $request) {
                    if ($exception->response->status() != 403) {
                        return false;
                    }
                    $newToken = decrypt((new TokenCache())->noCache()->provider(self::PROVIDER)->get());
                    $request->withToken($newToken);
                    return true;
                }, throw: false)
                ->get($uri);
        }
        $alt = str_replace(['(', ')', '-', '#', '_', 'Extern'], '', $this->alt);
        return (isset($response) && $response->status() == 200)
            ? 'data:image/jpeg;base64,' . base64_encode($response->body())
            : 'https://ui-avatars.com/api/?name=' . urlencode($alt) . '&color=7F9CF5&background=random';
    }

    public function getAvatarProperty(): string
    {
        if ($this->noCache) return $this->callAvatarApi();

        $avatar = Cache::tags([$this->userId])->get('avatar');

        if (is_string($avatar)) {
            $this->isLoaded = false;
        } else {
            $this->isLoaded = true;
            $avatar = $this->callAvatarApi();
            Cache::tags([$this->userId])->put('avatar', $avatar, now()->addHours(3));
        }
        return $avatar;
    }

    public function render()
    {
        return <<<'blade'
            <div wire:init="loadAvatar" wire:key="avatar-{{$key}}">
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
