<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    public function index() {
        return redirect()->route('dashboard');
    }

    public function create() {
        $categories = Category::whereNull('created_by')->orWhere('created_by', auth()->id())->orderBy('name', 'asc')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request) {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        Expense::create($validated);
        
        if ($request->input('action') === 'save_and_add') {
            return redirect()->route('expenses.create')
                ->withInput(['expense_date' => $request->expense_date])
                ->with('success', 'Spesa inserita! Puoi aggiungerne un\'altra.');
        }

        return redirect()->route('dashboard')->with('success', 'Spesa inserita!');
    }

    public function edit(Expense $expense) {
        Gate::authorize('update', $expense);
        $categories = Category::whereNull('created_by')->orWhere('created_by', auth()->id())->orderBy('name', 'asc')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense) {
        $expense->update($request->validated());
        return redirect()->route('dashboard')->with('success', 'Spesa aggiornata!');
    }

    public function destroy(Expense $expense) {
        Gate::authorize('delete', $expense);
        $expense->delete();
        return redirect()->route('dashboard')->with('success', 'Spesa eliminata!');
    }
}
