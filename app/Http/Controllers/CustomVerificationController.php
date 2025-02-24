<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class CustomVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        // Find the user based on the id in the URL
        $user = User::findOrFail($id);

        // Verify the hash matches the user's email hash
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        // Mark the email as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['success' => true, 'message' => 'Email verified successfully.'], 200);
    }
}
