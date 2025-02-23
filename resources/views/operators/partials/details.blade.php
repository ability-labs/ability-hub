<div class="space-y-4">
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Name') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $operator->name }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('VAT Number') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $operator->vat_id ?? __('Not Found') }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Disciplines') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">
            @if($operator->disciplines->isNotEmpty())
                @foreach($operator->disciplines as $discipline)
                    <span class="inline-block bg-gray-200 dark:bg-gray-600 rounded-full px-2 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                    </span>
                @endforeach
            @else
                {{ __('None') }}
            @endif
        </span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Registration') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $operator->created_at->format('d/m/Y') }}</span>
    </div>
    <div class="mt-4">
        <a href="{{ route('operators.edit', $operator) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            {{ __('Edit') }}
        </a>
        <a href="{{ route('operators.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 ml-2">
            {{ __('Back') }}
        </a>
    </div>
</div>
