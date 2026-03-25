<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    public function help()
    {
        $accounts = Account::orderBy('code_compte', 'asc')->get()->groupBy('classe');
        return view('accounting.help', compact('accounts'));
    }

    public function systemeDate()
    {
        $now = now();
        return response()->json([
            'datetime' => $now->toDateTimeString(),
            'date' => $now->format('d/m/Y'),
            'time' => $now->format('H:i:s')
        ]);
    }
}
