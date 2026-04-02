<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Category;
use Carbon\Carbon;

class TrendController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $useRange = $dateFrom && $dateTo;

        $year = $request->input('year', Carbon::now()->year);

        $categories = Category::whereNull('created_by')
            ->orWhere('created_by', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        // Recupero gli anni disponibili con dati (per l'utente attuale o tutti se admin)
        $availableYears = Expense::when(!$user->hasRole('admin'), function($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([Carbon::now()->year]);
        }

        $query = Expense::with('category');

        if ($useRange) {
            $query->whereDate('expense_date', '>=', $dateFrom)
                  ->whereDate('expense_date', '<=', $dateTo);
        } else {
            $query->whereYear('expense_date', $year);
        }

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $expenses = $query->orderBy('expense_date', 'asc')->get();

        $monthKeys = [];
        $monthsLabels = [];
        $mesiNomi = [1=>'Gen', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mag', 6=>'Giu',
                     7=>'Lug', 8=>'Ago', 9=>'Set', 10=>'Ott', 11=>'Nov', 12=>'Dic'];

        if ($useRange) {
            $start = Carbon::parse($dateFrom)->startOfMonth();
            // Utilizziamo fine del mese per evitare problemi di comparazione limite
            $end = Carbon::parse($dateTo)->endOfMonth();
            while ($start->lte($end)) {
                $monthKeys[] = $start->format('Y-m');
                $m = $start->month;
                $y = $start->year;
                $monthsLabels[] = $mesiNomi[$m] . ($y != date('Y') ? ' ' . $y : '');
                $start->addMonth();
            }
            $periodoLabel = Carbon::parse($dateFrom)->format('d/m/Y') . ' – ' . Carbon::parse($dateTo)->format('d/m/Y');
        } else {
            $isCurrentYear = ($year == Carbon::now()->year);
            $maxMonth = $isCurrentYear ? Carbon::now()->month : 12;

            for ($i = 1; $i <= $maxMonth; $i++) {
                $monthKeys[] = sprintf('%04d-%02d', $year, $i);
                $monthsLabels[] = $mesiNomi[$i];
            }
            $periodoLabel = 'Anno ' . $year;
        }

        $datasetsData = [];
        $categoryColors = $categories->pluck('color', 'name');
        
        // Inizializzazione dati
        foreach ($categories as $category) {
            $datasetsData[$category->name] = array_fill_keys($monthKeys, 0);
        }

        $expensesDetails = [];

        // Popolamento dati
        foreach ($expenses as $expense) {
            $key = $expense->expense_date->format('Y-m');
            $catName = $expense->category->name;
            if (isset($datasetsData[$catName][$key])) {
                $datasetsData[$catName][$key] += $expense->amount;

                $expensesDetails[$catName][$key][] = [
                    'id' => $expense->id,
                    'date' => $expense->expense_date->format('d/m/Y'),
                    'title' => $expense->title,
                    'amount' => number_format($expense->amount, 2, ',', '.'),
                    'notes' => $expense->notes,
                    'edit_url' => route('expenses.edit', $expense->id),
                    'destroy_url' => route('expenses.destroy', $expense->id)
                ];
            }
        }

        // Ordina i dataset per totale spesa decrescente
        uasort($datasetsData, function($a, $b) {
            return array_sum($b) <=> array_sum($a);
        });

        // Preparazione per Chart.js (escludendo le categorie a zero su tutto il periodo)
        $datasets = [];
        $visibleCount = 0;
        foreach ($datasetsData as $catName => $data) {
            if (array_sum($data) > 0) {
                $datasets[] = [
                    'label' => $catName,
                    'data' => array_values($data),
                    'borderColor' => $categoryColors[$catName] ?? '#374151',
                    'backgroundColor' => $categoryColors[$catName] ?? '#374151',
                    'tension' => 0.4, // Linee curve
                    'fill' => false,
                    'borderWidth' => 2,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                    'hidden' => $visibleCount >= 5
                ];
                $visibleCount++;
            }
        }

        return view('trend.index', compact(
            'year', 'dateFrom', 'dateTo', 'useRange', 'availableYears',
            'periodoLabel', 'monthsLabels', 'monthKeys', 'datasets', 'expensesDetails'
        ));
    }
}
