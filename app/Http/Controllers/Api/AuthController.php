<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->all();
        if ($request->has('email')) {
            $data['email'] = strtolower(trim((string) $request->input('email')));
        }

        $validator = Validator::make($data, [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::query()->where('email', $data['email'])->first();
        if (! $user || ! Hash::check((string) $data['password'], (string) $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->loadMissing(['pharmacy']);
        }

        return response()->json([
            'role' => $user ? $user->role : null,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sessão terminada.',
        ]);
    }

    public function register(Request $request)
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $accountType = (string) $request->input('account_type', 'client');
        $isPharmacy = in_array($accountType, ['pharmacy_normal', 'pharmacy_matrix'], true);
        $isClient = $accountType === 'client';

        $rules = [
            'account_type' => ['required', 'in:client,pharmacy_normal,pharmacy_matrix'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/^\S+$/',
                'confirmed',
            ],

            'name' => [$isPharmacy ? 'nullable' : 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],

            'business_name' => [$isPharmacy ? 'required' : 'nullable', 'string', 'max:255'],
            'responsible_name' => [$isPharmacy ? 'required' : 'nullable', 'string', 'max:255'],
            'nif' => [$isPharmacy ? 'required' : 'nullable', 'string', 'max:20', 'unique:pharmacies,nif'],
            'alvara' => [$isPharmacy ? 'required' : 'nullable', 'string', 'max:50', 'unique:pharmacies,alvara'],

            'alvara_document' => [
                $isPharmacy ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120',
            ],

            'province' => [$isPharmacy ? 'required' : ($isClient ? 'required' : 'nullable'), 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
            'street' => ['nullable', 'string', 'max:200'],
            'latitude' => [$isPharmacy ? 'required' : 'nullable', 'numeric'],
            'longitude' => [$isPharmacy ? 'required' : 'nullable', 'numeric'],

            'location_lat' => [$isClient ? 'required' : 'nullable', 'numeric'],
            'location_lng' => [$isClient ? 'required' : 'nullable', 'numeric'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = DB::transaction(function () use ($request, $accountType, $isPharmacy) {
            $data = $request->all();

            $userName = $isPharmacy
                ? (string) ($data['responsible_name'] ?? ($data['name'] ?? ''))
                : (string) ($data['name'] ?? '');

            $userRole = $accountType === 'pharmacy_matrix'
                ? 'pharmacy_matrix'
                : ($accountType === 'pharmacy_normal' ? 'pharmacy_normal' : 'client');

            $user = User::create([
                'name' => $userName,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => $userRole,
                'status' => 'pending',
                'province' => (! $isPharmacy) ? ($data['province'] ?? null) : null,
                'location_lat' => (! $isPharmacy && isset($data['location_lat'])) ? (float) $data['location_lat'] : null,
                'location_lng' => (! $isPharmacy && isset($data['location_lng'])) ? (float) $data['location_lng'] : null,
            ]);

            if ($isPharmacy) {
                $now = Carbon::now();
                $type = $accountType === 'pharmacy_matrix' ? 'matrix' : 'normal';
                $monthlyFee = $type === 'matrix' ? 2700 : 2000;

                $alvaraDocumentPath = null;
                if ($request->hasFile('alvara_document')) {
                    $file = $request->file('alvara_document');
                    $alvaraDocumentPath = $file->store('pharmacies/alvara_documents', 'local');
                }

                Pharmacy::create([
                    'user_id' => $user->id,
                    'business_name' => $data['business_name'],
                    'nif' => $data['nif'],
                    'alvara' => $data['alvara'],
                    'alvara_document_path' => $alvaraDocumentPath,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'],
                    'province' => $data['province'],
                    'city' => $data['city'] ?? null,
                    'neighborhood' => $data['neighborhood'] ?? null,
                    'street' => $data['street'] ?? null,
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'type' => $type,
                    'is_active' => false,
                    'monthly_fee' => $monthlyFee,
                    'trial_starts_at' => $now,
                    'trial_ends_at' => $now->copy()->addDays(30),
                ]);
            }

            return $user;
        });

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'user' => $user,
        ], 201);
    }
}
