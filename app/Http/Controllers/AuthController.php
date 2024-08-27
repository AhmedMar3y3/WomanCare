<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\storeUserRequest;
use App\Http\Requests\loginUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
     use HttpResponses;
    
     public function register(StoreUserRequest $request)
     {
         $validatedData = $request->validated();
         $validatedData['password'] = Hash::make($validatedData['password']);
 
         $user = User::create($validatedData);
 
         // Send welcome email to user's Gmail
         Mail::raw("Welcome to our platform!", function ($message) use ($user) {
             $message->to($user->email)
                 ->subject('Welcome to Woman Care, You have registered successfully');
         });
 
         return $this->Success([
             'user' => $user,
             'token' => $user->createToken('Api token of '. $user->name)->plainTextToken,
             'message' => 'we have sent you an email'
         ]);
     }

    public function login(loginUserRequest $request)
{
    $validatedData = $request->validated();
    $user = User::where('email', $request->input('email'))->first();

    if (!$user || !Hash::check($request->input('password'), $user->password)) {
        return $this->error('', "Credentials do not match", 404);
    }

    $token = $user->createToken('Api token of ' . $user->name)->plainTextToken;

    return $this->Success([
        'user' => $user,
        'token' => $token
    ]);
}
   
    public function logout(){
        Auth::user()->currentAccessToken()->delete();
        return $this->success([
            'message'=> Auth::user()->name .' ,you have successfully logged out and your token has been deleted'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return $this->error(['email' => 'User not found'], 404, "User not found");
        }
    
        // Generate a random 6-digit code
        $code = mt_rand(100000, 999999);
    
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => now()
            ]
        );
    
        // Send the code via gmail
        Mail::raw("Your password reset code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset Code');
        });
    
        return $this->success(['message' => 'Password reset code sent to your email']);
    }
    
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|numeric',
        'password' => 'required|min:8|confirmed',
    ]);

    // Retrieve the password reset entry
    $resetEntry = DB::table('password_resets')
        ->where('email', $request->email)
        ->where('token', $request->code)
        ->first();

    if (!$resetEntry) {
        return $this->error(['code' => 'Invalid reset code'], 400, "Invalid reset code");
    }

    // Reset the user's password
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // Delete the password reset entry
    DB::table('password_resets')->where('email', $request->email)->delete();

    return $this->success(['message' => 'Password has been reset successfully']);
}   
}