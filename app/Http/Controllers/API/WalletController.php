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
     * @return \Illuminate\Http\Response
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
     * Request withdrawal from a renter's wallet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function requestWithdrawal(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:0.01'
            ]);

            // Get authenticated user (must be a renter)
            $user = $request->user();

            // Ensure user is a renter
            if (!$user->isRenter()) {
                return $this->sendError('Only renters can request withdrawals');
            }

            // Get wallet
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
                $user->wallet()->save($wallet);
            }

            // Check if sufficient balance
            if ($wallet->balance < $request->amount) {
                return $this->sendError('Insufficient balance');
            }

            // Create withdrawal request
            $withdrawalRequest = new WithdrawalRequest([
                'renter_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'pending'
            ]);
            $withdrawalRequest->save();

            return $this->sendResponse([
                'request_id' => $withdrawalRequest->id,
                'amount' => $withdrawalRequest->amount,
                'status' => $withdrawalRequest->status
            ], 'Withdrawal request submitted successfully');
        } catch (Exception $e) {
            return $this->sendError('Withdrawal request failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve a withdrawal request (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $requestId
     * @return \Illuminate\Http\Response
     */
    public function approveWithdrawal(Request $request, $requestId)
    {
        try {
            // Find withdrawal request
            $withdrawalRequest = WithdrawalRequest::findOrFail($requestId);

            // Check if already processed
            if ($withdrawalRequest->status !== 'pending') {
                return $this->sendError('Withdrawal request already processed');
            }

            // Get renter wallet
            $renter = $withdrawalRequest->renter;
            $renterWallet = $renter->wallet;
            if (!$renterWallet) {
                $renterWallet = new Wallet(['user_id' => $renter->id, 'balance' => 0]);
                $renter->wallet()->save($renterWallet);
            }

            // Check if sufficient balance
            if ($renterWallet->balance < $withdrawalRequest->amount) {
                return $this->sendError('Insufficient balance in renter wallet');
            }

            // Deduct from renter wallet
            $renterWallet->balance -= $withdrawalRequest->amount;
            $renterWallet->save();

            // Update withdrawal request status
            $withdrawalRequest->status = 'approved';
            $withdrawalRequest->save();

            return $this->sendResponse([
                'request_id' => $withdrawalRequest->id,
                'amount' => $withdrawalRequest->amount,
                'status' => $withdrawalRequest->status
            ], 'Withdrawal request approved successfully');
        } catch (Exception $e) {
            return $this->sendError('Failed to approve withdrawal request', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a withdrawal request (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $requestId
     * @return \Illuminate\Http\Response
     */
    public function rejectWithdrawal(Request $request, $requestId)
    {
        try {
            // Find withdrawal request
            $withdrawalRequest = WithdrawalRequest::findOrFail($requestId);

            // Check if already processed
            if ($withdrawalRequest->status !== 'pending') {
                return $this->sendError('Withdrawal request already processed');
            }

            // Update withdrawal request status
            $withdrawalRequest->status = 'rejected';
            $withdrawalRequest->save();

            return $this->sendResponse([
                'request_id' => $withdrawalRequest->id,
                'amount' => $withdrawalRequest->amount,
                'status' => $withdrawalRequest->status
            ], 'Withdrawal request rejected successfully');
        } catch (Exception $e) {
            return $this->sendError('Failed to reject withdrawal request', ['error' => $e->getMessage()]);
        }
    }

    /**
     * List withdrawal requests (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listWithdrawalRequests(Request $request)
    {
        try {
            $requests = WithdrawalRequest::with('renter')->paginate(20);

            return $this->sendPaginatedResponse($requests, 'Withdrawal requests retrieved successfully');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve withdrawal requests', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get wallet balance for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Http\Response
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
