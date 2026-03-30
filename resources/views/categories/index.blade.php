<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestione Categorie') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Elenco Categorie</h3>
                    <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-[#374151] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800">Nuova Categoria</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Colore</th>
                                <th class="px-6 py-3">Nome Categoria</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $category->id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full border border-gray-200" style="background-color: {{ $category->color ?? '#374151' }};"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                                    <td class="px-6 py-4">
                                        @if(is_null($category->created_by))
                                            <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded-full text-xs">Globale</span>
                                        @else
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Personale</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 flex gap-3">
                                        @can('update', $category)
                                        <a href="{{ route('categories.edit', $category) }}" class="text-[#374151] hover:text-gray-900 font-medium">Modifica</a>
                                        @endcan
                                        
                                        @can('delete', $category)
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Sicuro di voler eliminare questa categoria? Potrebbe essere in uso da alcune spese!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Elimina</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Nessuna categoria trovata.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
