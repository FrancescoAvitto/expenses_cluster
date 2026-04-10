<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('csv_file');
        
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors('Impossibile leggere il file.');
        }

        $user = $request->user();
        
        // Remove UTF-8 BOM if present
        $bom = "\xef\xbb\xbf";
        $firstChars = fread($handle, 3);
        if ($firstChars != $bom) {
            rewind($handle);
        }

        $importedCount = 0;
        $skippedCount = 0;
        $rowNumber = 0;

        while (($row = fgetcsv($handle, 10000, ';')) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            // Expected columns from export: ['ID', 'Data', 'Categoria', 'Titolo', 'Importo', 'Note']
            // Check header
            if ($rowNumber === 1) {
                // If the first row contains 'ID' or 'Data', assume it's the header and skip
                if (strcasecmp(trim($row[0]), 'ID') === 0 || strcasecmp(trim($row[1] ?? ''), 'Data') === 0) {
                    continue;
                }
            }

            if (count($row) < 5) {
                // Not enough columns
                continue;
            }

            $dateStr     = trim($row[1]);
            $categoryName= trim($row[2]);
            $title       = trim($row[3]);
            $amountStr   = trim($row[4]);
            $notes       = isset($row[5]) ? trim($row[5]) : null;
            if ($notes === '') {
                $notes = null;
            }

            // Parse Date
            try {
                $expenseDate = Carbon::createFromFormat('Y-m-d', $dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                // Se il formato non combacia, proviamo con strtotime fallbacks o saltiamo
                try {
                    $expenseDate = Carbon::parse($dateStr)->format('Y-m-d');
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // Parse Amount: replace ',' with '.' and keep safely
            $amountClean = str_replace(',', '.', $amountStr);
            $amount      = (float) $amountClean;

            // Get or create Category
            if (empty($categoryName)) {
                $categoryName = 'Generale'; // fallback se categoria vuota nel csv
            }

            $category = Category::where('name', $categoryName)
                ->where(function ($query) use ($user) {
                    $query->whereNull('created_by')->orWhere('created_by', $user->id);
                })->first();

            if (!$category) {
                $category = Category::create([
                    'name' => $categoryName,
                    'created_by' => $user->id
                ]);
            }

            // Check if expense already exists (duplicates)
            $existing = Expense::where('user_id', $user->id)
                ->whereDate('expense_date', $expenseDate)
                ->where('category_id', $category->id)
                ->where('title', $title)
                ->where('amount', $amount)
                // Usiamo where per le note se non sono null, altrimenti whereNull
                ->where(function($query) use ($notes) {
                    if ($notes === null) {
                        $query->whereNull('notes');
                    } else {
                        $query->where('notes', $notes);
                    }
                })
                ->first();

            if ($existing) {
                $skippedCount++;
            } else {
                Expense::create([
                    'user_id'      => $user->id,
                    'expense_date' => $expenseDate,
                    'category_id'  => $category->id,
                    'title'        => $title,
                    'amount'       => $amount,
                    'notes'        => $notes,
                ]);
                $importedCount++;
            }
        }

        fclose($handle);

        $msg = "Importazione completata: $importedCount spese aggiunte.";
        if ($skippedCount > 0) {
            $msg .= " ($skippedCount spese ignorate perché già presenti).";
        }

        return back()->with('success', $msg);
    }
}
