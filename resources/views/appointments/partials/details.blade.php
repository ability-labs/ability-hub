<div class="space-y-4">
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Title') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $appointment->title }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Start Date') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">
      {{ \Carbon\Carbon::parse($appointment->start_time)->format('d/m/Y H:i') }}
    </span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('End Date') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">
      {{ \Carbon\Carbon::parse($appointment->finish_time)->format('d/m/Y H:i') }}
    </span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Operator') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $appointment->operator->name }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Learner') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $appointment->learner->full_name }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Discipline') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">
      {{ __($appointment->discipline->getTranslation('name', app()->getLocale())) }}
    </span>
        @if(isset($appointment->discipline->color))
            <span class="inline-block w-3 h-3 ml-2 rounded" style="background-color: {{ $appointment->discipline->color }}"></span>
        @endif
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Comments') }}:</span>
        <div class="mt-1 p-2 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-gray-100">
            {!! nl2br(e($appointment->comments)) !!}
        </div>
    </div>
    <div class="mt-4">
        <a href="{{ route('appointments.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            {{ __('Back') }}
        </a>
    </div>
</div>
