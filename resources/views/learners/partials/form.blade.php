@php
    $isEdit = isset($attributes);
    $actionUrl = $isEdit ? route('learners.update', ['learner' => $attributes->id]) : route('learners.store');

    if ($isEdit) {
        $min = (int) ($attributes->weekly_minutes ?? 0);
        $h = intdiv($min, 60);
        $weeklyHoursFromMinutes = $h + (($min % 60) === 30 ? 0.5 : 0);
        // mostra la virgola (es. 4,5)
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
      x-data="{ tab: 'main', date: '{{ old('birth_date', $isEdit ? $attributes->birth_date->format('Y-m-d') : '') }}' }">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- Tabs --}}
    <div class="flex mb-4 space-x-4 border-b pb-2">
        <button type="button" @click="tab = 'main'"
                :class="{ 'font-bold border-b-2 border-blue-500': tab === 'main' }">
            {{ __('Main') }}
        </button>

        @if($isEdit)
            <button type="button" @click="tab = 'availability'"
                    :class="{ 'font-bold border-b-2 border-blue-500': tab === 'availability' }">
                {{ __('Availability') }}
            </button>
        @endif
    </div>

    {{-- MAIN TAB --}}
    <div x-show="tab === 'main'">
        {{--  First Name  --}}
        <div class="mb-4">
            <label for="first_name" class="block text-gray-700 dark:text-gray-300">{{__('Firstname') }}</label>
            <input type="text" name="first_name" id="first_name"
                   value="{{ old('first_name', $isEdit ? $attributes->first_name : '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Inserisci il nome">
            @error('first_name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{--  Last Name  --}}
        <div class="mb-4">
            <label for="last_name" class="block text-gray-700 dark:text-gray-300">{{__('Lastname') }}</label>
            <input type="text" name="last_name" id="last_name"
                   value="{{ old('last_name', $isEdit ? $attributes->last_name : '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Inserisci il cognome">
            @error('last_name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{--  Birth Date  --}}
        <div class="mb-4" x-data="{ date: '{{ old('birth_date', $isEdit ? $attributes->birth_date->format('Y-m-d') : '') }}' }">
            <label for="birth_date" class="block text-gray-700 dark:text-gray-300">{{__('Birth Date') }}</label>
            <input type="date" name="birth_date" id="birth_date" x-model="date"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('birth_date')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{--  Gender --}}
        <div class="mb-4">
            <label for="gender" class="block text-gray-700 dark:text-gray-300">{{__('Gender') }}</label>
            <select name="gender" id="gender"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @foreach(\App\Enums\PersonGender::cases() as $case)
                    <option {{ old('gender', $isEdit ? $attributes->gender : '') === $case->value ? 'selected' : '' }} value="{{ $case }}">
                        {{ $case }}
                    </option>
                @endforeach
            </select>
            @error('gender')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Weekly Hours (accpets integers or .5 / ,5) --}}
        <div class="mb-4">
            <label for="weekly_hours" class="block text-gray-700 dark:text-gray-300">
                {{ __('Weekly Hours') }}
            </label>
            <input
                type="number"
                min="0"
                step="0.5"
                name="weekly_hours"
                id="weekly_hours"
                value="{{ $weeklyHours }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="{{ __('Enter weekly hours (e.g. 4.5 or 4,5)') }}"
            >
            @error('weekly_hours')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Operators select --}}
        <div class="mb-4">
            <label for="operator_ids" class="block text-gray-700 dark:text-gray-300">{{ __('Assigned operators') }}</label>
            <select name="operator_ids[]" id="operator_ids" multiple size="{{ min(6, max(3, ($operators ?? collect())->count())) }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @foreach(($operators ?? collect()) as $op)
                    <option value="{{ $op->id }}"
                        {{ in_array($op->id, $selectedOperatorIds, true) ? 'selected' : '' }}>
                        {{ $op->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">{{ __('Hold CTRL (or CMD on Mac) to select multiple operators.') }}</p>
            @error('operator_ids')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            @error('operator_ids.*')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{--  Registration Date  --}}
        <div class="mb-4" x-data="{ date: '{{ old('created_at', $isEdit ? $attributes->created_at->format('Y-m-d') : '') }}' }">
            <label for="created_at" class="block text-gray-700 dark:text-gray-300">{{__('Registration Date') }}</label>
            <input type="date" name="created_at" id="created_at" x-model="date"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('created_at')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- AVAILABILITY TAB (solo in edit) --}}
    @if($isEdit)
        <div x-show="tab === 'availability'" class="mt-6">
            @php
                $selectedOperators = $attributes->operators ?? collect();
                $learnerDisciplines = $selectedOperators->loadMissing('disciplines')
                    ->flatMap(fn($operator) => $operator->disciplines)
                    ->unique('id');
            @endphp

            @if($learnerDisciplines->isEmpty())
                <div class="text-center py-4 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">
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

    <div class="flex justify-between mt-6">
        <a href="{{ route('learners.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            {{ __('Back') }}
        </a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ $isEdit ? __('Update') : __('Create') }}
        </button>
    </div>
</form>
