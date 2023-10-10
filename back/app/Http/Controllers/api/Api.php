<?php

namespace App\Http\Controllers\api;

use App\Models\Usermodel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class Api extends Controller
{

    public function index(Type $var = null)
    {
       echo "hello";
    }

    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'firstname' => 'required',
					'lastname' => 'required',
                    'email' => 'required|email|unique:user_managements,email',
					'mobile' => 'required|unique:user_managements,mobile',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = Usermodel::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
				'email' => $request->email,
				'mobile' => $request->mobile,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Account Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function loginuser(Request $request)
    {
        try {
            $validuser = validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if ($validuser->fails()) {
                return response()->json(['status' => false, 'message' => 'validator error', 'errors' => $validuser->errors()], 401);
            }
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = Usermodel::where('email', $request->email)->first();
            return response()->json(['status' => true, 'message' => 'User Logged Successfully', 'token' => $user->createToken("API TOKEN")->plainTextToken]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
    public function getCourierList()
    {
        return response()->json(['status' => true, 'data' => array('courire' => 1)], 200);
    }
}
