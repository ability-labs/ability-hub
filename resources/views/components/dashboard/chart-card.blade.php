<div @class([
    'bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 p-8 shadow-sm',
    'lg:col-span-2' => $isLarge
])>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        </div>
        <div class="p-2 bg-gray-50 dark:bg-gray-900 rounded-lg">
            {{ $slot }}
        </div>
    </div>
    <div class="h-[300px] w-full relative">
        <canvas id="{{ $id }}"></canvas>
    </div>
</div>
