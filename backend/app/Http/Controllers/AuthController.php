<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Mail\MailSender;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

        // Náhodné dočasné heslo (nebude sa posielať používateľovi)
        $password = Str::random(12);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($password),
        ]);

        // Token pre nastavenie hesla
        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now()
        ]);

        // Pošli e-mail s linkom
        Mail::to($user->email)->send(new MailSender($user, $token));

        return response()->json([
            'message' => 'Registration successful. Please check your email to set your password.'
        ], 201);
    }

    // -----------------------------
    // SET PASSWORD (from email link)
    // -----------------------------
    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $reset = DB::table('password_resets')->where('email', $request->email)->first();

       if (!$reset || $request->token !== $reset->token) {
         return response()->json(['message' => 'Invalid or expired token'], 400);
        }


        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password set successfully']);
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
