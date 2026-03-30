<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifica Spesa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="expense_date" :value="__('Data Spesa')" />
                        <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', $expense->expense_date->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Categoria')" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-gray-500 focus:ring-[#374151] rounded-md shadow-sm" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $expense->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Titolo/Descrizione')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $expense->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" :value="__('Importo (€)')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $expense->amount)" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Note (Opzionale)')" />
                        <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-gray-500 focus:ring-[#374151] rounded-md shadow-sm">{{ old('notes', $expense->notes) }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Aggiorna Spesa') }}</x-primary-button>
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Annulla</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
