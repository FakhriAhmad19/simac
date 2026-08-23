<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $users = User::with('technician')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('users.create', ['roles' => UserRole::options()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in([UserRole::Owner->value, UserRole::Technician->value])],
            'password' => ['required', 'confirmed', Password::defaults()],
            'specialization' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            if ($user->role === UserRole::Technician) {
                Technician::create([
                    'user_id' => $user->id,
                    'specialization' => $data['specialization'] ?? null,
                    'status' => 'available',
                ]);
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $user->load('technician');

        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'specialization' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $user) {
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if ($user->role === UserRole::Technician) {
                $user->technician()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['specialization' => $data['specialization'] ?? null],
                );
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->role === UserRole::Admin) {
            return back()->with('error', 'Akun admin tidak dapat dihapus dari sini.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
