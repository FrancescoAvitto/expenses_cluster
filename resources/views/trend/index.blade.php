<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trend Spese') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtri Standard -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="GET" action="{{ route('trend') }}" id="filterForm" class="space-y-4">

                    <!-- Filtri base: Anno e Azioni -->
                    <div class="flex gap-4 items-end flex-wrap">
                        <div>
                            <x-input-label for="year" :value="__('Anno')" />
                            <select id="year" name="year"
                                class="border-gray-300 focus:border-gray-500 focus:ring-[#374151] rounded-md shadow-sm mt-1"
                                {{ $useRange ? 'disabled' : '' }}>
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label class="invisible" :value="__('Azioni')" />
                            <div class="flex items-center gap-2 mt-1 h-[42px]">
                                <x-primary-button class="h-full"
                                    id="btnApplyFilter">{{ __('Filtra') }}</x-primary-button>
                                @if($useRange || $year != \Carbon\Carbon::now()->year)
                                    <a href="{{ route('trend') }}"
                                        class="inline-flex items-center h-full px-4 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#374151] focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Reimposta') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Filtri Avanzati (collapsibile) -->
                    <div>
                        <button type="button" id="toggleAdvanced"
                            class="flex items-center gap-2 text-sm font-medium text-[#374151] hover:text-gray-900 focus:outline-none transition">
                            <svg id="arrowIcon" class="w-4 h-4 transition-transform {{ $useRange ? 'rotate-90' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            Filtri Avanzati
                            @if($useRange)
                                <span
                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Intervallo attivo: {{ $periodoLabel }}
                                </span>
                            @endif
                        </button>

                        <div id="advancedFilters"
                            class="{{ $useRange ? '' : 'hidden' }} mt-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500 mb-4">
                                Quando un intervallo è attivo, il filtro Anno viene ignorato.
                            </p>

                            <div class="flex flex-wrap gap-x-6 gap-y-4 items-end">
                                {{-- Intervallo personalizzato --}}
                                <div>
                                    <x-input-label for="date_from" :value="__('Dal')" />
                                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom ?? '' }}"
                                        class="border-gray-300 focus:border-gray-500 focus:ring-[#374151] rounded-md shadow-sm mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="date_to" :value="__('Al')" />
                                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo ?? '' }}"
                                        max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                        class="border-gray-300 focus:border-gray-500 focus:ring-[#374151] rounded-md shadow-sm mt-1" />
                                </div>
                                @if($useRange)
                                    <div>
                                        <x-input-label class="invisible" :value="__('x')" />
                                        <a href="{{ route('trend', ['year' => $year]) }}"
                                            class="inline-flex items-center h-[42px] px-4 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#374151] focus:ring-offset-2 transition ease-in-out duration-150">
                                            Rimuovi intervallo
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stacked Bar Chart -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Totale Spese Mensili</h3>
                    <div class="text-sm text-gray-500">{{ $periodoLabel }}</div>
                </div>

                @if(count($datasets) > 0)
                    <div style="height: 350px; width: 100%;">
                        <canvas id="trendStackedBarChart"></canvas>
                    </div>
                @else
                    <div
                        class="flex items-center justify-center text-gray-400 h-64 border-2 border-dashed border-gray-200 rounded-md">
                        Nessun dato sulle spese per il periodo selezionato.
                    </div>
                @endif
            </div>

            <!-- Line Chart -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Andamento Mensile per Categoria</h3>
                    <div class="text-sm text-gray-500">{{ $periodoLabel }}</div>
                </div>

                @if(count($datasets) > 0)
                    <div class="flex flex-row gap-6 mb-2" style="height: 400px; width: 100%;">
                        <div style="flex: 1; position: relative; min-height: 300px;">
                            <canvas id="trendLineChart"></canvas>
                        </div>
                        <div id="trendLegend"
                            class="w-1/3 sm:w-1/4 md:w-64 overflow-y-auto flex flex-col gap-1 pr-1 border-l pl-4 border-gray-100">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 text-center">Puoi cliccare sulle categorie nella lista a destra per
                        accenderle/spegnerle e confrontare i trend. <br>Clicca su CTRL + rotellina mouse per zoomare, SHIFT
                        + rotellina mouse per scrollare orizzontalmente.</p>
                @else
                    <div
                        class="flex items-center justify-center text-gray-400 h-64 border-2 border-dashed border-gray-200 rounded-md">
                        Nessun dato sulle spese per il periodo selezionato.
                    </div>
                @endif
            </div>

            <!-- Table Container (Initially Hidden) -->
            <div id="detailsTableContainer" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg hidden mt-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900" id="detailsTableTitle">Dettaglio Spese</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Data</th>
                                <th class="px-6 py-3">Categoria</th>
                                <th class="px-6 py-3">Titolo</th>
                                <th class="px-6 py-3">Importo</th>
                                <th class="px-6 py-3">Note</th>
                                <th class="px-6 py-3">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody">
                            <!-- Filled via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @if(count($datasets) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.1.0/dist/chartjs-plugin-zoom.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rawDatasets = @json($datasets);
                const monthsLabels = @json($monthsLabels);
                const monthKeys = @json($monthKeys);
                const expensesDetails = @json($expensesDetails);

                // 1. Stacked Bar Chart
                const barCtx = document.getElementById('trendStackedBarChart');
                if (barCtx) {
                    // Rimuoviamo l'attributo hidden inserito da PHP per mostrarle tutte by default
                    const barDatasets = JSON.parse(JSON.stringify(rawDatasets));
                    barDatasets.forEach(ds => ds.hidden = false);

                    // Calcolo totali per mese per mostrare la label multi-riga
                    const monthTotals = new Array(monthsLabels.length).fill(0);
                    barDatasets.forEach(ds => {
                        ds.data.forEach((val, idx) => {
                            monthTotals[idx] += val;
                        });
                    });
                    const barMonthsLabels = monthsLabels.map((lbl, idx) => {
                        const totalText = '€ ' + monthTotals[idx].toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        return [totalText, lbl];
                    });

                    const barChart = new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: barMonthsLabels,
                            datasets: barDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'nearest',
                                intersect: true,
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    grid: { display: false }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grid: { borderDash: [2, 4] },
                                    ticks: {
                                        callback: function (value, index, values) {
                                            return '€ ' + value.toLocaleString('it-IT', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                                        }
                                    }
                                }
                            },
                            plugins: {
                                zoom: {
                                    zoom: {
                                        wheel: { enabled: true, modifierKey: 'ctrl' },
                                        pinch: { enabled: true },
                                        mode: 'x'
                                    },
                                    pan: {
                                        enabled: true,
                                        mode: 'x'
                                    }
                                },
                                legend: {
                                    display: false // Nascosta per pulizia, la legenda colori è nella card sotto
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += '€ ' + context.parsed.y.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Gestione Pan tramite Shift + Scroll
                    barCtx.addEventListener('wheel', (e) => {
                        if (e.shiftKey) {
                            e.preventDefault();
                            barChart.pan({ x: -(e.deltaX || e.deltaY) });
                        }
                    }, { passive: false });
                }

                // 2. Line Chart
                const lineCtx = document.getElementById('trendLineChart');
                if (lineCtx) {
                    const chart = new Chart(lineCtx, {
                        type: 'line',
                        data: {
                            labels: monthsLabels,
                            datasets: rawDatasets
                        },
                        options: {
                            onClick: (e, elements, chart) => {
                                if (elements.length > 0) {
                                    const el = elements[0];
                                    const datasetIndex = el.datasetIndex;
                                    const index = el.index;
                                    
                                    const dataset = chart.data.datasets[datasetIndex];
                                    const catName = dataset.label;
                                    const monthKey = monthKeys[index];
                                    // estrai solo il mese dalla label se ha il totale
                                    const monthName = (typeof monthsLabels[index] === 'string') ? monthsLabels[index] : monthsLabels[index][0];
                                    
                                    renderExpensesTable(catName, monthKey, monthName);
                                }
                            },
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'nearest',
                                intersect: true,
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        borderDash: [2, 4]
                                    },
                                    ticks: {
                                        callback: function (value, index, values) {
                                            return '€ ' + value.toLocaleString('it-IT', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                zoom: {
                                    zoom: {
                                        wheel: { enabled: true, modifierKey: 'ctrl' },
                                        pinch: { enabled: true },
                                        mode: 'x'
                                    },
                                    pan: {
                                        enabled: true,
                                        mode: 'x'
                                    }
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += '€ ' + context.parsed.y.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Gestione Pan tramite Shift + Scroll
                    lineCtx.addEventListener('wheel', (e) => {
                        if (e.shiftKey) {
                            e.preventDefault();
                            chart.pan({ x: -(e.deltaX || e.deltaY) });
                        }
                    }, { passive: false });

                    // Custom HTML Legend
                    const datasetTotals = chart.data.datasets.map((ds, index) => {
                        const total = ds.data.reduce((sum, val) => sum + val, 0);
                        return { index, total, label: ds.label, color: ds.borderColor };
                    });

                    // Ordina per totale decrescente
                    datasetTotals.sort((a, b) => b.total - a.total);

                    const legendEl = document.getElementById('trendLegend');
                    if (legendEl) {
                        datasetTotals.forEach((dsInfo) => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.title = 'Mostra/Nascondi ' + dsInfo.label;
                            item.style.cssText = [
                                'display:flex', 'align-items:center', 'gap:8px',
                                'text-align:left', 'width:100%', 'padding:5px 8px',
                                'border-radius:6px', 'border:none', 'background:transparent',
                                'cursor:pointer', 'font-size:0.875rem', 'line-height:1.25',
                                'transition:background 0.2s, opacity 0.2s'
                            ].join(';');

                            const dot = document.createElement('span');
                            dot.style.cssText = `flex-shrink:0;width:12px;height:12px;border-radius:50%;background:${dsInfo.color}`;

                            const text = document.createElement('span');
                            text.style.overflow = 'hidden';
                            text.style.textOverflow = 'ellipsis';
                            text.style.whiteSpace = 'nowrap';
                            text.style.flex = '1';
                            text.textContent = dsInfo.label;

                            const val = document.createElement('span');
                            val.style.cssText = 'flex-shrink:0;color:#374151;font-weight:600;white-space:nowrap;font-size:0.75rem';
                            val.textContent = '€\u00a0' + dsInfo.total.toLocaleString('it-IT', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

                            item.appendChild(dot);
                            item.appendChild(text);
                            item.appendChild(val);

                            // Init hidden style
                            if (chart.isDatasetVisible(dsInfo.index) === false) {
                                item.style.opacity = '0.45';
                            }

                            item.addEventListener('mouseenter', () => { if (chart.isDatasetVisible(dsInfo.index)) item.style.background = '#f9fafb'; });
                            item.addEventListener('mouseleave', () => item.style.background = 'transparent');

                            // Toggle Dataset Visibility
                            item.addEventListener('click', () => {
                                const isVisible = chart.isDatasetVisible(dsInfo.index);
                                if (isVisible) {
                                    chart.hide(dsInfo.index);
                                    item.style.opacity = '0.45';
                                    item.style.background = 'transparent';
                                } else {
                                    chart.show(dsInfo.index);
                                    item.style.opacity = '1';
                                }
                                applyFillLogic();
                            });

                            legendEl.appendChild(item);
                        });

                        // Logica aggiuntiva per Area Fill singolo
                        function applyFillLogic() {
                            let visibleCount = 0;
                            let visibleIndex = -1;
                            chart.data.datasets.forEach((ds, idx) => {
                                if (chart.isDatasetVisible(idx)) {
                                    visibleCount++;
                                    visibleIndex = idx;
                                }
                                ds.fill = false;
                                ds.backgroundColor = ds.borderColor; // Ripristina originario nel caso
                            });

                            if (visibleCount === 1 && visibleIndex !== -1) {
                                chart.data.datasets[visibleIndex].fill = true;
                                let color = chart.data.datasets[visibleIndex].borderColor;
                                // Conversione alpha semplificata per un colore hex
                                if (color && typeof color === 'string' && color.startsWith('#') && color.length === 7) {
                                    chart.data.datasets[visibleIndex].backgroundColor = color + '33'; // 20% alpha
                                } else {
                                    chart.data.datasets[visibleIndex].backgroundColor = (typeof color === 'string' ? color : '#374151');
                                }
                            }
                            chart.update();
                        }

                        // Esecuzione iniziale
                        applyFillLogic();
                    }
                }

                function renderExpensesTable(catName, monthKey, monthName) {
                    const container = document.getElementById('detailsTableContainer');
                    const title = document.getElementById('detailsTableTitle');
                    const tbody = document.getElementById('detailsTableBody');
                    
                    const details = (expensesDetails[catName] && expensesDetails[catName][monthKey]) ? expensesDetails[catName][monthKey] : [];
                    
                    title.textContent = `Dettaglio Spese: ${catName} a ${monthName}`;
                    
                    if (details.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-400">Nessuna spesa loggata qui.</td></tr>';
                    } else {
                        let html = '';
                        // get CSRF token from page meta tag if it exists (for delete forms)
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        
                        details.forEach(exp => {
                            html += `
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">${exp.date}</td>
                                    <td class="px-6 py-4">${catName}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">${exp.title || ''}</td>
                                    <td class="px-6 py-4 font-bold whitespace-nowrap">€ ${exp.amount}</td>
                                    <td class="px-6 py-4 truncate max-w-xs" title="${exp.notes || ''}">${exp.notes || ''}</td>
                                    <td class="px-6 py-4 flex gap-3">
                                        <a href="${exp.edit_url}" class="text-orange-600 hover:text-orange-500 active:text-orange-700 font-medium transition-colors">Modifica</a>
                                        <form action="${exp.destroy_url}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa spesa?');">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Elimina</button>
                                        </form>
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    }
                    
                    container.classList.remove('hidden');
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        </script>
    @endif

    <!-- Advanced Filter Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('toggleAdvanced');
            const panel = document.getElementById('advancedFilters');
            const arrow = document.getElementById('arrowIcon');
            const dateFromEl = document.getElementById('date_from');
            const dateToEl = document.getElementById('date_to');
            const yearEl = document.getElementById('year');

            if (toggle && panel) {
                toggle.addEventListener('click', function () {
                    panel.classList.toggle('hidden');
                    arrow.classList.toggle('rotate-90');
                });
            }

            function syncDisabled() {
                const hasRange = (dateFromEl && dateFromEl.value) && (dateToEl && dateToEl.value);
                if (yearEl) yearEl.disabled = hasRange;
            }

            if (dateFromEl) dateFromEl.addEventListener('change', syncDisabled);
            if (dateToEl) dateToEl.addEventListener('change', syncDisabled);

            const form = document.getElementById('filterForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const from = dateFromEl ? dateFromEl.value : '';
                    const to = dateToEl ? dateToEl.value : '';
                    if (from && to && from > to) {
                        e.preventDefault();
                        alert('La data di inizio deve essere precedente o uguale alla data di fine.');
                    }
                    if (dateFromEl && !dateFromEl.value) dateFromEl.removeAttribute('name');
                    if (dateToEl && !dateToEl.value) dateToEl.removeAttribute('name');
                });
            }
        });
    </script>
</x-app-layout>