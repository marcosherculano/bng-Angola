<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(Request $request)
    {
        return view('perfil.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:100'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
        ]);

        $emailChanged = array_key_exists('email', $data) && (string) $data['email'] !== (string) $user->email;
        $passwordProvided = ! empty($data['password']);

        if ($emailChanged || $passwordProvided) {
            if (empty($data['current_password'])) {
                return back()->withErrors([
                    'current_password' => 'Informe a sua senha actual para confirmar esta alteração.',
                ])->withInput();
            }

            if (! Hash::check((string) $data['current_password'], (string) $user->password)) {
                return back()->withErrors([
                    'current_password' => 'A senha actual está incorrecta.',
                ])->withInput();
            }
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->province = $data['province'] ?? null;

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('perfil.edit')->with('success', 'Perfil actualizado com sucesso.');
    }
}
