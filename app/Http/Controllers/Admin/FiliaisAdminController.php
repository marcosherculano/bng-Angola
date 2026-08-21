<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PharmacyBranch;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FiliaisAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PharmacyBranch::query()->with(['matrix', 'user']);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('branch_name', 'like', "%{$q}%")
                    ->orWhere('province', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('email', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('matrix_id')) {
            $query->where('matrix_id', (int) $request->input('matrix_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        $branches = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.filiais.index', [
            'branches' => $branches,
        ]);
    }

    public function approve(Request $request, PharmacyBranch $branch)
    {
        DB::transaction(function () use ($request, $branch) {
            $branch->is_active = true;
            $branch->status = 'approved';
            $branch->save();

            $user = $branch->user;
            if ($user) {
                $user->status = 'approved';
                $user->approved_at = Carbon::now();
                $user->approved_by = optional($request->user())->id;
                $user->save();
            }

            ActivityLogger::log(
                $request,
                'admin_branch_approved',
                'Filial aprovada: '.$branch->branch_name,
                PharmacyBranch::class,
                $branch->id
            );
        });

        return redirect()->route('admin.filiais.index')->with('success', 'Filial aprovada e activada.');
    }

    public function alvaraDocument(Request $request, PharmacyBranch $branch)
    {
        $path = (string) ($branch->document_path ?? '');
        if ($path === '') {
            return redirect()->route('admin.filiais.index')->with('error', 'Sem documento de alvará para esta filial.');
        }

        if (! Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.filiais.index')->with('error', 'Documento não encontrado no servidor.');
        }

        ActivityLogger::log(
            $request,
            'admin_branch_alvara_document_download',
            'Documento do alvará baixado (Filial ID '.$branch->id.'): '.$branch->branch_name,
            PharmacyBranch::class,
            $branch->id
        );

        return Storage::disk('local')->download($path);
    }

    public function update(Request $request, PharmacyBranch $branch)
    {
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
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],

            'user_name' => ['required', 'string', 'max:150'],
            'user_email' => ['required', 'email', 'max:255', 'unique:users,email,'.$branch->user_id],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_password' => ['nullable', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
        ]);

        DB::transaction(function () use ($request, $data, $branch) {
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
            if (array_key_exists('monthly_fee', $data)) {
                $branch->monthly_fee = $data['monthly_fee'] !== null ? (float) $data['monthly_fee'] : null;
            }
            $branch->save();

            ActivityLogger::log(
                $request,
                'admin_branch_updated',
                'Filial actualizada: '.$branch->branch_name,
                PharmacyBranch::class,
                $branch->id
            );
        });

        return redirect()->route('admin.filiais.index')->with('success', 'Filial actualizada com sucesso.');
    }

    public function destroy(Request $request, PharmacyBranch $branch)
    {
        DB::transaction(function () use ($request, $branch) {
            $user = $branch->user;

            $branchName = (string) $branch->branch_name;
            $branchId = (int) $branch->id;

            $branch->delete();

            if ($user) {
                $user->delete();
            }

            ActivityLogger::log(
                $request,
                'admin_branch_deleted',
                'Filial eliminada: '.$branchName,
                PharmacyBranch::class,
                $branchId
            );
        });

        return redirect()->route('admin.filiais.index')->with('success', 'Filial eliminada.');
    }
}
