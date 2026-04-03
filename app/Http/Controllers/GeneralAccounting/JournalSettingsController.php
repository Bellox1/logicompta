<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Grâce au trait BelongsToEntreprise, Journal::all() filtre déjà par active_entreprise_id
        $journals = Journal::all();
        return view('accounting.journals-settings.index', compact('journals'));
    }

    public function create()
    {
        return view('accounting.journals-settings.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        Journal::create([
            'name' => $request->name,
            'description' => $request->description,
            'entreprise_id' => $user->entreprise_id,
        ]);

        return redirect()->route('accounting.journals-settings.index')->with('success', 'Journal créé avec succès.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $journal = Journal::findOrFail($id);
        
        // La vérification est déjà en partie gérée par le Global Scope du trait,
        // mais on s'assure que l'utilisateur ne tente pas d'accéder à un journal global ou d'autrui.
        if ($journal->entreprise_id != $user->entreprise_id) {
            abort(403, "Accès non autorisé à ce journal.");
        }

        return view('accounting.journals-settings.edit', compact('journal'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $journal = Journal::findOrFail($id);
        if ($journal->entreprise_id != $user->entreprise_id) {
            abort(403, "Accès non autorisé.");
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $journal->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('accounting.journals-settings.index')->with('success', 'Journal mis à jour.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $journal = Journal::findOrFail($id);
        if ($journal->entreprise_id != $user->entreprise_id) {
            abort(403, "Accès non autorisé.");
        }
        
        // Vérifier si le journal a des entrées
        if ($journal->entries()->exists()) {
            return back()->with('error', 'Impossible de supprimer un journal qui contient des écritures.');
        }

        $journal->delete();
        return redirect()->route('accounting.journals-settings.index')->with('success', 'Journal supprimé.');
    }
}
