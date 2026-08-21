<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
        register as traitRegister;
    }

    public function register(Request $request)
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        return $this->traitRegister($request);
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/aguardar-aprovacao';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $accountType = (string) ($data['account_type'] ?? 'client');
        $isPharmacy = in_array($accountType, ['pharmacy_normal', 'pharmacy_matrix'], true);
        $isClient = $accountType === 'client';

        if (isset($data['email'])) {
            $data['email'] = strtolower(trim((string) $data['email']));
        }

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

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $accountType = (string) ($data['account_type'] ?? 'client');
        $isPharmacy = in_array($accountType, ['pharmacy_normal', 'pharmacy_matrix'], true);

        return DB::transaction(function () use ($data, $accountType, $isPharmacy) {
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
                if (request()->hasFile('alvara_document')) {
                    $file = request()->file('alvara_document');
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
    }
}
