<div x-data="{ color: '{{ old($name, $value ?? '#000000') }}' }" class="flex items-center gap-4">
    <input type="color" x-model="color" name="{{ $name }}" class="h-10 w-16 p-0 border-none rounded" />
</div>
