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
        $journals = Journal::where(function($q) use ($user) {
            $q->where('entreprise_id', $user->entreprise_id)
              ->orWhereNull('entreprise_id');
        })->get();
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
        
        // On ne modifie pas les journaux globaux sauf si on est admin (optionnel)
        if ($journal->entreprise_id != $user->entreprise_id && !is_null($journal->entreprise_id)) {
            abort(403);
        }

        return view('accounting.journals-settings.edit', compact('journal'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $journal = Journal::findOrFail($id);
        if ($journal->entreprise_id != $user->entreprise_id && !is_null($journal->entreprise_id)) {
            abort(403);
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
        if ($journal->entreprise_id != $user->entreprise_id && !is_null($journal->entreprise_id)) {
            abort(403);
        }
        
        // Vérifier si le journal a des entrées
        if ($journal->entries()->exists()) {
            return back()->with('error', 'Impossible de supprimer un journal qui contient des écritures.');
        }

        $journal->delete();
        return redirect()->route('accounting.journals-settings.index')->with('success', 'Journal supprimé.');
    }
}
