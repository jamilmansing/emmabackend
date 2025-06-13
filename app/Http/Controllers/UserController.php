<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    // Step 1: Create a pending user
    public function createTempUser(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string',
            'dateOfBirth' => 'required|date',
            'contactNumber' => 'required|string',
            'emailAddress' => 'required|email|unique:users,email',
            // 'accountType' => 'required|string',
        ]);

        $user = User::create([
            'name' => $validated['fullName'],
            'date_of_birth' => $validated['dateOfBirth'],
            'contact_number' => $validated['contactNumber'],
            'email' => $validated['emailAddress'],
            // 'account_type' => $validated['accountType'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'userId' => $user->id,
        ]);
    }

    // Step 2: Complete registration (set status to active)
    public function completeRegistration(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->status = 'active';
        $user->save();

        return response()->json(['success' => true]);
    }

    // Cleanup endpoint
    public function cleanupPendingUsers()
    {
        $cutoff = Carbon::now()->subHour();
        $deleted = User::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->delete();

        return response()->json(['deleted' => $deleted]);
    }
}