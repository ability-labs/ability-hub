@props(['resource'])

@php
$isOperator = $resource instanceof \App\Models\Operator;
$isLearner = $resource instanceof \App\Models\Learner;

$name = $isOperator ? $resource->name : $resource->full_name;

// Initials logic
$parts = explode(' ', $name);
$initials = (count($parts) >= 2)
? mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1)
: mb_substr($name, 0, 1);
$initials = strtoupper($initials);

// Color logic
if ($isOperator) {
$bgColor = $resource->color ?? '#3b82f6';
$gradient = "linear-gradient(135deg, $bgColor 0%, " . ($resource->color ? $resource->color.'cc' : '#2563eb') . " 100%)";
} else {
// Learner gender colors
$bgColor = match($resource->gender) {
\App\Enums\PersonGender::FEMALE => '#db2777', // pink-600
\App\Enums\PersonGender::MALE => '#0284c7', // sky-600
default => '#4b5563' // gray-600
};
$gradient = "linear-gradient(135deg, $bgColor 0%, " . ($bgColor . 'cc') . " 100%)";
}

// Detail logic
$details = [];
if ($isOperator) {
$details[] = ['label' => __('VAT'), 'value' => $resource->vat_id ?? __('VAT Not Found'), 'icon' => 'card'];
$details[] = ['label' => __('Joined'), 'value' => $resource->created_at->format('d/m/Y'), 'icon' => 'calendar'];
} else {
$details[] = ['label' => __('Age'), 'value' => $resource->age . ' ', 'icon' => 'user'];
$details[] = ['label' => __('Birth Date'), 'value' => $resource->birth_date->format('d/m/Y'), 'icon' => 'calendar'];
}

// Badges
$badges = [];
$badgeLabel = $isOperator ? __('Disciplines') : __('Assigned operators');
if ($isOperator) {
foreach($resource->disciplines as $d) {
$badges[] = ['text' => __($d->getTranslation('name', app()->getLocale())), 'color' => 'gray'];
}
} else {
foreach($resource->operators as $o) {
$badges[] = ['text' => $o->name, 'color' => 'blue'];
}
}

// Actions
$editRoute = $isOperator ? route('operators.edit', $resource) : route('learners.edit', $resource);
$backRoute = $isOperator ? route('operators.index') : route('learners.index');
@endphp

<div
    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-6">
    <div class="p-6 flex flex-col md:flex-row items-center md:items-center justify-between gap-6">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0 w-20 h-20 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-md transform -rotate-2"
                style="background: {{ $gradient }};">
                {{ $initials }}
            </div>

            <!-- Name and Info -->
            <div class="space-y-1 text-center md:text-left">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">
                    {{ $name }}
                </h2>
                <div
                    class="flex flex-wrap justify-center md:justify-start items-center gap-x-4 gap-y-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                    @foreach($details as $detail)
                    <span class="flex items-center gap-1.5">
                        @if($detail['icon'] === 'card')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-4 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                        </svg>
                        @elseif($detail['icon'] === 'calendar')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-4 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        @elseif($detail['icon'] === 'user')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-4 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        @endif
                        {{ $detail['value'] }}
                    </span>
                    @if(!$loop->last)
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 hidden md:block"></span>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Badges and Actions -->
        <div class="flex flex-col items-center md:items-end gap-4 min-w-[200px]">
            <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest -mb-3 items-center">
                {{ $badgeLabel }}
            </div>
            <div class="flex flex-wrap justify-center md:justify-end gap-1.5">
                @foreach($badges as $badge)
                <span
                    class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded text-[10px] font-bold uppercase tracking-wider border border-gray-200 dark:border-gray-600">
                    {{ $badge['text'] }}
                </span>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $editRoute }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="size-3.5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ $backRoute }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>
</div>