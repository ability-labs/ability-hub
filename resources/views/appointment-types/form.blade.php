<div class="space-y-6">

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/40">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ __('Errors occurred') }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul role="list" class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
        <div class="sm:col-span-4">
            <label for="name_it" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">
                {{ __('Name') }} <span class="text-red-500">*</span>
            </label>
            <div class="mt-2">
                <input type="text" name="name[it]" id="name_it" required
                       value="{{ old('name.it', isset($appointmentType) ? $appointmentType->getTranslation('name', 'it') : '') }}"
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
            </div>
        </div>

        <div class="sm:col-span-4" x-data="{
            isTransparent: {{ old('color', $appointmentType->color ?? null) ? 'false' : 'true' }},
            colorVal: '{{ old('color', $appointmentType->color ?? '#000000') }}'
        }">
            <label for="color_picker" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">
                {{ __('Color') }}
            </label>
            <div class="mt-2 flex items-center space-x-4">
                <!-- Color Picker -->
                <input type="color"
                       id="color_picker"
                       x-model="colorVal"
                       x-bind:disabled="isTransparent"
                       class="h-10 w-10 cursor-pointer rounded-md border-0 p-0 shadow-sm disabled:cursor-not-allowed disabled:opacity-50">

                <!-- Transparent Checkbox -->
                <div class="relative flex items-start">
                    <div class="flex h-6 items-center">
                        <input id="is_transparent"
                               type="checkbox"
                               x-model="isTransparent"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:ring-offset-gray-900">
                    </div>
                    <div class="text-sm leading-6 ml-3">
                        <label for="is_transparent" class="font-medium text-gray-900 dark:text-gray-300">
                            {{ __('Transparent') }}
                        </label>
                    </div>
                </div>

                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="color" :value="isTransparent ? '' : colorVal">
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('The chosen color will be used as the border color for events in the calendar.') }}
            </p>
        </div>
    </div>
</div>
