<?php

namespace App\Http\Controllers;

use App\Models\PointTransaction;
use App\Models\Withdrawal;
use App\Services\PointsService;
use App\Services\WithdrawalService;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $transactions = PointTransaction::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $earnedTotal = (int) PointTransaction::where('user_id', $user->id)
            ->where('delta', '>', 0)
            ->sum('delta');

        return view('wallet', [
            'user'                => $user,
            'balance'             => (int) $user->reputation,
            'availableBalance'    => WithdrawalService::availableBalance($user),
            'withdrawableBalance' => WithdrawalService::withdrawableBalance($user),
            'earnedTotal'         => $earnedTotal,
            'transactions'        => $transactions,
            'withdrawals'         => $withdrawals,
            'minEgp'              => WithdrawalService::MIN_EGP,
            'maxEgp'              => WithdrawalService::MAX_EGP,
            'pointsPerEgp'        => WithdrawalService::POINTS_PER_EGP,
            'rules'               => PointsService::RULES,
        ]);
    }
}
