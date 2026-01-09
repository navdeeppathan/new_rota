<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use Illuminate\Support\Facades\Log;

class PrivacyPolicyController extends Controller
{
    public function manage(Request $request)
    {
        try {
            // Handle POST request for create or update
            if ($request->isMethod('post')) {
                $request->validate([
                    'content' => 'required|string',
                ]);

                $policy = PrivacyPolicy::first();

                if ($policy) {
                    $policy->update(['content' => $request->content]);
                } else {
                    $policy = PrivacyPolicy::create(['content' => $request->content]);
                }

                return redirect()->back()->with('success', 'Privacy Policy saved successfully.');
            }

            // GET request: show the editor with existing content
            $policy = PrivacyPolicy::first();
            return view('admin.privacy_policy_manage', compact('policy'));

        } catch (\Exception $e) {
            Log::error('Privacy Policy Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while processing your request.');
        }
    }
}
