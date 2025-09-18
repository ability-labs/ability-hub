@php
    $isEdit = isset($attributes);
    $actionUrl = $isEdit ? route('operators.update', ['operator' => $attributes->id]) : route('operators.store');
    $selectedDisciplines = $isEdit ? $attributes->disciplines->pluck('id')->toArray() : [];
@endphp


<form action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div x-data="{ tab: 'main' }">
        <div class="flex mb-4 space-x-4 border-b pb-2">
            <button type="button" @click="tab = 'main'" :class="{ 'font-bold border-b-2 border-blue-500': tab === 'main' }">Principale</button>
            <button type="button" @click="tab = 'availability'" :class="{ 'font-bold border-b-2 border-blue-500': tab === 'availability' }">Disponibilità</button>
        </div>

        <div x-show="tab === 'main'">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $isEdit ? $attributes->name : '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="{{ __('Enter operator name') }}">
            @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-300">{{ __('Color') }}</label>
            <x-color-picker name="color" :value="old('color', $isEdit ? $attributes->color : '#000000')" />

            @error('color')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>



        <div class="mb-4">
            <label for="vat_id" class="block text-gray-700 dark:text-gray-300">{{ __('VAT Number') }}</label>
            <input type="text" name="vat_id" id="vat_id"
                   value="{{ old('vat_id', $isEdit ? $attributes->vat_id : '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="{{ __('VAT Number') . ' (' . __('Optional') . ')' }}">
            @error('vat_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">{{ __('Disciplines') }}</label>
            <div class="mt-1 space-y-2">
                @foreach($disciplines as $discipline)
                    <div class="flex items-center">
                        <input id="discipline_{{ $discipline->id }}" type="checkbox" name="disciplines[]" value="{{ $discipline->id }}"
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               @if(in_array($discipline->id, old('disciplines', $selectedDisciplines))) checked @endif>
                        <label for="discipline_{{ $discipline->id }}" class="ml-2 block text-gray-700 dark:text-gray-300">
                            {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                        </label>
                    </div>
                @endforeach
            </div>
            @error('disciplines')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        </div>

        <div x-show="tab === 'availability'" class="mt-6">
            @if($isEdit)
                <x-scatter-plot-week
                    :subject="$operator"
                    :disciplines="$operator->disciplines"
                    :toggle-url="route('operators.availability.toggle', $operator)"
                />
            @endif
        </div>
    </div>

    <div class="flex justify-between">
        <a href="{{ route('operators.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            {{ __('Back') }}
        </a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ $isEdit ? __('Update') : __('Create') }}
        </button>
    </div>
</form>
