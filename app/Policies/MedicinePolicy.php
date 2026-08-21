<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\PharmacyBranch;
use App\Models\User;

class MedicinePolicy
{
    private function resolvePharmacy(User $user)
    {
        $pharmacy = $user->pharmacy;
        if ($pharmacy) {
            return $pharmacy;
        }

        if ((string) ($user->role ?? '') === 'pharmacy_branch') {
            $branch = PharmacyBranch::query()->with(['matrix'])->where('user_id', $user->id)->first();
            return optional($branch)->matrix;
        }

        return null;
    }

    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'pharmacy_normal', 'pharmacy_matrix', 'pharmacy_branch'], true);
    }

    public function create(User $user)
    {
        if ($user->role === 'admin') {
            return true;
        }

        if (! in_array($user->role, ['pharmacy_normal', 'pharmacy_matrix', 'pharmacy_branch'], true)) {
            return false;
        }

        return (bool) $this->resolvePharmacy($user);
    }

    public function update(User $user, Medicine $medicine)
    {
        if ($user->role === 'admin') {
            return true;
        }

        if (! in_array($user->role, ['pharmacy_normal', 'pharmacy_matrix', 'pharmacy_branch'], true)) {
            return false;
        }

        $pharmacy = $this->resolvePharmacy($user);
        if (! $pharmacy) {
            return false;
        }

        return (int) $medicine->pharmacy_id === (int) $pharmacy->id;
    }

    public function delete(User $user, Medicine $medicine)
    {
        return $this->update($user, $medicine);
    }
}
