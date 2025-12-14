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
                'name' => $request->name,
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
            $success['name'] =  $user->name;
            $success['role'] =  $user->role;
            $success['status'] =  $user->status;

            return $this->sendResponse($success, 'messages.register_success');
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
            return $this->sendError('messages.invalid_credentials', ['error' => 'messages.invalid_credentials']);
        }

        // Check if user account is active
        if ($user->status !== 'active') {
            return $this->sendError('messages.account_not_active', ['error' => 'messages.account_not_active']);
        }

        // Create token for the user
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
        $success['role'] =  $user->role;

        return $this->sendResponse($success, 'messages.login_success');
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

        return $this->sendResponse($userData, 'messages.user_profile_retrieved');
    }

    
      //Update user profile
     
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'mobile' => 'sometimes|string|unique:users,mobile,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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
            if ($request->has('name')) {
                $user->name = $request->name;
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

            return $this->sendResponse($userData, 'messages.profile_updated');
        } catch (Exception $e) {
            return $this->sendError('messages.profile_update_failed', ['error' => $e->getMessage()]);
        }
    }
}