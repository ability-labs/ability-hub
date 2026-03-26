@php
    $dims = match ($size) {
        'xs' => 'w-8 h-8 text-[10px]',
        'sm' => 'w-10 h-10 text-xs',
        'md' => 'w-12 h-12 text-sm',
        'lg' => 'w-16 h-16 text-xl',
        'xl' => 'w-24 h-24 text-3xl',
        default => 'w-12 h-12 text-sm'
    };
@endphp
<div {{ $attributes->merge(['class' => "$dims flex-shrink-0 aspect-square rounded-xl flex items-center justify-center text-white font-bold shadow-sm transform -rotate-2 overflow-hidden"]) }}
    style="background: {{ $gradient }};">
    {{ $initials }}
</div>
