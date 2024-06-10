<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register (Request $request){
        $input = $request -> validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]) ;
        
            // Check if the email is already registered
    if (User::where('email', $input['email'])->exists()) {
        return response()->json([
            'message' => 'Email is already registered.'
        ], 400);
    }

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => bcrypt($input['password'])

        ]);

        $token = $user -> createToken('AuthToken') -> plainTextToken;

        return response()->json([
            'user' => $user, 
            'token' => $token,
            'message' => 'Account created successfully'], 201);

    }

    public function login (Request $request){
        $input = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $input['email'])->first();

         // Check if the user exists
    if (!$user) {
        return response()->json([
            'message' => 'User does not exist. Kindly register.'
        ], 404); 
    }

        //checking password
        if(!Hash::check($input['password'], $user->password)){
            return response([
                'message' => 'wrong credentials'
            ], 401);
        }

        $token = $user -> createToken('AuthToken') -> plainTextToken;

        return response()->json([
            'user' => $user, 
            'token' => $token,
            'message' => 'Login successful'], 200);


    }
    
    public function logout (Request $request){
        $request->user()->tokens()->delete();
        return response([
            'message' => "Thank you for using our services! You've been successfully logged out. Have a great day!"
        ], 200);

    }


    public function indexU(){
       return User::all();

    }
  
}
