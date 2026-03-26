@php
    $isEdit = isset($attributes);
    $actionUrl = $isEdit ? route('operators.update', ['operator' => $attributes->id]) : route('operators.store');
    $selectedDisciplines = $isEdit ? $attributes->disciplines->pluck('id')->toArray() : [];
@endphp

<form action="{{ $actionUrl }}" method="POST" autocomplete="off"
      x-data="{ tab: 'main' }"
      class="space-y-8">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- Modern Tab Switcher --}}
    <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-900/50 rounded-2xl mb-2">
        <button type="button" @click="tab = 'main'"
                :class="tab === 'main' ? 'bg-white dark:bg-gray-800 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            {{ __('Main') }}
        </button>

        @if($isEdit)
            <button type="button" @click="tab = 'availability'"
                    :class="tab === 'availability' ? 'bg-white dark:bg-gray-800 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                {{ __('Availability') }}
            </button>
        @endif
    </div>

    {{-- MAIN TAB --}}
    <div x-show="tab === 'main'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        {{-- Section: Profile --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider text-sm">{{ __('Operator Profile') }}</h3>
            </div>

            <div class="grid grid-cols-1 gap-5">
                {{-- Name --}}
                <div>
                    <x-input-label for="name" :value="__('Name')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                    <x-text-input id="name" name="name" type="text" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl" :value="old('name', $isEdit ? $attributes->name : '')" placeholder="{{ __('Enter operator name') }}" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                {{-- Color --}}
                <div>
                    <x-input-label :value="__('Color')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                    <x-color-picker name="color" :value="old('color', $isEdit ? $attributes->color : '#3b82f6')" />
                    <x-input-error class="mt-2" :messages="$errors->get('color')" />
                </div>

                {{-- VAT ID --}}
                <div>
                    <x-input-label for="vat_id" :value="__('VAT Number')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                    <x-text-input id="vat_id" name="vat_id" type="text" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl" :value="old('vat_id', $isEdit ? $attributes->vat_id : '')" placeholder="{{ __('VAT Number') . ' (' . __('Optional') . ')' }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('vat_id')" />
                </div>
            </div>
        </div>

        {{-- Section: Expertise --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider text-sm">{{ __('Expertise & Roles') }}</h3>
            </div>

            <div class="space-y-4">
                <x-input-label :value="__('Disciplines')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($disciplines as $discipline)
                        <label for="discipline_{{ $discipline->id }}" 
                               class="relative flex items-center p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 cursor-pointer transition-all hover:bg-white dark:hover:bg-gray-800 hover:shadow-sm group">
                            <input id="discipline_{{ $discipline->id }}" type="checkbox" name="disciplines[]" value="{{ $discipline->id }}"
                                   class="h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-blue-500 transition-all"
                                   @if(in_array($discipline->id, old('disciplines', $selectedDisciplines))) checked @endif>
                            <span class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('disciplines')" />
            </div>
        </div>
    </div>

    {{-- AVAILABILITY TAB --}}
    @if($isEdit)
        <div x-show="tab === 'availability'" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">
            <x-scatter-plot-week
                :subject="$operator"
                :disciplines="$operator->disciplines"
                :toggle-url="route('operators.availability.toggle', $operator)"
            />
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700">
        <a href="{{ route('operators.index') }}" 
           class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-sm font-bold shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            {{ __('Back') }}
        </a>
        
        <x-primary-button class="rounded-xl px-8 py-3 bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg transition-all focus:ring-offset-2">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                {{ $isEdit ? __('Update') : __('Create') }}
            </div>
        </x-primary-button>
    </div>
</form>

