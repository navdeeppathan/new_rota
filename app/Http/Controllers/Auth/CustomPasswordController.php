<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class CustomPasswordController extends Controller
{
    // Step 1: Show Forgot Password form
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Step 2: Send simple reset link
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found']);
        }

        // Create reset link
        $link = url('/change-password/' . urlencode($user->email));

        // Send mail
        Mail::raw("Click here to reset your password: $link", function ($message) use ($user) {
            $message->to($user->email)
              ->from('support@marianhouse.co.uk', 'Marion House')
                    ->subject('Reset Password Link');
        });

        return back()->with('status', 'Reset link has been sent to your email!');
    }

    // Step 3: Show Change Password form
    public function showChangePasswordForm($email)
    {
        return view('auth.change-password', ['email' => $email]);
    }

    // Step 4: Update password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/login')->with('status', 'Password changed successfully! You can now login.');
    }
}
