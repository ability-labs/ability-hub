<div class="space-y-4">
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Firstname') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $learner->first_name }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Lastname') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $learner->last_name }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Birth Date') }}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $learner->birth_date->isoFormat('D MMMM YYYY') }}</span>
    </div>
    <div>
        <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Age')}}:</span>
        <span class="text-gray-900 dark:text-gray-100">{{ $learner->age }}</span>
    </div>
    <div class="mt-4">
        <a href="{{ route('learners.edit', $learner) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">{{__('Edit')}}</a>
        <a href="{{ route('learners.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 ml-2">{{ __('Back') }}</a>
    </div>
</div>
