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
        
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $categoryId = $request->input('category_id');

        $categories = \App\Models\Category::whereNull('created_by')
            ->orWhere('created_by', auth()->id())
            ->orderBy('name', 'asc')
            ->get();

        $query = Expense::with('category')
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year);

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
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

        $sortBy = $request->input('sort_by', 'expense_date');
        $sortDir = $request->input('sort_dir', 'desc');

        if ($sortDir === 'asc') {
            if ($sortBy === 'category') {
                $expenses = $expenses->sortBy(function($expense) { return strtolower($expense->category->name); });
            } else {
                $expenses = $expenses->sortBy(function($expense) use ($sortBy) { return is_string($expense->$sortBy) ? strtolower($expense->$sortBy) : $expense->$sortBy; });
            }
        } else {
            if ($sortBy === 'category') {
                $expenses = $expenses->sortByDesc(function($expense) { return strtolower($expense->category->name); });
            } else {
                $expenses = $expenses->sortByDesc(function($expense) use ($sortBy) { return is_string($expense->$sortBy) ? strtolower($expense->$sortBy) : $expense->$sortBy; });
            }
        }
        $expenses = $expenses->values();

        $total = $expenses->sum('amount');
        $categoryMapping = $categories->pluck('id', 'name');

        return view('dashboard', compact('expenses', 'expensesByCategory', 'total', 'month', 'year', 'categories', 'categoryId', 'categoryMapping', 'sortBy', 'sortDir'));
    }
}
