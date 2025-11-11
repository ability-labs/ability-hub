

<form action="{{ route('locale.update') }}" method="POST" class="flex space-x-2 items-center">
    @csrf

    <label for="locale" class="sr-only block text-sm font-medium text-gray-700">
        {{ __('Select language') }}
    </label>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
    </svg>

    <select id="locale" name="locale"
            class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black"
            aria-describedby="locale-description">
        @php
            $languages = collect(\App\Enums\AppLocale::cases())
                        ->map(fn (UnitEnum $case) => [
                            'key' => \Illuminate\Support\Str::lower($case->name),
                            'value' => $case->value,
                        ]);
            $currentLocale = old('locale', app()->getLocale());
        @endphp
        @foreach($languages as $language)
            <option value="{{ $language['key'] }}" {{ $currentLocale === $language['key'] ? 'selected' : '' }}>
                {{ $language['value'] }}
            </option>
        @endforeach
    </select>
    <p id="locale-description" class="sr-only mt-2 text-sm text-gray-500">
        {{ __('Select your preferred language and confirm to update the view.') }}
    </p>

    <!-- Bottone di submit visibile solo se JavaScript è disabilitato -->
    <noscript>
        <button type="submit"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Change language') }}
        </button>
    </noscript>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var select = document.getElementById("locale");
        var form = select.closest("form");

        // Se esiste il bottone di submit, lo nascondiamo
        var submitButton = form.querySelector("button[type='submit']");
        if (submitButton) {
            submitButton.style.display = 'none';
        }

        // Aggiungiamo l'evento che invia il form automaticamente al cambio della select
        select.addEventListener("change", function() {
           form.submit();
        });
    });
</script>
