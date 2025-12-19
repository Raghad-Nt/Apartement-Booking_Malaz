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
use Illuminate\Http\Response;





class RegisterController extends BaseController
{
    
    
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            
            $idImagePath = null;
            if ($request->hasFile('id_image')) {
                $idImagePath = $request->file('id_image')->store('id_images', 'public');
            }

           
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

           
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'role' => $request->role,
                'status' => 'pending', 
                'id_image' => $idImagePath,
                'profile_image' => $profileImagePath
            ]);

           
            
            $success['name'] =  $user->name;
            $success['role'] =  $user->role;
            $success['status'] =  $user->status;
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;

            return $this->sendResponse($success, 'register success');
        } catch (Exception $e) {
            return $this->sendError('messages.registration_failed', ['error' => $e->getMessage()]);
        }
    }
   
    
     
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'password' => 'required|string'
        ]);

       
        $user = User::where('mobile', $request->mobile)->first();

        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('messages.invalid_credentials');
        }

        
        if ($user->status !== 'active') {
            return $this->sendError('messages.account_not_active');
        }

       
       
        $success['name'] =  $user->name;
        $success['role'] =  $user->role;
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;

        return $this->sendResponse($success, 'login success');
    }

    
      
     
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        
        $userData = $user->toArray();
        if ($user->id_image) {
            $userData['id_image_url'] = asset('storage/' . $user->id_image);
        }
        if ($user->profile_image) {
            $userData['profile_image_url'] = asset('storage/' . $user->profile_image);
        }

        return $this->sendResponse($userData, 'user profile retrieved');
    }

    
      
     
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
           
            if ($request->hasFile('profile_image')) {
                
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $user->profile_image = $profileImagePath;
            }

            
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
    
    
     
    public function logout(Request $request): JsonResponse
    {
        try {
           
            $request->user()->currentAccessToken()->delete();

            return $this->sendResponse([], 'logout success');
        } catch (Exception $e) {
            return $this->sendError('messages.logout_failed', ['error' => $e->getMessage()]);
        }
    }
}