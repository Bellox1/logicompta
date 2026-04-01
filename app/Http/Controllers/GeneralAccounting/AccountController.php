<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use App\Models\SousCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }

        $accounts = Account::with(['sousComptes' => function($query) use ($user) {
            $query->where('entreprise_id', $user->entreprise_id);
        }])->orderBy('code_compte', 'asc')->get()->groupBy('classe');

        // Extraer todos los sous-comptes para la tabla separada
        $allSousComptes = SousCompte::with('account')
            ->where('entreprise_id', $user->entreprise_id)
            ->get();

        return view('accounting.account.index', compact('accounts', 'allSousComptes'));
    }

    public function storeSousCompte(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }

        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'numero_sous_compte' => 'required|string|max:50',
            'libelle' => 'required|string|max:255',
        ]);

        // Vérifier si le numéro existe déjà en tant que compte principal
        $isMainAccount = Account::where('code_compte', $request->numero_sous_compte)->exists();
        if ($isMainAccount) {
            return back()->with('error', 'Le numéro de sous-compte ne peut pas être identique à un numéro de compte existant.')->withInput();
        }

        // Vérifier si le numéro existe déjà en tant que sous-compte pour cette entreprise
        $existsS = SousCompte::where('entreprise_id', $user->entreprise_id)
            ->where('numero_sous_compte', $request->numero_sous_compte)
            ->exists();
            
        if ($existsS) {
            return back()->with('error', 'Ce numéro de sous-compte existe déjà pour votre entreprise.')->withInput();
        }

        SousCompte::create([
            'entreprise_id' => $user->entreprise_id,
            'account_id' => $request->account_id,
            'numero_sous_compte' => $request->numero_sous_compte,
            'libelle' => $request->libelle,
        ]);

        return back()->with('success', 'Sous-compte créé avec succès.');
    }

    public function updateSousCompte(Request $request, $id)
    {
        $user = Auth::user();
        $sousCompte = SousCompte::where('entreprise_id', $user->entreprise_id)->findOrFail($id);

        $request->validate([
            'numero_sous_compte' => 'required|string|max:50',
            'libelle' => 'required|string|max:255',
        ]);

        // Vérifier si le numéro existe déjà en tant que compte principal
        $isMainAccount = Account::where('code_compte', $request->numero_sous_compte)->exists();
        if ($isMainAccount) {
            return back()->with('error', 'Le numéro de sous-compte ne peut pas être identique à un numéro de compte existant.');
        }

        // Vérifier si le numéro existe déjà en tant que sous-compte pour cette entreprise (autre que lui-même)
        $existsS = SousCompte::where('entreprise_id', $user->entreprise_id)
            ->where('numero_sous_compte', $request->numero_sous_compte)
            ->where('id', '!=', $id)
            ->exists();
            
        if ($existsS) {
            return back()->with('error', 'Ce numéro de sous-compte existe déjà pour votre entreprise.');
        }

        $sousCompte->update([
            'numero_sous_compte' => $request->numero_sous_compte,
            'libelle' => $request->libelle,
        ]);

        return back()->with('success', 'Sous-compte mis à jour avec succès.');
    }

    public function destroySousCompte($id)
    {
        $user = Auth::user();
        $sousCompte = SousCompte::where('entreprise_id', $user->entreprise_id)->findOrFail($id);
        
        $sousCompte->delete();

        return back()->with('success', 'Sous-compte supprimé avec succès.');
    }

    /* --- IMPORT METHODS --- */

    public function importForm()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');
        return view('accounting.account.import');
    }

    public function importPreview(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');
        
        $request->validate(['file' => 'required|file']); // Relaxed validation
        $file = $request->file('file');
        
        // Detect delimiter efficiently
        $fp = fopen($file->getRealPath(), 'r');
        $firstLine = fgets($fp);
        fclose($fp);
        
        $delimiters = [';', ',', "\t"];
        $delimiter = ';';
        $maxCount = 0;
        foreach ($delimiters as $d) {
            $count = substr_count($firstLine, $d);
            if ($count > $maxCount) {
                $maxCount = $count;
                $delimiter = $d;
            }
        }

        $handle = fopen($file->getRealPath(), 'r');
        // Handle BOM and read header
        $header = fgetcsv($handle, 1000, $delimiter);
        if (!$header) {
            if ($handle) fclose($handle);
            return back()->with('error', 'Fichier vide ou illisible.');
        }

        // Robust mapping
        $map = [];
        foreach ($header as $i => $col) {
            // Remove BOM and everything above 127
            $cleanCol = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', mb_strtolower(trim($col)));
            if (empty($cleanCol)) $cleanCol = mb_strtolower(trim($col)); // Fallback if preg_replace was too aggressive

            if (preg_match('/^(numero|compte|numro|num|n|no|code|numer|numo|sous-compte)$/', $cleanCol)) $map['numero'] = $i;
            if (preg_match('/^(libelle|libell|libel|intitul|label|nom|description|desc|intitule|designation|libell du compte|nom du compte)$/', $cleanCol)) $map['libelle'] = $i;
        }

        // Broader second pass
        if (!isset($map['numero']) || !isset($map['libelle'])) {
            foreach ($header as $i => $col) {
                $colLow = mb_strtolower(trim($col));
                if (!isset($map['numero']) && (str_contains($colLow, 'num') || str_contains($colLow, 'compte'))) $map['numero'] = $i;
                if (!isset($map['libelle']) && (str_contains($colLow, 'lib') || str_contains($colLow, 'nom') || str_contains($colLow, 'desc'))) $map['libelle'] = $i;
            }
        }

        if (!isset($map['numero']) || !isset($map['libelle'])) {
            fclose($handle);
            $found_cols = implode(', ', $header);
            return back()->with('error', "Colonnes obligatoires [NUMERO, LIBELLE] manquantes. Colonnes détectées : [$found_cols] dans un fichier utilisant le délimiteur '$delimiter'.");
        }

        $allMainAccounts = Account::orderByRaw('LENGTH(code_compte) DESC', [])->get();
        $previewData = [];
        $lineNum = 1;

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $lineNum++;
            if (empty($data) || count($data) <= max(array_values($map))) continue;
            
            $numeroRaw = $data[$map['numero']];
            $numero = preg_replace('/[^a-zA-Z0-9]/', '', (string)$numeroRaw);
            $libelle = trim($data[$map['libelle']]);

            if (empty($numero) || empty($libelle)) continue;

            $isMainAccount = Account::where('code_compte', $numero)->exists();
            $parentAccount = null;
            if (!$isMainAccount) {
                foreach ($allMainAccounts as $main) {
                    if (str_starts_with((string)$numero, (string)$main->code_compte)) {
                        $parentAccount = $main;
                        break;
                    }
                }
            }

            $exists = SousCompte::where('entreprise_id', $user->entreprise_id)
                ->where('numero_sous_compte', $numero)
                ->first();

            $previewData[] = [
                'line' => $lineNum,
                'numero' => $numero,
                'libelle' => $libelle,
                'is_main' => $isMainAccount,
                'parent' => $parentAccount ? $parentAccount->code_compte : null,
                'parent_id' => $parentAccount ? $parentAccount->id : null,
                'status' => $isMainAccount ? 'error_main' : ($parentAccount ? ($exists ? 'update' : 'new') : 'error_no_parent')
            ];
        }
        fclose($handle);

        if (empty($previewData)) return back()->with('error', 'Aucune donnée valide à importer.');

        session(['pending_account_import' => $previewData]);

        return view('accounting.account.import-preview', compact('previewData'));
    }

    public function importProcess(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');
        $entrepriseId = $user->entreprise_id;

        $previewData = session('pending_account_import');
        if (!$previewData) return redirect()->route('accounting.account.import')->with('error', 'Session d’importation expirée.');

        $importedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($previewData as $row) {
                if ($row['status'] === 'error_main' || $row['status'] === 'error_no_parent') {
                    $errors[] = "[Ligne {$row['line']}] Compte n° {$row['numero']} : " . 
                        ($row['status'] === 'error_main' ? "Identique à un compte principal." : "Aucun compte parent trouvé.");
                    continue;
                }

                $sc = SousCompte::updateOrCreate(
                    ['entreprise_id' => $entrepriseId, 'numero_sous_compte' => $row['numero']],
                    ['account_id' => $row['parent_id'], 'libelle' => $row['libelle']]
                );
                
                if ($sc->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
                $importedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();
            session()->forget('pending_account_import');

            $msg = "$createdCount nouveaux sous-comptes ajoutés.";
            if ($updatedCount > 0) $msg .= " $updatedCount existants mis à jour.";
            
            $redirect = redirect()->route('accounting.account.index')->with('success', $msg);
            if (!empty($errors)) $redirect->with('warnings', $errors);
            
            return $redirect;

        } catch (\Exception $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Erreur lors de l’importation : ' . $e->getMessage());
        }
    }
}
