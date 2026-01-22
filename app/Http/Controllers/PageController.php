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
        // if (session()->has('user')) {
        //     return redirect()->route('dashboard');
        // }

        //  if (session()->has('login_success')) {
        //         return view('admin.auth.login');
        //     }
    
        return view('admin.auth.login');
    }

    public function loginCheck(Request $request)
{
    // Validate OUTSIDE try-catch
    $validated = $request->validate([
        'email'    => 'required',
        'password' => 'required',
    ]);


    try {

        // Debug input
        // dd($validated);

        $user = User::where('email', $validated['email'])->first();

        // Debug user
        // dd($user);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()->with('error', 'Invalid email or password');
        }

        if (!in_array($user->role_id, [0, 1])) {
            return back()->with('error', 'Access denied');
        }

        session([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role_id,
            ]
        ]);

        return redirect()->route('login')->with('login_success', true);

    } catch (\Exception $e) {
        dd($e->getMessage());
        Log::error('Login error: '.$e->getMessage());

        return back()->with('login_error', 'Something went wrong');
    }
}


     public function superadminlogin()
    {
        return view('superadmin.auth.login');
    }


    public function superadminloginCheck(Request $request)
    {
        try {
           
            // Validate the request
            $request->validate([
                'email'    => 'required',
                'password' => 'required',
            ]);

           
            // Attempt to find the user
            $user = User::where('email', $request->email)->first();
 
            
            // dump($user);

           if (!$user || $request->password !== $user->password) {
                return redirect()->back()->with('error', 'Invalid credentials');
            }
    
            // Allow only Admin (role_id == 1)
            if ($user->role_id != 11) {
                return redirect()->back()->with('error', 'Access denied. Only SuperAdmin can log in.');
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
            // return redirect()->route('dashboard')->with('success', 'Login successful!');
            return redirect()->route('superadmin.dashboard')->with('login_success', true);
    
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            // dd($e->getMessage());
            return redirect()->back()->with('login_error', 'Something went wrong. Please try again.');
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

    
    public function index()
    {
       $user = session('user');
       $users = User::where('parent_id', $user['id'])->latest()->get();
       return view('superadmin.user.index', compact('users'));
    }

    public function storeSuperAdminCreate()
    {
        return view('superadmin.user.create');
    }



    public function storeSuperAdminUser(Request $request)
    {
        try {
            $user = session('user');

            // Validate only required fields
            $request->validate([
                'name'     => 'required|string',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string',
                'registeration_no'=>'required',
                
                'website_url'=>'required',
                'phone_number'=>'required',
                'address'=>'required',
            ]);

            // Create user
            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' =>  Hash::make($request->password),
                'role_id'  => 1,
                'parent_id' => $user['id'],
                'registeration_no'=>$request->registeration_no,
                     
                'website_url'=>$request->website_url,
                'phone_number'=>$request->phone_number,
                'address'=>$request->address,

                 // Plain password (no hash)
                // OR use Hash::make() if you decide later
            ]);

            return redirect()->back()->with('success', 'User created successfully');

        } catch (\Exception $e) {
            Log::error('User creation error: ' . $e->getMessage());
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

