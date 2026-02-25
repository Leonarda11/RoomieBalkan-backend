<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $auth = auth()->user();

        if (in_array($auth->role, ['admin', 'super_admin'])) {
            return User::all();
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    public function store(Request $request)
    {
        $auth = auth()->user();

        if (!in_array($auth->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $role = $auth->role === 'super_admin' ? $request->input('role', 'user') : 'user';

        $user = User::create([
            ...$data,
            'role' => $role,
            'password' => $data['password']
        ]);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        $auth = auth()->user();

        if ($auth->role === 'super_admin' || $auth->role === 'admin' || $auth->id === $user->id) {
            return $user;
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    public function update(Request $request, User $user)
    {
        $auth = auth()->user();

        if ($auth->role !== 'super_admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'role' => 'sometimes|required|in:user,admin,super_admin',
            'password' => 'sometimes|min:6'
        ]);

        if (isset($data['password'])) {
            $data['password'] = $data['password'];
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $auth = auth()->user();

        if ($auth->role !== 'super_admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}