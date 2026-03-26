<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filtri Standard -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="GET" action="{{ route('dashboard') }}" id="filterForm" class="space-y-4">

                    <!-- Filtri base: Mese / Anno / Categoria -->
                    <div class="flex gap-4 items-end flex-wrap">
                        <div>
                            <x-input-label for="month" :value="__('Mese')" />
                            <select id="month" name="month" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1" {{ $useRange ? 'disabled' : '' }}>
                                @php
                                    $mesi = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',
                                             7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];
                                @endphp
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ $i }}" @selected($i == $month)>{{ $mesi[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="year" :value="__('Anno')" />
                            <select id="year" name="year" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1" {{ $useRange ? 'disabled' : '' }}>
                                @for($i=\Carbon\Carbon::now()->year + 2; $i>=2020; $i--)
                                    <option value="{{ $i }}" @selected($i == $year)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="category_id" :value="__('Categoria')" />
                            <select id="category_id" name="category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1">
                                <option value="">Tutte le categorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected($category->id == $categoryId)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label class="invisible" :value="__('Azioni')" />
                            <div class="flex items-center gap-2 mt-1 h-[42px]">
                                <x-primary-button class="h-full" id="btnApplyFilter">{{ __('Filtra') }}</x-primary-button>
                                @if($categoryId || $useRange)
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center h-full px-4 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Reimposta') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Filtri Avanzati (collapsibile) -->
                    <div>
                        <button type="button" id="toggleAdvanced"
                            class="flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 focus:outline-none transition">
                            <svg id="arrowIcon" class="w-4 h-4 transition-transform {{ $useRange ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Filtri Avanzati
                            @if($useRange)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Intervallo attivo: {{ $periodoLabel }}
                                </span>
                            @endif
                        </button>

                        <div id="advancedFilters" class="{{ $useRange ? '' : 'hidden' }} mt-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500 mb-4">
                                Quando un intervallo è attivo, i filtri Mese e Anno vengono ignorati.
                            </p>

                            <div class="flex flex-wrap gap-x-6 gap-y-4 items-end">

                                {{-- 1. Selezione anno intero --}}
                                <div class="flex items-end gap-2">
                                    <div>
                                        <x-input-label for="quickYear" :value="__('Anno')" />
                                        @php $quickYear = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->year : \Carbon\Carbon::now()->year; @endphp
                                        <select id="quickYear"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1">
                                            @for($i = \Carbon\Carbon::now()->year + 2; $i >= 2020; $i--)
                                                <option value="{{ $i }}" @selected($i == $quickYear)>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <button type="button" id="btnApplyYear"
                                        class="inline-flex items-center h-[42px] px-4 bg-indigo-50 border border-indigo-300 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest shadow-sm hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Applica anno
                                    </button>
                                </div>

                                <div class="self-stretch border-l border-gray-300 hidden sm:block"></div>

                                {{-- 2. Anno corrente --}}
                                <div>
                                    <x-input-label class="invisible" :value="__('x')" />
                                    <button type="button" id="btnCurrentYear"
                                        class="inline-flex items-center h-[42px] px-4 bg-indigo-50 border border-indigo-300 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest shadow-sm hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        📅 Anno corrente
                                    </button>
                                </div>

                                <div class="self-stretch border-l border-gray-300 hidden sm:block"></div>

                                {{-- 3. Intervallo personalizzato --}}
                                <div>
                                    <x-input-label for="date_from" :value="__('Dal')" />
                                    <input type="date" id="date_from" name="date_from"
                                        value="{{ $dateFrom ?? '' }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="date_to" :value="__('Al')" />
                                    <input type="date" id="date_to" name="date_to"
                                        value="{{ $dateTo ?? '' }}"
                                        max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1" />
                                </div>

                                @if($useRange)
                                    <div>
                                        <x-input-label class="invisible" :value="__('x')" />
                                        <a href="{{ route('dashboard', ['month' => $month, 'year' => $year, 'category_id' => $categoryId]) }}"
                                            class="inline-flex items-center h-[42px] px-4 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Rimuovi intervallo
                                        </a>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>




                </form>
            </div>

            <!-- Stats & Charts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg flex flex-col justify-center items-center">
                    <h3 class="text-lg font-medium text-gray-900">Totale Spese</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $periodoLabel }}</p>
                    <p class="mt-3 text-4xl font-bold text-indigo-600">€ {{ number_format($total, 2, ',', '.') }}</p>
                </div>

                <!-- Pie Chart -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg md:col-span-2">
                    @if($expensesByCategory->count() > 0)
                        <div class="flex gap-4" style="height:16rem">
                            <div class="flex-shrink-0" style="width:16rem;position:relative">
                                <canvas id="categoryPieChart"></canvas>
                            </div>
                            <div id="pieLegend"
                                 style="overflow-y:auto;max-height:100%;flex:1;display:flex;flex-direction:column;gap:6px;padding-right:4px">
                            </div>
                        </div>
                    @else
                        <p class="flex items-center h-64 justify-center text-gray-400">Nessun dato per il grafico</p>
                    @endif
                </div>
            </div>

            <!-- Bar Chart -->
            @if($expensesByCategory->count() > 0)
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg h-80">
                <canvas id="categoryBarChart"></canvas>
            </div>
            @endif

            <!-- Table -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Elenco Spese</h3>
                    <div class="flex gap-4">
                        <a href="{{ route('export.csv', ['month' => $month, 'year' => $year, 'category_id' => $categoryId, 'sort_by' => $sortBy, 'sort_dir' => $sortDir, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                            class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">Esporta CSV</a>
                        <a href="{{ route('expenses.create') }}"
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Inserisci Spesa</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">
                                    <a href="{{ route('dashboard', ['month' => $month, 'year' => $year, 'category_id' => $categoryId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort_by' => 'expense_date', 'sort_dir' => $sortBy == 'expense_date' && $sortDir == 'desc' ? 'asc' : 'desc']) }}" class="flex items-center gap-1 hover:text-indigo-600">
                                        Data
                                        @if($sortBy == 'expense_date')
                                            <span>{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3">
                                    <a href="{{ route('dashboard', ['month' => $month, 'year' => $year, 'category_id' => $categoryId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort_by' => 'category', 'sort_dir' => $sortBy == 'category' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-indigo-600">
                                        Categoria
                                        @if($sortBy == 'category')
                                            <span>{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3">
                                    <a href="{{ route('dashboard', ['month' => $month, 'year' => $year, 'category_id' => $categoryId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort_by' => 'title', 'sort_dir' => $sortBy == 'title' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-indigo-600">
                                        Titolo
                                        @if($sortBy == 'title')
                                            <span>{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3">
                                    <a href="{{ route('dashboard', ['month' => $month, 'year' => $year, 'category_id' => $categoryId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort_by' => 'amount', 'sort_dir' => $sortBy == 'amount' && $sortDir == 'desc' ? 'asc' : 'desc']) }}" class="flex items-center gap-1 hover:text-indigo-600">
                                        Importo
                                        @if($sortBy == 'amount')
                                            <span>{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3">Note</th>
                                <th class="px-6 py-3">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">{{ $expense->category->name }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $expense->title }}</td>
                                    <td class="px-6 py-4 font-bold whitespace-nowrap">€ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 truncate max-w-xs" title="{{ $expense->notes }}">{{ $expense->notes }}</td>
                                    <td class="px-6 py-4 flex gap-3">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Modifica</a>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa spesa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Elimina</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Nessuna spesa trovata per il periodo selezionato.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Scripts -->
    @if($expensesByCategory->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($expensesByCategory);
            const categoryMapping = @json($categoryMapping);
            const categoryColors = @json($categoryColors);
            const labels = Object.keys(chartData);
            const data = Object.values(chartData);

            const activeCategoryId = "{{ $categoryId }}";

            const bgColors = labels.map((label) => {
                const baseColor = categoryColors[label] || '#6366f1';
                if (!activeCategoryId) return baseColor;
                const catId = categoryMapping[label];
                return catId == activeCategoryId ? baseColor : '#d1d5db';
            });

            const hoverBgColors = labels.map((label) => categoryColors[label] || '#6366f1');

            const pieCtx = document.getElementById('categoryPieChart');
            if (pieCtx) {
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{ data: data, backgroundColor: bgColors, hoverBackgroundColor: hoverBgColors, borderWidth: 0, hoverOffset: 4 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        onClick: function(event, activeElements) {
                            if (activeElements.length > 0) {
                                const categoryName = labels[activeElements[0].index];
                                const catId = categoryMapping[categoryName];
                                if (catId) {
                                    document.getElementById('category_id').value = catId;
                                    document.getElementById('filterForm').submit();
                                }
                            }
                        }
                    }
                });

                // Build custom scrollable legend
                const legendEl = document.getElementById('pieLegend');
                if (legendEl) {
                    labels.forEach((label, i) => {
                        const color = hoverBgColors[i];
                        const catId = categoryMapping[label];
                        const amount = data[i];
                        const isActive = activeCategoryId && catId == activeCategoryId;
                        const isDimmed = activeCategoryId && !isActive;

                        const item = document.createElement('button');
                        item.type = 'button';
                        item.title = 'Filtra per ' + label;
                        item.style.cssText = [
                            'display:flex', 'align-items:center', 'gap:8px',
                            'text-align:left', 'width:100%', 'padding:3px 6px',
                            'border-radius:6px', 'border:none', 'background:transparent',
                            'cursor:pointer', 'font-size:0.78rem', 'line-height:1.3',
                            isActive ? 'font-weight:700;background:#eef2ff' : '',
                            isDimmed ? 'opacity:0.45' : ''
                        ].join(';');

                        const dot = document.createElement('span');
                        dot.style.cssText = `flex-shrink:0;width:12px;height:12px;border-radius:50%;background:${color}`;

                        const text = document.createElement('span');
                        text.style.overflow = 'hidden';
                        text.style.textOverflow = 'ellipsis';
                        text.style.whiteSpace = 'nowrap';
                        text.style.flex = '1';
                        text.textContent = label;

                        const val = document.createElement('span');
                        val.style.cssText = 'flex-shrink:0;color:#6366f1;font-weight:600;white-space:nowrap';
                        val.textContent = '€\u00a0' + amount.toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:2});

                        item.appendChild(dot);
                        item.appendChild(text);
                        item.appendChild(val);

                        item.addEventListener('mouseenter', () => item.style.background = '#f5f3ff');
                        item.addEventListener('mouseleave', () => item.style.background = isActive ? '#eef2ff' : 'transparent');

                        item.addEventListener('click', () => {
                            if (catId) {
                                document.getElementById('category_id').value = catId;
                                document.getElementById('filterForm').submit();
                            }
                        });

                        legendEl.appendChild(item);
                    });
                }
            }

            const barCtx = document.getElementById('categoryBarChart');
            if (barCtx) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Totale Speso (€)', data: data, backgroundColor: bgColors, hoverBackgroundColor: hoverBgColors, borderRadius: 4 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                            x: { grid: { display: false } }
                        },
                        onClick: function(event, activeElements) {
                            if (activeElements.length > 0) {
                                const categoryName = labels[activeElements[0].index];
                                const catId = categoryMapping[categoryName];
                                if (catId) {
                                    document.getElementById('category_id').value = catId;
                                    document.getElementById('filterForm').submit();
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif

    <!-- Advanced Filter Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('toggleAdvanced');
            const panel  = document.getElementById('advancedFilters');
            const arrow  = document.getElementById('arrowIcon');
            const dateFromEl = document.getElementById('date_from');
            const dateToEl   = document.getElementById('date_to');
            const monthEl    = document.getElementById('month');
            const yearEl     = document.getElementById('year');

            if (toggle && panel) {
                toggle.addEventListener('click', function () {
                    panel.classList.toggle('hidden');
                    arrow.classList.toggle('rotate-90');
                });
            }

            // When date range inputs change, disable month/year selects
            function syncDisabled() {
                const hasRange = (dateFromEl && dateFromEl.value) && (dateToEl && dateToEl.value);
                if (monthEl) monthEl.disabled = hasRange;
                if (yearEl)  yearEl.disabled  = hasRange;
            }

            if (dateFromEl) dateFromEl.addEventListener('change', syncDisabled);
            if (dateToEl)   dateToEl.addEventListener('change', syncDisabled);

            // "Anno corrente" quick button
            const btnCurrentYear = document.getElementById('btnCurrentYear');
            if (btnCurrentYear) {
                btnCurrentYear.addEventListener('click', function () {
                    const now   = new Date();
                    const year  = now.getFullYear();
                    const pad   = n => String(n).padStart(2, '0');
                    const today = `${year}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
                    if (dateFromEl) dateFromEl.value = `${year}-01-01`;
                    if (dateToEl)   dateToEl.value   = today;
                    syncDisabled();
                    form.submit();
                });
            }

            // "Applica anno" button – full year Jan 1 to Dec 31
            const btnApplyYear  = document.getElementById('btnApplyYear');
            const quickYearEl   = document.getElementById('quickYear');
            if (btnApplyYear && quickYearEl) {
                btnApplyYear.addEventListener('click', function () {
                    const y = quickYearEl.value;
                    if (dateFromEl) dateFromEl.value = `${y}-01-01`;
                    if (dateToEl)   dateToEl.value   = `${y}-12-31`;
                    syncDisabled();
                    form.submit();
                });
            }

            // Validate: date_from <= date_to before submit
            const form = document.getElementById('filterForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const from = dateFromEl ? dateFromEl.value : '';
                    const to   = dateToEl   ? dateToEl.value   : '';
                    if (from && to && from > to) {
                        e.preventDefault();
                        alert('La data di inizio deve essere precedente o uguale alla data di fine.');
                    }
                    // Clear empty date inputs so they don't appear in the URL
                    if (dateFromEl && !dateFromEl.value) dateFromEl.removeAttribute('name');
                    if (dateToEl   && !dateToEl.value)   dateToEl.removeAttribute('name');
                });
            }
        });
    </script>

</x-app-layout>
