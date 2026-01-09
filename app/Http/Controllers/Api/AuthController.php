<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StatusPlaystore;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'          => 'nullable|string|max:255',
                'email'         => 'required|email|unique:users',
                'password'      => 'required|string|min:6',
                'role_id'       => 'nullable|integer',
                'date_of_birth' => 'nullable|string|max:20',
                'phone_number'  => 'nullable|string|max:20',
                'job_title'     => 'nullable|string|max:100',
                'address'       => 'nullable|string|max:255',
                'gender'        => 'nullable|string|max:20',
                
            ]);
    
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'role_id'       => $request->role_id,
                'date_of_birth' => $request->date_of_birth,
                'phone_number'  => $request->phone_number,
                'job_title'     => $request->job_title,
                'address'       => $request->address,
                'gender'        => $request->gender,
                'category'      => $request->category??''
            ]);
            
            Auth::login($user);
    
            return response()->json([
                'success' => true,
                'message' => 'Registered and logged in successfully',
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
            ], 500);
        }
    }
    
    
    
    public function storeUsers(Request $request)
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
                'category'      => 'required'
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
                 'category '    => $request->category
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
            }
    
            $user->save();
            
              // Send notifications
            // $this->sendNotifications($user);
    
             return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user'    => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('User creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'User creation failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function toggle(Request $request)
    {
       $request->validate([
            'status' => 'required|in:0,1'
        ]);

        // Assume only one row needed (like a global status)
        $status = StatusPlaystore::first();

        if ($status) {
            $status->update(['status' => $request->status]);
        } else {
            $status = StatusPlaystore::create(['status' => $request->status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'data' => $status
        ]);
    }
    
    public function getStatus()
    {
        $status = StatusPlaystore::first();

        return response()->json([
            'success' => true,
            'status' => $status ? $status->status : 0
        ]);
    }


    public function login(Request $request)
    {
        try {
            \Log::info($request->all());
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);
             $user = User::where('email', $credentials['email'])->first();
                
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }
            if ($user->status == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive. Please contact support.',
                ], 403);
            }

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                \Log::info($user);
            
                // Convert to string
                $user['role_id'] = (string) $user->role_id;
                $user['status']  = (string) $user->status;
            
                return response()->json([
                    'success' => true,
                    'message' => 'Logged in successfully',
                    'user'    => $user,
                ]);
            }
            

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        } catch (\Exception $e) {
            
            Log::error('Login error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = Auth::user();
    
            if ($user) {
                $user->role_id = (string) $user->role_id;
                $user->status  = (string) $user->status;
            }
    
            return response()->json([
                'success' => true,
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Me fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not fetch user details',
            ], 500);
        }
    }


    
    
    public function storeOrUpdateProfilePic(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'profile_pic' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);
    
            $user = User::find($request->input('user_id'));
    
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_' . $file->getClientOriginalName();
    
                // Store in public/profile_pics folder
                $destinationPath = public_path('profile_pics');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
    
                $file->move($destinationPath, $filename);
    
                // Save relative path in DB
                $user->profile_pic = 'profile_pics/' . $filename;
                $user->save();
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Profile pic upload error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updateUser(Request $request)
    {
        try {
            $request->validate([
                'id'            => 'required',
                'name'          => 'nullable|string|max:255',
                'email'         => 'nullable|email|unique:users,email,' . $request->id,
                'role_id'       => 'nullable|integer',
                'date_of_birth' => 'nullable|string|max:20',
                'phone_number'  => 'nullable|string|max:20',
                'job_title'     => 'nullable|string|max:100',
                'address'       => 'nullable|string|max:255',
                'gender'        => 'nullable|string|max:20',
                'category'      => 'required'
            ]);
    
            $user = User::findOrFail($request->id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }
    
            $user->update([
                'name'          => $request->name ?? $user->name,
                'email'         => $request->email ?? $user->email,
                
                'role_id'       => $request->role_id ?? $user->role_id,
                'date_of_birth' => $request->date_of_birth ?? $user->date_of_birth,
                'phone_number'  => $request->phone_number ?? $user->phone_number,
                'job_title'     => $request->job_title ?? $user->job_title,
                'address'       => $request->address ?? $user->address,
                'gender'        => $request->gender ?? $user->gender,
                'category'      =>  $request->category ??$user->category,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
            ], 500);
        }
    }
    
    public function getUser(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:users,id',
            ]);
    
            $user = User::findOrFail($request->id);
    
            // convert fields to string
            $user->role_id = (string) $user->role_id;
            $user->status  = (string) $user->status;
    
            return response()->json([
                'success' => true,
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('User fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    }

    
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email|exists:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);
            
    
            $user = User::where('email', $request->email)->firstOrFail();
            if(!$user){
                return response()->json([
                'success' => false,
                'message' => 'User not found',
            ]);
            }
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Password update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Password update failed',
            ], 500);
        }
    }


   public function reset(Request $request)
   {
    try {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Reset password error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
        ], 500);
    }
}

    public function getUserShifts(Request $request)
    {
    try {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $roleId = $user->role_id;
        $users = collect();

        if ($roleId == 3) {
            // T/Leaders: get all users except admins
            $users = User::where('role_id', '!=', 1)->get();
        } elseif ($roleId == 4) {
            // Seniors: all users except T/Leaders and admins
            $users = User::whereNotIn('role_id', [1, 3])->get();
        } elseif ($roleId == 5) {
            // Carers: only self (if not admin)
            $users = $user->role_id != 1 ? User::where('id', $userId)->get() : collect();
        } elseif ($roleId == 6) {
            // Bank: only bank users (not admin)
            $users = User::where('role_id', 6)->get();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized role.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    } catch (\Exception $e) {
        Log::error('Error in getUserShifts: ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching users.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function createDummyForAdmin(Request $request)
    {
        try {
            $adminId = $request->input('admin_id', 1); // default to admin ID 1
    
            $notification = Notification::create([
                'alert_type_id'     => 1, // assume alert_type with ID 1 exists
                'message'           => 'This is a test notification for admin.',
                'show_to_admin'     => 1,
                'admin_id'          => $adminId,
                'user_id'           => null,
                'superadmin_id'     => null,
            ]);
    
            return response()->json([
                'status' => true,
                'message' => 'Dummy notification created for admin.',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating dummy notification: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'Failed to create dummy notification.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function updateStatus(Request $request)
    {
        try {
            $id = $request->input("user_id");
            $status = $request->input("status");
    
            if (!$id || !is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing user ID.'
                ], 400);
            }
    
            $user = User::find($id);
    
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }
    
            $user->status = $status;
            $user->save();
    
            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating user status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function getUsersByRole($user_id)
    // {
    //     try {
            
    //         $user_id = (int) $user_id;
    
    //         if ($user_id === 1) {
    
    //             $users = User::all();
    //         } elseif ($user_id === 2) {
            
    //             $users = User::whereIn('role_id', [1, 3])->get();
    //         } elseif ($user_id === 3) {
                
    //             $users = User::all();
    //         }elseif ($user_id === 6) {
                
    //             $users = User::all();
    //         } else {
    //               $users = User::all();
    //             // return response()->json([
    //             //     'success' => false,
    //             //     'message' => 'Invalid user_id or role.',
    //             // ], 400);
    //         }
    
    //         return response()->json([
    //             'success' => true,
    //             'users' => $users,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('getUsersByRole error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch users.',
    //         ], 500);
    //     }
    // }
    public function getUsersByRole($user_id)
    {
        try {
            $user_id = (int) $user_id;
    
            if ($user_id === 1) {
                $users = User::all();
            } elseif ($user_id === 2) {
                $users = User::whereIn('role_id', [1, 3])->get();
            } elseif ($user_id === 3) {
                $users = User::all();
            } elseif ($user_id === 6) {
                $users = User::all();
            } else {
                $users = User::all();
            }
    
            // Convert role_id and status to string
            $users->transform(function ($user) {
                $user->role_id = (string) $user->role_id;
                $user->status  = (string) $user->status;
                return $user;
            });
    
            return response()->json([
                'success' => true,
                'users' => $users,
            ]);
    
        } catch (\Exception $e) {
            Log::error('getUsersByRole error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users.',
            ], 500);
        }
    }



}
