@php
    $suggestions = \App\Models\Reinforcer::all()->map(fn ($item) => $item->forSuggestions());
@endphp

<div x-data="datasheetReport(
        {{ $datasheet->data->toJson() }},
        {{ json_encode($datasheet->report()->getInfo()) }},
        {{ json_encode($datasheet->report()->report(), JSON_PRETTY_PRINT) }},
        {{ $datasheet->type->toJson() }}
    )"
     x-effect.debounce.1000="saveDatasheet()">


    <ol class="mt-8 px-4 lg:px-24 flex items-center w-full text-sm font-medium text-center text-gray-500 dark:text-gray-400 sm:text-base">
        <template x-for="(step, stepIndex) in steps">
            <li @click="() => currentStep = stepIndex"
                :class="currentStep === stepIndex ? 'text-indigo-500 border-indigo-500' : ''"
                class="cursor-pointer flex md:w-full items-center sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10 dark:after:border-gray-700">
            <span
                class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-gray-200 dark:after:text-gray-500">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                </svg>
                <span class="hidden sm:inline-flex sm:ms-2"><span x-text="stepIndex + 1"></span>. <span
                        x-text="step"></span></span>
            </span>
            </li>
        </template>
    </ol>

    <div class="min-h-[220px]">
        <!-- Info Section -->
        <div x-show="currentStep === 0" class="my-8 mx-auto max-w-2xl">
            <h1 class="text-3xl mb-4 text-pink-500 flex items-center space-x-2">
                <span class="bg-pink-100 text-pink-500 px-2 py-1 rounded-md font-bold text-xs uppercase">
                    {{ $datasheet->type->category }}
                </span>
                <span>{{ $datasheet->type->name }}</span>
            </h1>

            <p class="text-lg">
                {{ $datasheet->type->description }}
                <button type="button" @click="() => displayInfo = !displayInfo" class="text-gray-600 underline ml-2" x-text="displayInfo ? hideInfoLabel : displayInfoLabel"></button>
            </p>


            <div x-show="displayInfo">
                <h2 class="text-xl mt-4 font-bold text-pink-500">{{ __('Instructions') }}</h2>
                <p class="text-lg">{{ $datasheet->type->instruction }}</p>
            </div>
        </div>

        <!-- Items Section -->
        <div x-show="currentStep === 1" class="my-8 mx-auto max-w-3xl">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <h3 class="text-xl font-bold">{{ __('Items') }}</h3>
                    <button title=" {{ __("Add :resource", ['resource' => __('Item')]) }}" :disabled="searchItemIsVisible" @click="addNewItem()"
                            class="px-3 py-1 bg-green-600 text-white rounded-full text-2xl font-bold">
                        +

                    </button>
                </div>
                <button :class="items.length === 0 ? 'visible' : 'invisible'" @click="addSuggestedItems()"
                        class="text-sm flex items-center space-x-1 text-indigo-600 underline font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>

                    <span>{{ __('Add Suggested Items') }}</span>
                </button>

                <div class="flex items-center space-x-2">
                    <div x-show="items.length === 0 || items.length < reportInfo.minimum_items" class="flex items-center">
                        <p class="text-sm text-red-400">{{ __('Minimum') }}: <span
                                x-text="reportInfo.minimum_items"></span></p>
                    </div>
                    <div x-show="items.length === 0 || items.length < reportInfo.suggested_items"
                         class="flex items-center space-x-1 text-yellow-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>

                        <p class="text-sm">{{ __('Suggested') }}: <span
                                x-text="reportInfo.suggested_items"></span></p>
                    </div>
                </div>
            </div>

            <div  x-show="items.length > 0" class="grid md:grid-cols-2 gap-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-4">
                            <p class="flex items-center space-x-4 text-2xl">
                            <span class="font-bold px-4 py-2 rounded-md bg-pink-500 text-white"
                                  x-text="item.key"></span>
                                <span x-text="item.name"></span>
                            </p>
                            <button @click="removeItem(index)" class="text-sm text-red-500" type="button"
                                    title="{{ __('Remove') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Item Search Modal -->
        <div x-show="searchItemIsVisible" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl shadow-lg p-6 z-10" @click.away="closeSearchModal()">
                <div class="flex-1 relative max-w-md">
                    <label for="itemInput" class="sr-only">{{ __('Enter item name') }}</label>
                    <input id="itemInput" :ref="'itemInput'" type="text" x-model="itemSearch"
                           @input="checkAutoComplete()" placeholder="{{ __('Enter item name') }}"
                           class="w-full border rounded p-2">
                    <!-- Suggestion dropdown -->
                    <div x-show="suggestionsVisible"
                         class="absolute left-0 right-0 bg-white border rounded mt-1 z-40">
                        <template x-for="suggestion in filteredReinforcers" :key="suggestion.id">
                            <div @click="selectSuggestion(suggestion)"
                                 class="p-2 hover:bg-gray-200 cursor-pointer"
                                 x-text="suggestion.name"></div>
                        </template>
                    </div>
                </div>
                <!-- Directory dei rinforzi raggruppati per categoria e subcategoria -->
                <div class="mt-4">
                    <!-- Tab Bar per le categorie -->
                    <div class="border-b">
                        <nav class="-mb-px flex space-x-4">
                            <template x-for="(reinforcers, category) in groupedReinforcers" :key="category">
                                <button type="button"
                                        :class="{'border-indigo-500 text-indigo-600': activeCategory === category, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeCategory !== category}"
                                        class="whitespace-nowrap border-b-2 font-medium text-sm"
                                        @click="activeCategory = category">
                                    <span x-text="category"></span>
                                </button>
                            </template>
                        </nav>
                    </div>
                    <!-- Elenco rinforzi per la categoria attiva -->
                    <div class="mt-4 max-h-80 overflow-y-auto">
                        <template x-if="activeCategory !== ''">
                            <div>
                                <template x-for="(reinforcerList, subcategory) in groupedReinforcers[activeCategory]" :key="subcategory">
                                    <div class="mb-4">
                                        <h4 class="font-semibold text-sm mb-2" x-text="subcategory"></h4>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="reinforcer in reinforcerList" :key="reinforcer.id">
                                                <button type="button" @click="selectSuggestion(reinforcer)"
                                                        class="px-2 py-1 border rounded text-sm hover:bg-gray-100"
                                                        x-text="reinforcer.name"></button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sessions Section -->
        <div x-show="currentStep === 2" class="my-4 mx-auto p-4">
            <h3 class="text-xl font-bold mb-4">{{ __('Sessions') }}</h3>

            {{-- Toolbar --}}
            <div class="my-4 flex flex-col items-start space-y-4">
                <p>
                    <span>{{ __('Selected Session') }} #</span><span x-text="currentSessionView+1"></span>/<span
                        x-text="sessions.length"></span>
                </p>

                <div class="w-full flex items-center justify-between">
                    <div class="w-full flex items-center text-sm">
                        <button @click="addNewSession()" class=" border border-green-600 rounded-md
                                                px-2 py-1 text-green-600
                                              flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <span>{{ __('Add :resource', ['resource' => __('Session')]) }}</span>
                        </button>
                    </div>

                    <div x-show="sessions.length > 0 && currentSessionView >= 0"
                         class="w-full text-sm flex items-center justify-end space-x-2">
                        <button class="border border-indigo-600 rounded-md px-2 py-1 text-indigo-600
                                              flex items-center space-x-1"
                                type="button" @click="addNewSessionRow(currentSessionView)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <span>{{ __("Add :resource", ['resource' => __('Answer')]) }}</span>
                        </button>

                        <template x-if="reportInfo.sequence_strategy !== 'keep-chosen'">
                            <button class="border border-indigo-600 rounded-md px-2 py-1 text-indigo-600
                                                  flex items-center space-x-1"
                                    type="button" @click="generateSessionRows(currentSessionView)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                     stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <span>{{ __("Precompile Sessions Data") }}</span>
                            </button>
                        </template>

                        <button class="border border-red-500 rounded-md px-2 py-1 text-red-500
                                              flex items-center space-x-1"
                                type="button" @click="removeSession(currentSessionView)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>

                            <span>{{ __("Delete :resource", ['resource' => __('Session')]) }}</span>
                        </button>
                    </div>
                </div>

            </div>
            {{-- Table --}}
            <div class="grid lg:grid-cols-3 gap-4">
                <template x-for="(session, sessionIndex) in sessions" :key="sessionIndex">
                    <div
                        x-show="(displayOnlyCurrentSession && sessionIndex === currentSessionView) || !displayOnlyCurrentSession"
                        @click="switchCurrentSessionView(sessionIndex)"
                        :class="!displayOnlyCurrentSession && sessionIndex === currentSessionView ? 'bg-gray-100' : ''"
                        class="cursor-pointer">
                        <table class="min-w-full border">
                            <thead>
                            <tr>
                                <template x-for="col in sessionsTemplate.columns" :key="col">
                                    <template x-if="col !== 'Sequence Order'">
                                        <th class="border px-4 py-2" x-text="col"></th>
                                    </template>
                                </template>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>

                            <template x-for="(row, rowIndex) in session.answers.rows" :key="rowIndex">

                                <tr class="border">
                                    <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                        <template x-if="sessionsTemplate.columns[cellIndex] !== 'Sequence Order'">
                                            <td class="border px-4 py-2">
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Order'">
                                                <template x-for="(item, index) in cell">
                                                    <span x-text="item.key"></span>
                                                </template>
                                            </template>
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Proposed Items'">
                                                <div class="flex items-center space-x-2">
                                                <template x-for="(proposedItem, proposedItemIndex) in sessions[sessionIndex].answers.rows[rowIndex][cellIndex]" :key="proposedItem">
                                                    <span class="py-1 px-3 text-white rounded-lg text-xl font-bold bg-pink-500" x-text="proposedItem"></span>
                                                </template>
                                                </div>
                                            </template>
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Item'">
                                                <select x-model="sessions[sessionIndex].answers.rows[rowIndex][cellIndex]"
                                                        class="w-full border rounded">
                                                    <option value="">{{ __('Item') }}</option>
                                                    <template x-for="item in items">
                                                        <option
                                                            :selected="sessions[sessionIndex].answers.rows[rowIndex][cellIndex] === item?.key?.toString()"
                                                            :value="item?.key"
                                                            x-text="`#${item?.key} ${item?.name}`"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Choice'">
                                                <select x-model="sessions[sessionIndex].answers.rows[rowIndex][cellIndex]"
                                                        class="w-full border rounded p-1">
                                                    <option value="">{{ __('Choice') }}</option>
                                                    <template x-for="item in getSequenceChoices(sessionIndex, rowIndex)">
                                                        <option
                                                            :selected="sessions[sessionIndex].answers.rows[rowIndex][cellIndex] === item?.key?.toString()"
                                                            :value="item?.key"
                                                            x-text="`#${item?.key} ${item?.name}`"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Answer' && hasLegend">
                                                <select x-model="sessions[sessionIndex].answers.rows[rowIndex][cellIndex]"
                                                        class="w-full border rounded p-1">
                                                    <option value="">{{ __('Answer') }}</option>
                                                    <template x-for="legendItem in legend" :key="legendItem.key">
                                                        <option :selected="cell === legendItem.key"
                                                                :value="legendItem.key"
                                                                x-text="`${legendItem.key}`"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Answer' && !hasLegend">
                                                <input type="text" x-model="session.answers.rows[rowIndex][cellIndex]"
                                                       class="w-full border rounded p-1">
                                            </template>
                                            <template x-if="sessionsTemplate.columns[cellIndex] === 'Order' || sessionsTemplate.columns[cellIndex] === 'Sequence Order'">
                                                <p x-text="rowIndex + 1"></p>
                                            </template>
                                        </td>
                                        </template>
                                    </template>
                                    <td>
                                        <button @click="removeSessionRow(sessionIndex,rowIndex)">X</button>
                                    </td>
                                </tr>


                            </template>


                            </tbody>
                        </table>

                    </div>
                </template>
            </div>
            <div class="mt-4">
                <div class="mb-4 block lg:hidden flex justify-between">
                    <button @click="currentSessionView--"
                            class="flex items-center"
                            :class="currentSessionView !== 0 ? 'visible' : 'invisible'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5"/>
                        </svg>
                        <span>{{__("Previous Session")}}</span>
                    </button>

                    <button @click="currentSessionView++"
                            class="flex items-center"
                            :class="currentSessionView !== sessions.length ? 'visible' : 'invisible'">
                        <span>{{__("Next Session")}}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Section -->
        <div x-show="currentStep === 3" class="my-8 mx-auto max-w-3xl flex flex-col justify-center">
            <h3 class="text-xl font-bold mb-4">{{ __('Report Results') }}</h3>
            <table class="text-2xl">
                <thead>
                <tr>
                    <template x-for="column in report.columns">
                        <th :class="column === 'Item' ? 'text-left' : 'text-center'" x-text="column"></th>
                    </template>
                </tr>
                </thead>
                <tbody>
                <template x-for="row in report.rows">
                    <tr  class="h-12" >
                        <template x-for="(cell, index) in row">
                            <td :class="report.columns[index] === 'Item' ? 'text-left' : 'text-center'">
                                <template x-if="report.columns[index] === 'Item'">
                                    <span x-text="`${items[(cell-1).toString()]?.name}`"></span>
                                </template>
                                <template x-if="report.columns[index] !== 'Item'">
                                    <span x-text="cell"></span>
                                </template>
                            </td>
                        </template>
                    </tr>
                </template>
                </tbody>
            </table>

            <div class="mt-8 flex justify-end">
                @if($datasheet->finalized_at)
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-400">{{ __("Finalized") . ' ' . $datasheet->finalized_at->diffForHumans() }}</span>
                        <form method="POST" action="{{ route('datasheets.update', ['datasheet' => $datasheet ]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="finalized_at" value="">
                            <button class="underline" type="submit">{{ __('Cancel') }}</button>
                        </form>
                    </div>

                @else
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-400">{{  __('Pending') }}</span>
                        <form method="POST" action="{{ route('datasheets.update', ['datasheet' => $datasheet ]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="finalized_at" value="{{ now() }}">
                            <button class="underline" type="submit">{{ __('Finalize') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="flex justify-between px-24">
        <button @click="currentStep--"
                class="flex items-center underline font-bold text-pink-500"
                :class="currentStep !== 0 ? 'visible' : 'invisible'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5"/>
            </svg>
            <span>{{__("Previous Step")}}</span>
        </button>

        <button @click="currentStep++"
                class="flex items-center underline font-bold text-pink-500"
                :class="(currentStep + 1) === steps.length ? 'invisible' : 'visible'">
            <span>{{__("Next Step")}}</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
    </div>
</div>

<script>
    function datasheetReport(initialData, reportInfo, reportData, datasheetType) {
        return {
            // Initialize items and sessions from initialData
            datasheetType: datasheetType,
            report: reportData,
            steps: [
                '{{ __('Info') }}',
                '{{ __('Items') }}',
                '{{ __('Sessions') }}',
                '{{ __('Report') }}',
            ],
            displayInfo: false,
            displayInfoLabel: '{{ __('Mostra Informazioni ...') }}',
            hideInfoLabel: '{{ __('Nascondi Informazioni ...') }}',
            currentStep: 0,
            items: initialData.items && initialData.items.length ? initialData.items : [{
                id: '',
                key: '',
                name: '',
                description: ''
            }],
            sessions: (initialData.sessions && initialData.sessions.length)
                ? initialData.sessions
                : [],
            sessionsTemplate: reportInfo.templates.session.answers,
            reportInfo: reportInfo,
            legend: reportInfo.legend || [],
            hasLegend: reportInfo.has_legend,

            // For auto-completion
            reinforcers: @json($suggestions),
            groupedReinforcers: {},
            activeCategory: '',
            searchItemIsVisible: false,
            itemSearch: null,
            suggestionsVisible: false,
            currentSuggestionIndex: null,
            currentSessionView: 0,
            displayOnlyCurrentSession: false,
            filteredReinforcers: [],
            checkResolution() {
                // When the viewport is at least 1024px (lg and above), we want to show all sessions (displayOnlyCurrentSession = false).
                // Otherwise, on smaller screens, display only the current session.
                this.displayOnlyCurrentSession = !window.matchMedia('(min-width: 1024px)').matches;
            },
            init() {
                window.addEventListener('DOMContentLoaded', () => {
                    // Set the initial value based on current resolution
                    this.checkResolution();
                });
                // Add a listener for window resize to update the value dynamically.
                window.addEventListener('resize', () => {
                    this.checkResolution();
                });
                this.getInitialStep()
                this.groupReinforcers()
            },
            getInitialStep() {
                const hasZeroItemAndSessions = this.items.length === 0 && this.sessions.length === 0;
                const hasRequiredItems = this.items.length >= this.reportInfo.minimum_items;
                const hasSessions = this.sessions.length > 0;

                if (!hasRequiredItems)
                    this.currentStep = 1
                if (!hasSessions && hasRequiredItems)
                    this.currentStep = 2

                if (hasSessions && hasRequiredItems)
                    this.currentStep = 3

                if (hasZeroItemAndSessions)
                    this.currentStep = 0

                this.currentStep = 0
            },
            closeSearchModal() {
                this.searchItemIsVisible = false;
            },
            groupReinforcers() {
                let groups = {};
                this.reinforcers.forEach(reinforcer => {
                    let cat = reinforcer.category;
                    let sub = reinforcer.subcategory;
                    if (!groups[cat]) {
                        groups[cat] = {};
                    }
                    if (!groups[cat][sub]) {
                        groups[cat][sub] = [];
                    }
                    groups[cat][sub].push(reinforcer);
                });
                this.groupedReinforcers = groups;
                // Imposta la categoria attiva al primo gruppo se non è già settata
                if (Object.keys(groups).length > 0 && this.activeCategory === '') {
                    this.activeCategory = Object.keys(groups)[0];
                }
            },
            // Items methods
            checkAutoComplete() {
                let query = this.itemSearch;
                if (query.length >= 1) {
                    this.filteredReinforcers = this.reinforcers.filter(r => r.name.toLowerCase().includes(query.toLowerCase()));
                    this.suggestionsVisible = this.filteredReinforcers.length > 0;
                    // this.currentSuggestionIndex = index;
                } else {
                    this.suggestionsVisible = false;
                }
            },
            selectSuggestion(suggestion) {
                let duplicate = this.items.find((item) => item.id === suggestion.id);
                if (duplicate) {
                    let input = this.$refs['itemInput'];
                    if (input) {
                        input.setCustomValidity("This item is already added.");
                        input.reportValidity();
                        input.setCustomValidity("");
                    }
                    return;
                }
                suggestion.key = this.items.length + 1;
                this.items.push(suggestion);
                this.itemSearch = null;
                this.suggestionsVisible = false;
                this.searchItemIsVisible = false;
            },
            addNewItem() {
                this.searchItemIsVisible = true;
            },
            removeItem(index) {
                this.items.splice(index, 1);
            },
            addSuggestedItems() {
                // Calculate how many items we need to add to reach suggested number
                let needed = this.reportInfo.suggested_items - this.items.length;
                if (needed <= 0) return;
                // Filter available reinforcers that are not already added
                let available = this.reinforcers.filter(r => !this.items.some(item => item.id === r.id));
                // Shuffle available items
                available.sort(() => Math.random() - 0.5);
                for (let i = 0; i < needed; i++) {
                    if (available.length === 0) break;
                    let suggestion = available.pop();
                    this.items.push({...suggestion, key: this.items.length + 1});
                }
            },

            switchCurrentSessionView(sessionIndex) {
                if (!this.displayOnlyCurrentSession)
                    this.currentSessionView = sessionIndex
            },
            // Sessions methods
            addNewSession() {
                if (this.sessions.length === 0)
                    this.currentSessionView = 1;
                // Clone the session template and add a new session object
                const date = new Date().toLocaleString("sv-SE", {
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit",
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit"
                }).replace(" ", "T")
                let newSession = {
                    datetime: date,
                    answers: {
                        columns: this.sessionsTemplate.columns,
                        rows: [
                            // this.sessionsTemplate.columns.map(column => this.determineAnswerValue(column))
                        ]
                    }
                };
                this.sessions.push(newSession);
                this.currentSessionView = this.sessions.length - 1;
            },
            removeSession(sessionIndex) {
                this.sessions.splice(sessionIndex, 1);
                this.currentSessionView = (this.sessions.length > 0) ? sessionIndex - 1 : null;
            },
            determineAnswerValue(column) {
                if (this.reportInfo.sequence_strategy === 'keep-chosen' && column === 'Proposed Items') {
                    const currentSession = this.sessions[this.currentSessionView];
                    const rows = currentSession?.answers.rows
                    if (rows.length === 0)
                        return this.generateSequence()
                    const answerColumn = this.sessionsTemplate.columns.indexOf('Choice');
                    const previousAnswer = rows[rows.length - 1][answerColumn]
                    if (!previousAnswer) {
                        alert('{{ __('To generate a new sequence you must first choose an item for the previous sequence') }}')
                        return;
                    }
                    return this.generateSequence(previousAnswer)
                }

                if (this.reportInfo.sequence_strategy === 'remove-chosen-move-first-at-end' && column === 'Proposed Items') {
                    const currentSession = this.sessions[this.currentSessionView];
                    const rows = currentSession ? currentSession.answers.rows : [];
                    if (rows.length === 0)
                        return this.items.map(item => parseInt(item.key));

                    console.log('rows', rows)
                    const previousRow = rows[rows.length - 1];
                    if (!previousRow)
                        return this.items.map(item => parseInt(item.key))

                    const answerColumn = this.sessionsTemplate.columns.indexOf('Choice');
                    const sequenceColumn = this.sessionsTemplate.columns.indexOf('Proposed Items');
                    console.log('answer column', answerColumn)
                    const previousAnswer = previousRow[answerColumn]
                    const previousSequence = previousRow[sequenceColumn]
                    if (!previousAnswer) {
                        alert('{{ __('To generate a new sequence you must first choose an item for the previous sequence') }}')
                        return;
                    }
                    console.log('previous answer',previousAnswer)
                    return this.generateSequence(previousAnswer, previousSequence)
                }

                if (column === 'Sequence Order') {
                    const currentSession = this.sessions[this.currentSessionView];
                    return currentSession ? currentSession.answers.rows.length + 1 : 0
                }

                return column === 'Proposed Items' ? this.generateSequence() : '';


            },
            getSequenceChoices(sessionIndex, rowIndex) {
                let sequence = this.sessions[sessionIndex].answers.rows[rowIndex][this.sessionsTemplate.columns.indexOf('Proposed Items')]
                if (!sequence)
                    return null;

                return Array.isArray(sequence) && sequence?.map(key => {
                    return this.items.find(item => item.key === key)
                })
            },
            addNewSessionRow(sessionIndex) {
                let newRow = this.sessionsTemplate.columns.map((column) => this.determineAnswerValue(column));
                this.sessions[sessionIndex].answers.rows.push(newRow);
            },
            getRandomElements(arr, n) {
                if (arr.length < n) {
                    throw new Error("Not enough elements in the array");
                }
                let copy = arr.slice();
                for (let i = copy.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [copy[i], copy[j]] = [copy[j], copy[i]];
                }
                return copy.slice(0, n);
            },
            generateSequence(itemsChosen = [], previousSequence = []) {
                switch (this.reportInfo.sequence_strategy) {
                    case 'keep-chosen':
                        const numberOfRandomItemsNeeded = this.reportInfo.sequence_size - itemsChosen?.length;
                        const randomElements = this.getRandomElements(
                            this.items.filter(item => !itemsChosen?.includes(item)),
                            numberOfRandomItemsNeeded
                        )
                            .map(item => parseInt(item.key));
                        return [
                                ...itemsChosen || [],
                                ...randomElements
                            ].map(item => parseInt(item));
                    case 'remove-chosen-move-first-at-end':
                        if (previousSequence.length === 0)
                            return this.items.map(item => item.key)
                        else {
                            const nextSequence = previousSequence.filter(item => item !== parseInt(itemsChosen));
                            nextSequence.push(nextSequence.shift());
                            return nextSequence;
                        }
                    default:
                        return this.getRandomElements(this.items, this.reportInfo.sequence_size).map(item => item.key);
                }
            },
            removeSessionRow(sessionIndex, rowIndex) {
                this.sessions[sessionIndex].answers.rows.splice(rowIndex, 1);
            },
            generateSessionRows(sessionIndex) {
                this.sessions[sessionIndex].answers.rows = [];
                this.items.forEach(
                    item => this.sessions[sessionIndex].answers.rows.push([item.key.toString(), ""])
                )
                switch (datasheetType.id) {
                    case "preference-assessment-si":
                        break;
                    default:
                        this.items.forEach(
                            item => this.sessions[sessionIndex].answers.rows.push([item, ""])
                        )
                        console.log(this.sessions[sessionIndex].answers.rows)
                }
            },
            // Method to save datasheet changes via PUT request
            saveDatasheet() {
                const payload = {data: {items: this.items, sessions: this.sessions}};
                fetch("{{ route('datasheets.update', ['datasheet' => $datasheet->id]) }}", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': "XMLHttpRequest",
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                })
                    .then(response => {
                        if (!response.ok) {
                            console.error("Error updating datasheet.");
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.report = data.data.results
                        console.log("Datasheet updated:", data);
                    })
                    .catch(error => {
                        console.error("Update error:", error);
                    });
            }
        };
    }
</script>
