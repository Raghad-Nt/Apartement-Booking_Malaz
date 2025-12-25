<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;





class RegisterController extends BaseController
{
    
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Handle ID image upload
            $idImagePath = null;
            if ($request->hasFile('id_image')) {
                $idImagePath = $request->file('id_image')->store('id_images', 'public');
            }

           
            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
            
            // Create user with pending status
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'role' => $request->role,
                'status' => 'pending', // Default status is pending until admin approval
                'id_image' => $idImagePath,
                'profile_image' => $profileImagePath
            ]);
            
            // Create token for the user
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['first_name'] =  $user->first_name;
            $success['last_name'] =  $user->last_name;
            $success['full_name'] =  $user->first_name . ' ' . $user->last_name;
            $success['role'] =  $user->role;
            $success['status'] =  $user->status;
            
            return $this->sendResponse($success, 'register success');
        } catch (Exception $e) {
            return $this->sendError('messages.registration_failed', ['error' => $e->getMessage()]);
        }
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'password' => 'required|string'
        ]);

        // Find user by mobile
        $user = User::where('mobile', $request->mobile)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('messages.invalid_credentials');
        }

        // Check if user account is active
        if ($user->status !== 'active') {
            return $this->sendError('messages.account_not_active');
        }

        // Create token for the user
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['first_name'] =  $user->first_name;
        $success['last_name'] =  $user->last_name;
        $success['full_name'] =  $user->first_name . ' ' . $user->last_name;
        $success['role'] =  $user->role;

        return $this->sendResponse($success, 'login success');
    }

    
      //Get user profile
     
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Add full URLs to images if they exist
        $userData = $user->toArray();
        if ($user->id_image) {
            $userData['id_image_url'] = asset('storage/' . $user->id_image);
        }
        if ($user->profile_image) {
            $userData['profile_image_url'] = asset('storage/' . $user->profile_image);
        }

        return $this->sendResponse($userData, 'user profile retrieved');
    }

    
      //Update user profile
     
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date|before:today',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'mobile' => 'sometimes|string|unique:users,mobile,' . $user->id,
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old profile image if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $user->profile_image = $profileImagePath;
            }

            // Update other fields if provided
            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }
            
            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }
            
            if ($request->has('date_of_birth')) {
                $user->date_of_birth = $request->date_of_birth;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('mobile')) {
                $user->mobile = $request->mobile;
            }

            $user->save();

            // Add full URLs to images if they exist
            $userData = $user->toArray();
            if ($user->id_image) {
                $userData['id_image_url'] = asset('storage/' . $user->id_image);
            }
            if ($user->profile_image) {
                $userData['profile_image_url'] = asset('storage/' . $user->profile_image);
            }

            return $this->sendResponse($userData, 'profile updated');
        } catch (Exception $e) {
            return $this->sendError('messages.profile_update_failed', ['error' => $e->getMessage()]);
        }
    }
    
    
     //Logout api
     
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();

            return $this->sendResponse([], 'logout success');
        } catch (Exception $e) {
            return $this->sendError('messages.logout_failed', ['error' => $e->getMessage()]);
        }
    }
}