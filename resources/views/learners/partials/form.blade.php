@php
    $isEdit = isset($attributes);
    $actionUrl = $isEdit ? route('learners.update', ['learner' => $attributes->id]) : route('learners.store');
@endphp

<form action="{{ $actionUrl }}" method="POST" x-data="{ date: '{{ old('birth_date', $isEdit ? $attributes->birth_date->format('Y-m-d') : '') }}' }">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

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

    <div class="mb-4" x-data="{ date: '{{ old('birth_date', $isEdit ? $attributes->birth_date->format('Y-m-d') : '') }}' }">
        <label for="birth_date" class="block text-gray-700 dark:text-gray-300">{{__('Birth Date') }}</label>
        <input type="date" name="birth_date" id="birth_date" x-model="date"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        @error('birth_date')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-between">
        <a href="{{ route('learners.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            {{ __('Back') }}
        </a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ $isEdit ? __('Update') : __('Create') }}
        </button>
    </div>
</form>
