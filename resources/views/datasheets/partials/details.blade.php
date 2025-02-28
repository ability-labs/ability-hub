
<div class="w-full">
    <h4 class="font-bold text-lg mb-2 flex justify-between items-center space-x-4">
        <span>{{ $datasheet->type->name }}</span>
        <span class="text-sm font-normal text-gray-400">{{ __('Posted') .' '. $datasheet->created_at->diffForHumans() }}</span>
    </h4>
    <ul class="divide-y divide-gray-200 dark:divide-gray-600">
        <li>
            <div class="flex justify-between items-center">
                @isset($datasheet->operator)
                <div class="flex items-center space-x-2">
                    <span class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>

                        <span>{{ __('Operator') }}:</span>
                    </span>
                    <span class="font-semibold ">{{ $datasheet->operator->name }}</span>
                </div>
                @endisset
                <span class="text-sm text-gray-500 dark:text-gray-400">

                  </span>
            </div>
        </li>
    </ul>

    <div>
            @include('datasheets.partials.reports.results', ['datasheet' => $datasheet])
    </div>
</div>
