<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class PageController extends Controller
{
   public function login()
    {
        if (session()->has('user')) {
            return redirect()->route('dashboard');
        }
    
        return view('admin.auth.login');
    }

    public function loginCheck(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);
    
            // Attempt to find the user
            $user = User::where('email', $request->email)->first();
    
            if (!$user || !Hash::check($request->password, $user->password)) {
                return redirect()->back()->with('error', 'Invalid credentials');
            }
    
            // Allow only Admin (role_id == 1)
            if ($user->role_id != 1 && $user->role_id != 0) {
                return redirect()->back()->with('error', 'Access denied. Only Admins can log in.');
            }
    
            // Store session data
            session([
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  =>$user->role_id
                ]
            ]);
    
     
            $admin = User::where('role_id', 1)->first();
            
            // if ($admin) {
            //     Notification::create([
            //         'alert_type_id'    => 2, // e.g. user_related
            //         'message'          => "User has logged in.",
            //         'show_to_admin'    => 1,
            //         'admin_id'         => $admin->id,
            //         'user_id'          => $user->id,
            //     ]);
            // }
    
    
            // Return success response
            return redirect()->route('dashboard')->with('success', 'Login successful!');

    
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'data'    => null,
            ], 500);
        }
    }



    public function logout()
    {
        session()->forget('user');
        return redirect()->route('login');
    }
    
    public function dashboard()
    {
        $user = session('user'); 
        $role_id = $user['role'];

        if($role_id == 0){
            return view('admin.dashboardview');
        }else{
            return view('admin.dashboard');
        }
       
    }
    public function users(Request $request)
    {
        $query = User::query();
    
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%");
            });
        }
    
        $query->orderByDesc('id');
        $users = $query->get();
    
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        try {
            $request->validate([
                'name'          => 'nullable|string|max:255',
                'email'         => 'required|email|unique:users,email',
                'password'      => 'required|string|min:6',
                'role_id'       => 'nullable|integer',
                'date_of_birth' => 'nullable|string|max:20',
                'phone_number'  => 'nullable|string|max:20',
                'job_title'     => 'nullable|string|max:100',
                'address'       => 'nullable|string|max:255',
                'gender'        => 'nullable|string|max:20',
                'profile_pic'   => 'nullable|max:2048',
                'team_leader_id'=> 'nullable|integer',
                'category'      => 'required',
                // 'start_time'    => 'required_if:category,1|nullable|date_format:H:i',
                // 'end_time'      => 'required_if:category,1|nullable|date_format:H:i',
                
            ]);
    
            $user = new User([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'role_id'       => $request->role_id,
                'date_of_birth' => $request->date_of_birth,
                'phone_number'  => $request->phone_number,
                'job_title'     => $request->job_title,
                'address'       => $request->address,
                'gender'        => $request->gender,
                'team_leader_id'=> $request->team_leader_id,
                'category'    => $request->category,
                'rate'        => $request->rate,
                'start_time'  => $request->start_time ?? null,
                'end_time'    => $request->end_time??null,
                'overtime_rate'=>$request->overtime_rate ?? 0
            ]);

            
            if ($request->category == 1) { // Kitchen & Housekeeping
                $user->start_time = $request->start_time;
                $user->end_time   = $request->end_time;
            }
    
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('profile_pics');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $user->profile_pic = 'profile_pics/' . $filename;
            }
    
            $user->save();
            
              // Send notifications
            // $this->sendNotifications($user);
    
            return redirect()->route('users')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    
        // private function sendNotifications($user)
        // {
        //     $admin = User::find($user->admin_id);
        //     $user = User::find($user->user_id);
        
        //     $adminName = $admin ? $admin->name : 'admin';
        //     $userName = $user ? $user->name  : 'user';
        
        //   Notification::create([
        //         'alert_type_id'    => 2,
        //         'message'          => "New User is created successfully...",
        //         'show_to_admin'  => 1,
        //         'user_id'       => $user->id,
        //         'admin_id'        => $admin->id,
        //     ]);
        // }
    
     public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }
    
    
    public function updateUser(Request $request, $id)
    {
            try {
                
                $request->validate([
                    'name'          => 'nullable|string|max:255',
                    'email'         => 'required|email|unique:users,email,' . $id,
                    'role_id'       => 'nullable|integer',
                    'date_of_birth' => 'nullable|string|max:20',
                    'phone_number'  => 'nullable|string|max:20',
                    'job_title'     => 'nullable|string|max:100',
                    'address'       => 'nullable|string|max:255',
                    'gender'        => 'nullable|string|max:20',
                    'profile_pic'   => 'nullable|max:2048',
                    'category'      =>  'required'
                ]);
        
                $user = User::findOrFail($id);
                $user->update([
                    'name'          => $request->name,
                    'email'         => $request->email,
                    'role_id'       => $request->role_id,
                    'password'      => Hash::make($request->password) ?? $user->password,
                    'date_of_birth' => $request->date_of_birth,
                    'phone_number'  => $request->phone_number,
                    'job_title'     => $request->job_title,
                    'address'       => $request->address,
                    'gender'        => $request->gender,
                    'category'      => $request->category,
                    'rate'          => $request->rate ?? 0,
                    'start_time'    => $request->start_time ?? null,
                    'end_time'      => $request->end_time ?? null,
                    'overtime_rate' => $request->overtime_rate ?? 0
                ]);
        
                if ($request->hasFile('profile_pic')) {
                    $file = $request->file('profile_pic');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $destinationPath = public_path('profile_pics');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $file->move($destinationPath, $filename);
                    $user->profile_pic = 'profile_pics/' . $filename;
                    $user->save();
                }
        
                $admin = User::where('role_id', 1)->first();
                
                if ($admin) {
                    Notification::create([
                        'alert_type_id'    => 2,
                        'message'          =>"User has updated their profile.",
                        'show_to_admin'    => 1,
                        'admin_id'         => $admin->id,
                        'user_id'          => $user->id,
                    ]);
                }
                return redirect()->route('users')->with('success', 'User updated successfully');
            } catch (\Exception $e) {
                \Log::error('User update error: ' . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Update failed.']);
            }
    }

     public function deleteUser($id)
    {
            try {
                $user = User::findOrFail($id);
                $user->delete();
                return redirect()->route('users')->with('success', 'User deleted successfully');
            } catch (\Exception $e) {
                Log::error('User delete error: ' . $e->getMessage());
                return back()->withErrors(['error' => 'Failed to delete user']);
            }
    }
}

