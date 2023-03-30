@props([
	'file' => null,
	'accept' => 'image/jpg,image/jpeg,image/png,application/pdf,application/json',
	'multiple' => false,
	'mode' => 'attachment',
	'profileClass' => 'w-20 h-20 rounded-full'
])

<div x-data="{
	isMultiple: Boolean('{{ $multiple }}') || false,
	progress: 0,
	isFocused: false,
	handleFiles() {
		if (this.isMultiple === true) {
			@this.uploadMultiple('{{ $attributes->wire('model')->value }}', this.$refs.input.files, () => {
			}, () => {
			}, (event) => {
				this.progress = event.detail.progress || 0
			})
		} else {
			@this.upload('{{ $attributes->wire('model')->value }}',  this.$refs.input.files[0], () => {
		    }, () => {
		    }, (event) => {
		        this.progress = event.detail.progress || 0
		    });
		}
	}
}"
     x-cloak>
    @if(! $file || $mode === 'profile')
        @php $randomId = Str::random(6); @endphp
        <label for="file-{{ $randomId }}" class="relative block leading-tight  hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer inline-flex items-center transition duration-500 ease-in-out group overflow-hidden
			{{ 	$mode === 'profile' ? 'border group '. $profileClass : 'border-2 w-full pl-3 pr-4 py-2 rounded-lg border-dashed' }}
		"
               wire:loading.class="pointer-events-none"
               :class="{ 'border-gray-300': isFocused === true }"
        >
            {{-- hack to get the progress of upload file --}}
            <input
                    type="hidden"
                    name="{{ $attributes->wire('model')->value }}"
                    {{ $attributes->wire('model') }}
            />

            <input
                    type="file"
                    id="file-{{ $randomId }}"
                    class="absolute inset-0 cursor-pointer opacity-0 text-transparent sr-only"
                    accept="{{ $accept }}"
                    :multiple="isMultiple"
                    x-ref="input"
                    x-on:change.prevent="handleFiles"
                    x-on:focus="isFocused = true"
                    x-on:blur="isFocused = false"
            />

            {{-- Upload Progress --}}
            <div wire:loading.flex wire:target="{{ $attributes->wire('model')->value }}" wire:loading.class="w-full">
                @if ($mode === 'profile' && $file)
                    <div class="select-none text-sm  flex flex-1 items-center justify-center text-center p-4 flex-1">
                        <svg class="animate-spin h-6 w-6 " xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                @endif

                @if ($mode === 'attachment')
                    <div class="text-center flex-1 p-4">
                        <div class="mb-2">Uploading<span class="animate-ping">...</span></div>
                        <div>
                            <div class="h-4 relative max-w-lg mx-auto rounded-full overflow-hidden">
                                <div class="w-full h-full bg-gray-200 absolute"></div>
                                <div class="h-4 py-0.5 text-xs text-white font-bold bg-green-700 absolute" x-text="progress + '%'" x-bind:style="'width:' + progress + '%'"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Placeholder text for mode 'attachment' --}}
            <div class="flex items-center justify-center flex-1 px-4 py-2" wire:loading.class="hidden" wire:target="{{ $attributes->wire('model')->value }}">
                @if($slot->isEmpty())
                    @if($multiple)
                        <x-icon.document class="text-gray-500 dark:text-gray-400 -rotate-2" size="8"/>
                    @else
                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"></rect>
                            <path d="M168.001,100.00017v.00341a12.00175,12.00175,0,1,1,0-.00341ZM232,56V200a16.01835,16.01835,0,0,1-16,16H40a16.01835,16.01835,0,0,1-16-16V56A16.01835,16.01835,0,0,1,40,40H216A16.01835,16.01835,0,0,1,232,56Zm-15.9917,108.6936L216,56H40v92.68575L76.68652,112.0002a16.01892,16.01892,0,0,1,22.62793,0L144.001,156.68685l20.68554-20.68658a16.01891,16.01891,0,0,1,22.62793,0Z"></path>
                        </svg>
                    @endif

                    @if ($mode === 'attachment')
                        <span class="ml-2 text-gray-500 dark:text-gray-400">{{ is_array($file) ? 'Browse files' : 'Browse file' }} | <span class="text-sm">JSON</span></span>
                    @endif
                @else
                    {{ $slot }}
                @endif
            </div>
        </label>
    @endif

    @if ($mode === 'attachment')
        {{-- Loading indicator for file remove --}}
        <div wire:loading.delay wire:loading.flex wire:target="removeUpload" wire:loading.class="w-full">
            <div class="flex-1 p-1 text-center text-sm rounded-md font-text text-red-800 dark:text-red-50 bg-red-50 dark:bg-transparent p-2 dark:border border-red-500">
                <span class="animate-pulse">Removing file...</span>
            </div>
        </div>

        {{-- Preview for mode 'attachment' --}}
        <div>
            @if(is_array($file) && count($file) > 0)
                @foreach($file as $key => $f)
                    <div class="py-3 space-x-2 flex justify-between items-center">
                        <div class="hidden md:block w-16 flex-shrink-0 shadow-xs rounded-lg">
                            @if(collect(['jpg', 'png', 'jpeg', 'webp'])->contains($f->getClientOriginalExtension()))
                                <div class="relative pb-16 overflow-hidden rounded-lg border border-gray-100">
                                    <img src="{{ $f->temporaryUrl() }}" class="w-full h-full absolute object-cover rounded-lg">
                                </div>
                            @else
                                <div class="w-14 h-14 bg-gray-100 dark:bg-transparent text-gray-400 flex items-center justify-center rounded-lg border border-gray-100">
                                    <x-icon.document/>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            {{-- prints attachment.* --}}
                            <div class="text-sm font-medium truncate w-40 md:w-auto">{{ $f->getClientOriginalName() }}</div>
                            <div class="flex items-center space-x-1">
                                <div class="text-xs ">{{ $this->humanFileSize($f->getSize())}}</div>
                                <div class="text-xs">&bull;</div>
                                <div class="text-xs uppercase">{{ $f->getClientOriginalExtension() }}</div>
                            </div>
                        </div>
                        <div>
                            <button
                                    wire:key="remove-attachment-{{ $f->getFilename() }}"
                                    wire:loading.attr="disabled"
                                    type="button"
                                    x-on:click.prevent="$wire.removeUpload('{{ $attributes->wire('model')->value }}', '{{ $f->getFilename() }}')"
                                    class="text-xs text-red-500 appearance-none hover:underline">
                                <span class="border-red-800 inline p-1 rounded-full"><x-icon.x solid size="4"/></span>
                            </button>
                        </div>
                    </div>

                    @if ($multiple)
                        @error($attributes->wire('model')->value . '.'. $key)
                        <div class="rounded-md bg-red-50 dark:bg-transparent p-2 dark:border border-red-500 {{ !$loop->last ? 'mb-1' : '' }}">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <!-- Heroicon name: mini/x-circle -->
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-text text-red-800 dark:text-red-50">{{ $message }}</h3>
                                </div>
                            </div>
                        </div>
                        @enderror
                    @endif
                    <div class="{{ !$loop->last ? 'border-b border-gray-200' : '' }}"></div>
                @endforeach
            @else
                @if($file)
                    <div class="mt-3 space-x-2 flex">
                        <div class="w-16 flex-shrink-0 shadow-xs rounded-lg">
                            @if(collect(['jpg', 'png', 'jpeg', 'webp'])->contains($file->getClientOriginalExtension()))
                                <div class="relative pb-16 w-full overflow-hidden rounded-lg border border-gray-100">
                                    <img src="{{ $file->temporaryUrl() }}" class="w-full h-full absolute object-cover rounded-lg">
                                </div>
                            @else
                                <div class="w-16 h-16 bg-gray-100 items-center justify-center rounded-lg border border-gray-100">
                                    <svg class="h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>

                            @error($attributes->wire('model')->value)
                            <div class="rounded-md bg-red-50 dark:bg-transparent p-2 dark:border border-red-500">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <!-- Heroicon name: mini/x-circle -->
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-text text-red-800 dark:text-red-50">{{ $message }}</h3>
                                    </div>
                                </div>
                            </div>
                            @enderror

                            <div class="text-sm font-medium truncate w-40 md:w-auto">{{ $file->getClientOriginalName() }}</div>
                            <div class="flex items-center space-x-1">
                                <div class="text-xs">{{ Str::bytesToHuman($file->getSize()) }}</div>
                                <div class="text-xs">&bull;</div>
                                <div class="text-xs uppercase">{{ $file->getClientOriginalExtension() }}</div>
                            </div>
                            <div>
                                <button
                                        wire:loading.attr="disabled"
                                        type="button"
                                        x-on:click.prevent="$wire.removeUpload('{{ $attributes->wire('model')->value }}', '{{ $file->getFilename() }}')"
                                        class="text-xs  appearance-none hover:underline">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>