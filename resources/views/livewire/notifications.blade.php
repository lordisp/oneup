<div>
    <x-list>
        @forelse($unreadNotifications as $notification)
            <div wire:key="{{$notification['id']}}">
                <x-list.sticky tag="a" href="{{route('firewall.requests.read')}}">
                    <x-slot name="first">{{ $notification['title'] }}</x-slot>
                    <x-slot name="second">
                        {{ $notification['message'] }}
                    </x-slot>
                    <x-slot name="link">
                        <span class="hover:underline">view all requests</span>
                    </x-slot>
                    <x-slot name="cta">
                        <button type="button" wire:click.prevent="read('{{$notification['id']}}')" type="button">
                            <x-icon.check-circle/>
                        </button>
                    </x-slot>
                </x-list.sticky>
            </div>
        @empty
            <div class="flex space-x-1 h-16 rounded-lg text-green-900 border-green-200 border justify-center items-center bg-green-50">
                <span>{{ __('empty-table.nothing_to_do') }}</span>
                <span class="-rotate-6"><x-icon.hand-thumb-up /></span>
            </div>
        @endforelse
    </x-list>
</div>
