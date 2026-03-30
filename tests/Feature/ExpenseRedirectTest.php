<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class ExpenseRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Role::where('name', 'user')->exists()) {
            Role::create(['name' => 'admin']);
            Role::create(['name' => 'user']);
        }
    }

    public function test_store_redirects_to_filtered_dashboard()
    {
        $user = User::factory()->create()->assignRole('user');
        $category = Category::create(['name' => 'Food']);
        
        $expenseDate = '2026-02-15';
        $date = Carbon::parse($expenseDate);

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'expense_date' => $expenseDate,
            'category_id' => $category->id,
            'title' => 'Test Lunch',
            'amount' => 12.50,
            'action' => 'save'
        ]);

        $response->assertRedirect(route('dashboard', [
            'month' => $date->month,
            'year' => $date->year
        ]));
    }

    public function test_update_redirects_to_filtered_dashboard()
    {
        $user = User::factory()->create()->assignRole('user');
        $category = Category::create(['name' => 'Food']);
        $expense = Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Old Lunch',
            'amount' => 10.00,
            'expense_date' => '2026-01-01',
        ]);

        $newDate = '2026-03-20';
        $date = Carbon::parse($newDate);

        $response = $this->actingAs($user)->put(route('expenses.update', $expense), [
            'expense_date' => $newDate,
            'category_id' => $category->id,
            'title' => 'Updated Lunch',
            'amount' => 15.00,
        ]);

        $response->assertRedirect(route('dashboard', [
            'month' => $date->month,
            'year' => $date->year
        ]));
    }

    public function test_store_with_save_and_add_redirects_to_create()
    {
        $user = User::factory()->create()->assignRole('user');
        $category = Category::create(['name' => 'Food']);
        
        $expenseDate = '2026-02-15';

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'expense_date' => $expenseDate,
            'category_id' => $category->id,
            'title' => 'Test Lunch',
            'amount' => 12.50,
            'action' => 'save_and_add'
        ]);

        $response->assertRedirect(route('expenses.create'));
    }
}
