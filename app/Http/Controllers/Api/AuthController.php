<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ===========================
    // Registracija korisnika
    // ===========================
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'sometimes|in:user,admin',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'user',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registracija uspješna ✅',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ], 201);
    }

    // ===========================
    // Login
    // ===========================
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Neispravni podaci ❌'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login uspješan ✅',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // ===========================
    // Logout
    // ===========================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Uspješno logout ✅']);
    }

    // ===========================
    // Trenutno prijavljeni korisnik
    // ===========================
    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    // ===========================
    // Superadmin: Kreiranje admina
    // ===========================
    public function createAdmin(Request $request)
    {
        // Samo superadmin može kreirati admina
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Nemate ovlaštenje ❌'], 403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        return response()->json([
            'message' => 'Admin kreiran ✅',
            'user'    => $this->formatUser($admin),
        ], 201);
    }

    // ===========================
    // Superadmin: Lista svih korisnika
    // ===========================
    public function listUsers(Request $request)
    {
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Nemate ovlaštenje ❌'], 403);
        }

        $users = User::all()->map(fn($user) => $this->formatUser($user));

        return response()->json($users);
    }

    // ===========================
    // Superadmin: Ažuriranje korisnika
    // ===========================
    public function updateUser(Request $request, $id)
    {
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Nemate ovlaštenje ❌'], 403);
        }

        $user = User::findOrFail($id);

        // Zaštita: ne može se mjenjati drugi superadmin
        if ($user->role === 'superadmin' && $user->id !== $request->user()->id) {
            return response()->json(['message' => 'Ne možete mijenjati drugog superadmina ❌'], 403);
        }

        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'role'     => 'sometimes|in:user,admin,superadmin',
            'password' => 'sometimes|string|min:6',
        ]);

        // Zaštita: ne smije biti više od 2 superadmina
        if ($request->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount >= 2 && $user->role !== 'superadmin') {
                return response()->json(['message' => 'Maksimalan broj superadmina je 2 ❌'], 400);
            }
        }

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('role')) $user->role = $request->role;
        if ($request->has('password')) $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Korisnik ažuriran ✅',
            'user'    => $this->formatUser($user),
        ]);
    }

    // ===========================
    // Superadmin: Brisanje korisnika
    // ===========================
    public function deleteUser(Request $request, $id)
    {
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Nemate ovlaštenje ❌'], 403);
        }

        $user = User::findOrFail($id);

        // Ne može obrisati samog sebe ili drugog superadmina
        if ($user->role === 'superadmin') {
            return response()->json(['message' => 'Ne možete obrisati superadmina ❌'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Korisnik obrisan ✅']);
    }

    // ===========================
    // Helper: format usera za response
    // ===========================
    private function formatUser(User $user): array
    {
        return [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'role'    => $user->role,
            'room_id' => $user->room_id,
        ];
    }
}