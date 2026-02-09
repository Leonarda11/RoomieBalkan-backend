<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
  
    // Registracija korisnika

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:user,admin',
        ]);

        // Provjera postoji li ime s drugom rolom
        $existing = User::where('name', $request->name)->first();
        if ($existing && $existing->role !== $request->role) {
            return response()->json([
                'message' => 'Ime već postoji s drugom rolom!'
            ], 400);
        }


        if ($existing) {
            return response()->json([
                'message' => 'Registracija uspješna ✅',
                'user'    => $existing,
            ], 200);
        }

        // Kreiranje novog korisnika
        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->name.'@dummy.com', 
            'password' => bcrypt('dummy'),         
        ]);

        return response()->json([
            'message' => 'Registracija uspješna ✅',
            'user'    => $user,
        ], 201);
    }

    // Login

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::where('name', $request->name)
                    ->where('role', $request->role)
                    ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Ne postoji korisnik s tim imenom i rolom!'
            ], 401);
        }

  
        return response()->json([
            'message' => 'Login uspješan ✅',
            'user'    => $user,
        ]);
    }


    // Logout

    public function logout(Request $request)
    {
        // Ako koristiš token-based auth, možeš dodati kod za brisanje tokena
        return response()->json(['message' => 'Uspješno logout ✅']);
    }
}