<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Exception;

class WalletController extends BaseController
{
    /**
     * Deposit money into a tenant's wallet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deposit(Request $request, $userId)
    {
        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:0.01'
            ]);

            // Find user
            $user = User::findOrFail($userId);

            // Ensure user is a tenant
            if (!$user->isTenant()) {
                return $this->sendError('Only tenants can have wallet deposits');
            }

            // Create wallet if it doesn't exist
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
                $user->wallet()->save($wallet);
            }

            // Update balance
            $wallet->balance += $request->amount;
            $wallet->save();

            return $this->sendResponse([
                'user_id' => $user->id,
                'new_balance' => $wallet->balance
            ], 'Deposit successful');
        } catch (Exception $e) {
            return $this->sendError('Deposit failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get wallet balance for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request, $userId)
    {
        try {
            // Find user
            $user = User::findOrFail($userId);

            // Get wallet
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
            }

            return $this->sendResponse([
                'user_id' => $user->id,
                'balance' => $wallet->balance
            ], 'Balance retrieved successfully');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve balance', ['error' => $e->getMessage()]);
        }
    }
}
