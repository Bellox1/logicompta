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

    public function importProcess(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');
        $entrepriseId = $user->entreprise_id;

        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        $header = fgetcsv($handle, 1000, ';');
        if (!$header) return back()->with('error', 'Fichier vide.');

        // Mapping simple
        $map = [];
        foreach ($header as $i => $col) {
            $col = trim(str_replace("\xEF\xBB\xBF", '', mb_strtolower($col)));
            // Numero typos: numro, num, n, etc.
            if (in_array($col, ['numero', 'compte', 'numéro', 'code', 'n', 'no', 'numro', 'num', 'numo', 'numer'])) $map['numero'] = $i;
            // Libelle typos: libell, libel, nom, etc.
            if (in_array($col, ['libelle', 'libellé', 'intitulé', 'label', 'désignation', 'libell', 'libel', 'intitule', 'nom', 'description', 'desc'])) $map['libelle'] = $i;
        }

        if (!isset($map['numero']) || !isset($map['libelle'])) {
            return back()->with('error', 'Les colonnes [numero] et [libelle] sont obligatoires.');
        }

        $allMainAccounts = Account::orderByRaw('LENGTH(code_compte) DESC', [])->get();
        $importedCount = 0;
        $errors = [];

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                if (count($data) < 2) continue;
                
                $numero = trim($data[$map['numero']]);
                $libelle = trim($data[$map['libelle']]);

                if (empty($numero) || empty($libelle)) continue;

                // 1. Skip if already exists as main account
                if (Account::where('code_compte', $numero)->exists()) {
                    continue; // Skip
                }

                // 2. Find parent account (Longest prefix match)
                $parentAccount = null;
                foreach ($allMainAccounts as $main) {
                    if (strpos($numero, $main->code_compte) === 0) {
                        $parentAccount = $main;
                        break;
                    }
                }

                if (!$parentAccount) {
                    $errors[] = "Impossible de trouver un compte parent pour le numéro : $numero";
                    continue;
                }

                // 3. Create or Update for this entreprise
                SousCompte::updateOrCreate(
                    ['entreprise_id' => $entrepriseId, 'numero_sous_compte' => $numero],
                    ['account_id' => $parentAccount->id, 'libelle' => $libelle]
                );
                
                $importedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();
            fclose($handle);

            $msg = "$importedCount sous-comptes importés avec succès.";
            if (!empty($errors)) $msg .= " (Quelques erreurs : " . count($errors) . ")";
            
            return redirect()->route('accounting.account.index')->with('success', $msg);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Erreur lors de l’importation : ' . $e->getMessage());
        }
    }
}
