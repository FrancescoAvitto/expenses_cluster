<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $useRange = $dateFrom && $dateTo;

        $month = $request->input('month', Carbon::now()->month);
        $year  = $request->input('year', Carbon::now()->year);
        $categoryId = $request->input('category_id');

        $categories = \App\Models\Category::whereNull('created_by')
            ->orWhere('created_by', auth()->id())
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

        // Se non ci sono spese, mostro almeno l'anno corrente
        if ($availableYears->isEmpty()) {
            $availableYears = collect([Carbon::now()->year]);
        }

        $query = Expense::with('category');

        if ($useRange) {
            $query->whereDate('expense_date', '>=', $dateFrom)
                  ->whereDate('expense_date', '<=', $dateTo);
        } else {
            $query->whereMonth('expense_date', $month)
                  ->whereYear('expense_date', $year);
        }

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        $allExpenses = $query->orderBy('expense_date', 'desc')->get();

        $expensesByCategory = $allExpenses->groupBy(function($expense) {
            return $expense->category->name;
        })->map(function($group) {
            return $group->sum('amount');
        })->sortByDesc(function($amount) {
            return $amount;
        });

        if ($categoryId) {
            $expenses = $allExpenses->where('category_id', (int)$categoryId);
        } else {
            $expenses = $allExpenses;
        }

        $sortBy  = $request->input('sort_by', 'expense_date');
        $sortDir = $request->input('sort_dir', 'desc');

        // Ordinamento backend di default, l'ordinamento interattivo è in JS
        $expenses = $expenses->sortByDesc('expense_date')->values();

        $expensesDetails = $expenses->map(function($expense) {
            return [
                'id' => $expense->id,
                'date_raw' => $expense->expense_date->format('Y-m-d'),
                'date' => $expense->expense_date->format('d/m/Y'),
                'category' => $expense->category->name,
                'title' => $expense->title,
                'amount_raw' => $expense->amount,
                'amount' => number_format($expense->amount, 2, ',', '.'),
                'notes' => $expense->notes,
                'edit_url' => route('expenses.edit', $expense),
                'destroy_url' => route('expenses.destroy', array_merge(['expense' => $expense->id], request()->query())),
            ];
        });

        $total = $expenses->sum('amount');
        $categoryMapping = $categories->pluck('id', 'name');
        $categoryColors  = $categories->pluck('color', 'name');

        // Label periodo per la card totale
        if ($useRange) {
            $periodoLabel = Carbon::parse($dateFrom)->format('d/m/Y') . ' – ' . Carbon::parse($dateTo)->format('d/m/Y');
        } else {
            $mesi = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',
                     7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];
            $periodoLabel = ($mesi[$month] ?? '') . ' ' . $year;
        }

        return view('dashboard', compact(
            'expenses', 'expensesDetails', 'expensesByCategory', 'total',
            'month', 'year', 'categories', 'categoryId',
            'categoryMapping', 'categoryColors', 'sortBy', 'sortDir',
            'dateFrom', 'dateTo', 'useRange', 'periodoLabel', 'availableYears', 'search'
        ));
    }
}
