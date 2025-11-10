<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditService;

class UserController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        $users = $query->get();
        
        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|unique:users,username,' . $user->id,
        ]);

        $oldValues = $user->toArray();
        $user->update($request->only(['name', 'email', 'username']));
        
        $this->auditService->log('UPDATE', 'users', $user->id, $oldValues, $user->toArray());
        
        return redirect()->route('users.show', $user)->with('success', 'Profile updated successfully');
    }

    public function resetPassword(User $user)
    {
        $newPassword = 'password123';
        $oldValues = ['password' => $user->password];
        
        $user->update(['password' => Hash::make($newPassword)]);
        
        $this->auditService->log('PASSWORD_RESET', 'users', $user->id, $oldValues, ['password' => 'reset']);
        
        return redirect()->route('users.show', $user)->with('success', "Password reset to: {$newPassword}");
    }

    public function deactivate(User $user)
    {
        $oldValues = $user->toArray();
        $user->update(['is_active' => false]);
        
        $this->auditService->log('DEACTIVATE', 'users', $user->id, $oldValues, $user->toArray());
        
        return redirect()->route('users.index')->with('success', 'User deactivated successfully');
    }
}