<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // -----------------------------
    // REGISTER PERSON (USER)
    // -----------------------------
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $password = Str::random(12);
        Mail::to($request->email)->send(new MailSender($password));

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($password),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // -----------------------------
    // REGISTER COMPANY
    // -----------------------------
    public function registerCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'companyName' => 'required|string|max:255',
            'email'       => 'required|email|unique:companies,email',
            'address'     => 'required|string|max:255',
            'phone'       => 'required|string|max:255',
            'password'    => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $company = Company::create([
            'companyName' => $request->companyName,
            'email'       => $request->email,
            'address'     => $request->address,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
        ]);

        $token = $company->createToken('api-token')->plainTextToken;

        return response()->json([
            'company' => $company,
            'token' => $token
        ], 201);
    }

    // -----------------------------
    // LOGIN USER
    // -----------------------------
    public function loginUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    // -----------------------------
    // LOGIN COMPANY
    // -----------------------------
    public function loginCompany(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $company = Company::where('email', $request->email)->first();

        if (!$company || !Hash::check($request->password, $company->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $company->createToken('api-token')->plainTextToken;

        return response()->json([
            'company' => $company,
            'token' => $token
        ]);
    }

    // -----------------------------
    // LOGOUT
    // -----------------------------
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
