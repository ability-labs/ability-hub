@php
    $isEdit = isset($attributes);
    $actionUrl = $isEdit ? route('learners.update', ['learner' => $attributes->id]) : route('learners.store');

    if ($isEdit) {
        $min = (int) ($attributes->weekly_minutes ?? 0);
        $h = intdiv($min, 60);
        $weeklyHoursFromMinutes = $h + (($min % 60) === 30 ? 0.5 : 0);
        $weeklyHoursDefault = str_replace('.', ',', (string)$weeklyHoursFromMinutes);
    } else {
        $weeklyHoursDefault = '';
    }

    $weeklyHours = old('weekly_hours', $weeklyHoursDefault);
    $selectedOperatorIds = collect(old('operator_ids', $isEdit ? ($attributes->operators->pluck('id')->all() ?? []) : []))
        ->map(fn($id) => (string) $id)
        ->all();
@endphp

<form action="{{ $actionUrl }}" method="POST"
      autocomplete="off"
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
        
        {{-- Section: Personal Info --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-2.533-4.656 9.353 9.353 0 0 0-4.213-.997 1.125 1.125 0 0 1-1.096-.864 3.75 3.75 0 1 1 2.214-1.32" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider text-sm">{{ __('Personal Information') }}</h3>
            </div>

            <div class="grid grid-cols-1 gap-5">
                {{-- First Name --}}
                <div>
                    <x-input-label for="first_name" :value="__('Firstname')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                    <x-text-input id="first_name" name="first_name" type="text" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl" :value="old('first_name', $isEdit ? $attributes->first_name : '')" placeholder="{{ __('Enter first name') }}" required />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>

                {{-- Last Name --}}
                <div>
                    <x-input-label for="last_name" :value="__('Lastname')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                    <x-text-input id="last_name" name="last_name" type="text" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl" :value="old('last_name', $isEdit ? $attributes->last_name : '')" placeholder="{{ __('Enter last name') }}" required />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Birth Date --}}
                    <div>
                        <x-input-label for="birth_date" :value="__('Birth Date')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                        <x-text-input id="birth_date" name="birth_date" type="date" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl" :value="old('birth_date', $isEdit ? $attributes->birth_date->format('Y-m-d') : '')" />
                        <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
                    </div>

                    {{-- Gender --}}
                    <div>
                        <x-input-label for="gender" :value="__('Gender')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                        <select name="gender" id="gender"
                                class="block w-full border-gray-200 dark:border-gray-700/50 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm">
                            @foreach(\App\Enums\PersonGender::cases() as $case)
                                <option {{ (string) old('gender', $isEdit ? ($attributes->gender->value ?? $attributes->gender) : '') === $case->value ? 'selected' : '' }} value="{{ $case->value }}">
                                    {{ __($case->value) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Center & Allocation --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider text-sm">{{ __('Registration & Allocation') }}</h3>
            </div>

            <div class="grid grid-cols-1 gap-5">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Registration Date --}}
                    <div>
                        <x-input-label for="created_at" :value="__('Registration Date')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                        <x-text-input id="created_at" name="created_at" type="date" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl" :value="old('created_at', $isEdit ? $attributes->created_at->format('Y-m-d') : now()->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('created_at')" />
                    </div>

                    {{-- Weekly Hours --}}
                    <div>
                        <x-input-label for="weekly_hours" :value="__('Weekly Hours')" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1" />
                        <div class="relative">
                            <x-text-input id="weekly_hours" name="weekly_hours" type="number" step="0.5" min="0" class="block w-full border-gray-200 dark:border-gray-700/50 rounded-xl pr-10" :value="$weeklyHours" placeholder="0.0" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-xs font-bold text-gray-400">H</span>
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('weekly_hours')" />
                    </div>
                </div>

                {{-- Operators select --}}
                <div x-data="{ count: {{ count($selectedOperatorIds) }} }">
                    <x-input-label for="operator_ids" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-1 flex justify-between items-center">
                        <span>{{ __('Assigned operators') }}</span>
                        <span class='text-blue-500 font-bold' x-text="count + ' {{ __('Selected') }}'"></span>
                    </x-input-label>
                    <select name="operator_ids[]" id="operator_ids" multiple 
                            @change="count = Array.from($el.selectedOptions).length"
                            class="block w-full border-gray-200 dark:border-gray-700/50 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm"
                            size="6">
                        @foreach(($operators ?? collect()) as $op)
                            <option value="{{ $op->id }}"
                                {{ in_array((string)$op->id, $selectedOperatorIds, true) ? 'selected' : '' }}
                                class="py-2 px-3 border-b border-gray-50 dark:border-gray-800 last:border-0">
                                {{ $op->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mt-2 flex items-center gap-2 text-[10px] text-gray-500 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 .512 1.335l-.041.02a.75.75 0 0 1-.512-1.335Zm4.125 0 .041-.02a.75.75 0 1 1 .512 1.335l-.041.02a.75.75 0 0 1-.512-1.335Zm-2.062-1.875a.75.75 0 1 1-.001 1.501.75.75 0 0 1 0-1.501Zm2.063 3.75a.75.75 0 1 1-.001 1.501.75.75 0 0 1 0-1.501Zm-4.125 0a.75.75 0 1 1-.001 1.501.75.75 0 0 1 0-1.501Zm2.062 1.875a.75.75 0 1 1-.001 1.501.75.75 0 0 1 0-1.501Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{ __('Hold CTRL (or CMD on Mac) to select multiple operators.') }}
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('operator_ids')" />
                </div>
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
            @php
                $selectedOperators = $attributes->operators ?? collect();
                $learnerDisciplines = $selectedOperators->loadMissing('disciplines')
                    ->flatMap(fn($operator) => $operator->disciplines)
                    ->unique('id');
            @endphp

            @if($learnerDisciplines->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 px-4 bg-gray-50 dark:bg-gray-900/30 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                    <div class="p-4 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 text-center max-w-sm lowercase first-letter:uppercase">
                        {{ __('No disciplines available from the assigned operators. Select at least one operator first.') }}
                    </p>
                </div>
            @else
                <x-scatter-plot-week
                    :subject="$attributes"
                    :disciplines="$learnerDisciplines"
                    :toggle-url="route('learners.availability.toggle', $attributes)"
                />
            @endif
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700">
        <a href="{{ route('learners.index') }}" 
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

