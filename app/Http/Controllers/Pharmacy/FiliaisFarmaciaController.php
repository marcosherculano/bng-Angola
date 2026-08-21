<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FiliaisFarmaciaController extends Controller
{
    private function pharmacyOrRedirect(Request $request)
    {
        $pharmacy = optional($request->user())->pharmacy;

        if (! $pharmacy) {
            return redirect()->route('pharmacy.painel')->with('error', 'A sua conta não está associada a nenhuma farmácia.');
        }

        return $pharmacy;
    }

    public function index(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $branches = PharmacyBranch::query()
            ->with(['user'])
            ->where('matrix_id', $pharmacy->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('pharmacy.filiais.index', [
            'pharmacy' => $pharmacy,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        $data = $request->validate([
            'branch_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
            'street' => ['nullable', 'string', 'max:200'],
            'opening_hours' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],

            'branch_document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120'],
            'branch_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:5120'],

            'user_name' => ['required', 'string', 'max:150'],
            'user_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_password' => ['required', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
        ]);

        $documentPath = null;
        $file = $request->file('branch_document');
        $documentPath = $file->store('pharmacy_branches/documents', 'local');

        $imagePath = null;
        if ($request->hasFile('branch_image')) {
            $file = $request->file('branch_image');
            $imagePath = $file->store('pharmacy_branches/images', 'local');
        }

        DB::transaction(function () use ($data, $pharmacy, $documentPath, $imagePath) {
            $user = User::query()->create([
                'name' => trim((string) $data['user_name']),
                'email' => trim((string) $data['user_email']),
                'phone' => isset($data['user_phone']) ? trim((string) $data['user_phone']) : null,
                'password' => Hash::make((string) $data['user_password']),
                'role' => 'pharmacy_branch',
                'status' => 'pending',
                'province' => trim((string) $data['province']),
                'location_lat' => (float) $data['latitude'],
                'location_lng' => (float) $data['longitude'],
            ]);

            PharmacyBranch::query()->create([
                'matrix_id' => $pharmacy->id,
                'user_id' => $user->id,
                'branch_name' => trim((string) $data['branch_name']),
                'nif' => $pharmacy->nif,
                'alvara' => $pharmacy->alvara,
                'phone' => isset($data['phone']) ? trim((string) $data['phone']) : null,
                'email' => isset($data['email']) ? trim((string) $data['email']) : null,
                'province' => trim((string) $data['province']),
                'city' => isset($data['city']) ? trim((string) $data['city']) : null,
                'neighborhood' => isset($data['neighborhood']) ? trim((string) $data['neighborhood']) : null,
                'street' => isset($data['street']) ? trim((string) $data['street']) : null,
                'opening_hours' => trim((string) $data['opening_hours']),
                'latitude' => (float) $data['latitude'],
                'longitude' => (float) $data['longitude'],
                'is_active' => false,
                'status' => 'pending',
                'document_path' => $documentPath,
                'image_path' => $imagePath,
            ]);
        });

        return redirect()->route('pharmacy.filiais.index')->with('success', 'Filial criada. Aguarda aprovação do administrador para ficar activa.');
    }

    public function update(Request $request, PharmacyBranch $branch)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((int) $branch->matrix_id !== (int) $pharmacy->id) {
            return redirect()->route('pharmacy.filiais.index')->with('error', 'Acesso não autorizado.');
        }

        $data = $request->validate([
            'branch_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
            'street' => ['nullable', 'string', 'max:200'],
            'opening_hours' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],

            'user_name' => ['required', 'string', 'max:150'],
            'user_email' => ['required', 'email', 'max:255', 'unique:users,email,'.$branch->user_id],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_password' => ['nullable', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
        ]);

        DB::transaction(function () use ($data, $branch) {
            $user = $branch->user;
            if ($user) {
                $user->name = trim((string) $data['user_name']);
                $user->email = trim((string) $data['user_email']);
                $user->phone = isset($data['user_phone']) ? trim((string) $data['user_phone']) : null;
                $user->province = trim((string) $data['province']);
                $user->location_lat = (float) $data['latitude'];
                $user->location_lng = (float) $data['longitude'];
                if (! empty($data['user_password'])) {
                    $user->password = Hash::make((string) $data['user_password']);
                }
                $user->save();
            }

            $branch->branch_name = trim((string) $data['branch_name']);
            $branch->phone = isset($data['phone']) ? trim((string) $data['phone']) : null;
            $branch->email = isset($data['email']) ? trim((string) $data['email']) : null;
            $branch->province = trim((string) $data['province']);
            $branch->city = isset($data['city']) ? trim((string) $data['city']) : null;
            $branch->neighborhood = isset($data['neighborhood']) ? trim((string) $data['neighborhood']) : null;
            $branch->street = isset($data['street']) ? trim((string) $data['street']) : null;
            $branch->opening_hours = trim((string) $data['opening_hours']);
            $branch->latitude = (float) $data['latitude'];
            $branch->longitude = (float) $data['longitude'];
            $branch->save();
        });

        return redirect()->route('pharmacy.filiais.index')->with('success', 'Filial actualizada com sucesso.');
    }

    public function toggleActive(Request $request, PharmacyBranch $branch)
    {
        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        if ((int) $branch->matrix_id !== (int) $pharmacy->id) {
            return redirect()->route('pharmacy.filiais.index')->with('error', 'Acesso não autorizado.');
        }

        if (Schema::hasColumn('pharmacy_branches', 'status') && (string) ($branch->status ?? 'pending') !== 'approved') {
            return redirect()->route('pharmacy.filiais.index')->with('error', 'Esta filial ainda não foi aprovada pelo administrador.');
        }

        $branch->is_active = ! (bool) $branch->is_active;
        $branch->save();

        return redirect()->route('pharmacy.filiais.index')->with('success', 'Estado da filial actualizado.');
    }
}
