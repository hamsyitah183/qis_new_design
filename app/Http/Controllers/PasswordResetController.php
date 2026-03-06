<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\InternalUser;
use App\Models\PublicUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Notifications\ResetPassNotification;

class PasswordResetController extends Controller
{
    //
    function resetPage()
    {
        return view('pages.authentication.forgot_password', [
            'title' => 'Forgot Password'
        ]);
    }

    // Send reset email
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:public,internal',
        ]);

        $model = $request->type === 'internal' ? InternalUser::class : PublicUser::class;
        $user = $model::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with that email.']);
        }

        $token = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        // Use notification instead of Mail::send()
        $user->notify(new ResetPassNotification($token, $request->email, $request->type));

        return response()->json([
            'message' => 'Password reset link has been sent to your email!'
        ]);
    }

    // Show reset password form
    public function showResetForm($token)
    {
        $email = request('email');
        $type = request('type');
        $title = 'Reset Password';

        // Check if the token exists and is valid for the given email
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if ($resetRecord) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            session(['password_reset_auth_' . $token => $email]);

            return view('pages.authentication.reset_password', compact('token', 'email', 'type', 'title'));
        }

        // used or expired
        abort(403, 'This password reset link is invalid or has already been used.');
    }

    // Handle reset
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required',
            'type' => 'required|in:public,internal',
        ]);

        // Verify that this browser session is authorized to reset for this token
        if (session('password_reset_auth_' . $request->token) !== $request->email) {
            return back()->withErrors(['email' => 'Invalid or expired token.']);
        }

        $model = $request->type === 'internal' ? InternalUser::class : PublicUser::class;
        $user = $model::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clean up the session authorization
        session()->forget('password_reset_auth_' . $request->token);

        return redirect()->route('login')->with('status', 'Password reset successfully!');
    }
}