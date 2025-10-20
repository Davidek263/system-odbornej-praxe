<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ==========================
    // LOGIN
    // ==========================
    public function loginPerson(Request $request)
    {
        $person = Person::where('email', $request->email)->first();

        if (!$person || !Hash::check($request->password, $person->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $person->createToken('vue-spa-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function loginCompany(Request $request)
    {
        $company = Company::where('email', $request->email)->first();

        if (!$company || !Hash::check($request->password, $company->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $company->createToken('vue-spa-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    // ==========================
    // REGISTER
    // ==========================
    public function registerPerson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:people,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $person = Person::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $person->createToken('vue-spa-token')->plainTextToken;

        return response()->json([
            'person' => $person,
            'token' => $token
        ], 201);
    }

    public function registerCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'companyName' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $company = Company::create([
            'companyName' => $request->companyName,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $company->createToken('vue-spa-token')->plainTextToken;

        return response()->json([
            'company' => $company,
            'token' => $token
        ], 201);
    }

    // ==========================
    // LOGOUT
    // ==========================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
