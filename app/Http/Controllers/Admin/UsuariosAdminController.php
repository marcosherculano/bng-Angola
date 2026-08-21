<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\MedicineTransfer;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UsuariosAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', trim((string) $request->input('role')));
        }

        if ($request->filled('status')) {
            $query->where('status', trim((string) $request->input('status')));
        }

        $users = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.usuarios.index', [
            'users' => $users,
        ]);
    }

    public function approve(Request $request, User $user)
    {
        $user->status = 'approved';
        $user->approved_at = Carbon::now();
        $user->approved_by = optional($request->user())->id;
        $user->save();

        ActivityLogger::log(
            $request,
            'admin_user_approved',
            'Utilizador aprovado: '.$user->email,
            User::class,
            $user->id
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Utilizador aprovado.');
    }

    public function suspend(Request $request, User $user)
    {
        if ((string) $user->role === 'admin') {
            return redirect()->route('admin.usuarios.index')->with('error', 'Não é possível suspender contas administrativas.');
        }

        $user->status = 'suspended';
        $user->save();

        ActivityLogger::log(
            $request,
            'admin_user_suspended',
            'Utilizador suspenso: '.$user->email,
            User::class,
            $user->id
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Utilizador suspenso.');
    }

    public function block(Request $request, User $user)
    {
        if ((string) $user->role === 'admin') {
            return redirect()->route('admin.usuarios.index')->with('error', 'Não é possível bloquear contas administrativas.');
        }

        $user->status = 'blocked';
        $user->save();

        ActivityLogger::log(
            $request,
            'admin_user_blocked',
            'Utilizador bloqueado: '.$user->email,
            User::class,
            $user->id
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Utilizador bloqueado.');
    }

    public function unrestrict(Request $request, User $user)
    {
        $user->status = 'approved';
        $user->save();

        ActivityLogger::log(
            $request,
            'admin_user_unrestricted',
            'Utilizador reactivado: '.$user->email,
            User::class,
            $user->id
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Utilizador reativado.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user() && (int) $request->user()->id === (int) $user->id) {
            return redirect()->route('admin.usuarios.index')->with('error', 'Não é possível eliminar o seu próprio utilizador.');
        }

        if ((string) $user->role === 'admin') {
            return redirect()->route('admin.usuarios.index')->with('error', 'Não é possível eliminar contas administrativas.');
        }

        $email = (string) $user->email;
        $id = (int) $user->id;

        if (in_array((string) $user->role, ['pharmacy_normal', 'pharmacy_matrix'], true)) {
            $pharmacy = Pharmacy::query()->where('user_id', $user->id)->first();
            if (! $pharmacy) {
                return redirect()->route('admin.usuarios.index')->with('error', 'Não foi possível localizar a farmácia deste utilizador.');
            }

            DB::transaction(function () use ($pharmacy, $user) {
                $medicineIds = Medicine::query()
                    ->where('pharmacy_id', $pharmacy->id)
                    ->pluck('id');

                Order::query()->where('pharmacy_id', $pharmacy->id)->delete();

                if ($medicineIds->isNotEmpty()) {
                    MedicineTransfer::query()->whereIn('medicine_id', $medicineIds)->delete();
                    MedicineInventory::query()->whereIn('medicine_id', $medicineIds)->delete();
                    Medicine::query()->whereIn('id', $medicineIds)->delete();
                }

                $branchUserIds = PharmacyBranch::query()
                    ->where('matrix_id', $pharmacy->id)
                    ->pluck('user_id');

                PharmacyBranch::query()->where('matrix_id', $pharmacy->id)->delete();
                if ($branchUserIds->isNotEmpty()) {
                    User::query()->whereIn('id', $branchUserIds)->delete();
                }

                $pharmacy->delete();
                $user->delete();
            });
        } elseif ((string) $user->role === 'pharmacy_branch') {
            $branch = PharmacyBranch::query()->where('user_id', $user->id)->first();
            if (! $branch) {
                return redirect()->route('admin.usuarios.index')->with('error', 'Não foi possível localizar a filial deste utilizador.');
            }

            DB::transaction(function () use ($branch, $user) {
                Order::query()->where('pharmacy_branch_id', $branch->id)->delete();
                MedicineInventory::query()
                    ->where('owner_type', 'pharmacy_branch')
                    ->where('owner_id', $branch->id)
                    ->delete();

                $branch->delete();
                $user->delete();
            });
        } else {
            try {
                $user->delete();
            } catch (QueryException $e) {
                return redirect()->route('admin.usuarios.index')->with(
                    'error',
                    'Não foi possível eliminar este utilizador porque existem registos associados (ex.: pedidos/itens).'
                );
            }
        }

        ActivityLogger::log(
            $request,
            'admin_user_deleted',
            'Utilizador eliminado definitivamente: '.$email,
            User::class,
            $id
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Utilizador eliminado definitivamente.');
    }
}
