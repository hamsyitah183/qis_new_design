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
        // dd($request->all());
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

        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email, $request->type));

        // return back()->with('status', 'Password reset link has been sent to your email!',);
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
        return view('pages.authentication.reset_password', compact('token', 'email', 'type', 'title'));
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

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
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

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password reset successfully!');
    }
}
