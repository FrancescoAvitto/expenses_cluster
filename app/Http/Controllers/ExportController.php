<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        $user = $request->user();
        
        $month = $request->input('month', Carbon::now()->month);
        $year  = $request->input('year', Carbon::now()->year);
        $categoryId = $request->input('category_id');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $useRange = $dateFrom && $dateTo;

        $query = Expense::with('category');

        if ($useRange) {
            $query->whereDate('expense_date', '>=', $dateFrom)
                  ->whereDate('expense_date', '<=', $dateTo);
        } else {
            $query->whereMonth('expense_date', $month)
                  ->whereYear('expense_date', $year);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $expenses = $query->get();

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

        $filename = $useRange
            ? 'spese_' . str_replace('-', '', $dateFrom) . '_' . str_replace('-', '', $dateTo) . '.csv'
            : "spese_{$year}_{$month}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Data', 'Categoria', 'Titolo', 'Importo', 'Note'];

        $callback = function() use($expenses, $columns) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for proper Excel rendering
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';'); // EU format delimiter

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->id,
                    $expense->expense_date->format('Y-m-d'),
                    $expense->category->name,
                    $expense->title,
                    number_format($expense->amount, 2, ',', ''),
                    $expense->notes
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
