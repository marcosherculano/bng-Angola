<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyBranch;
use App\Models\PharmacyBranchPaymentSetting;
use App\Models\PharmacyPaymentSetting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    private function pharmacyOrRedirect(Request $request)
    {
        $user = $request->user();
        $pharmacy = optional($user)->pharmacy;

        if (! $pharmacy && $user && (string) ($user->role ?? '') === 'pharmacy_branch') {
            $branch = PharmacyBranch::query()->with(['matrix'])->where('user_id', $user->id)->first();
            $pharmacy = optional($branch)->matrix;
        }

        if (! $pharmacy) {
            return redirect()->route('pharmacy.painel')->with('error', 'A sua conta não está associada a nenhuma farmácia.');
        }

        return $pharmacy;
    }

    private function branchOrNull(Request $request): ?PharmacyBranch
    {
        $user = $request->user();
        if (! $user || (string) ($user->role ?? '') !== 'pharmacy_branch') {
            return null;
        }

        return PharmacyBranch::query()->with(['matrix'])->where('user_id', $user->id)->first();
    }

    public function edit(Request $request)
    {
        $branch = $this->branchOrNull($request);
        if ($branch) {
            return view('pharmacy.payment_settings.edit', [
                'pharmacy' => $branch->matrix,
                'branch' => $branch,
                'settings' => $branch->paymentSettings,
            ]);
        }

        $pharmacy = $this->pharmacyOrRedirect($request);
        if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
            return $pharmacy;
        }

        return view('pharmacy.payment_settings.edit', [
            'pharmacy' => $pharmacy,
            'branch' => null,
            'settings' => $pharmacy->paymentSettings,
        ]);
    }

    public function update(Request $request)
    {
        $branch = $this->branchOrNull($request);
        if (! $branch) {
            $pharmacy = $this->pharmacyOrRedirect($request);
            if ($pharmacy instanceof \Illuminate\Http\RedirectResponse) {
                return $pharmacy;
            }
        }

        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'iban' => ['nullable', 'string', 'max:80'],
            'express_number' => ['nullable', 'string', 'max:80'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        $isActive = (bool) ($data['is_active'] ?? false);

        if ($branch) {
            $settings = PharmacyBranchPaymentSetting::query()->firstOrNew([
                'pharmacy_branch_id' => $branch->id,
            ]);
        } else {
            $settings = PharmacyPaymentSetting::query()->firstOrNew([
                'pharmacy_id' => $pharmacy->id,
            ]);
        }

        $settings->fill([
            'is_active' => $isActive,
            'bank_name' => isset($data['bank_name']) && trim((string) $data['bank_name']) !== '' ? trim((string) $data['bank_name']) : null,
            'account_holder' => isset($data['account_holder']) && trim((string) $data['account_holder']) !== '' ? trim((string) $data['account_holder']) : null,
            'account_number' => isset($data['account_number']) && trim((string) $data['account_number']) !== '' ? trim((string) $data['account_number']) : null,
            'iban' => isset($data['iban']) && trim((string) $data['iban']) !== '' ? trim((string) $data['iban']) : null,
            'express_number' => isset($data['express_number']) && trim((string) $data['express_number']) !== '' ? trim((string) $data['express_number']) : null,
            'instructions' => isset($data['instructions']) && trim((string) $data['instructions']) !== '' ? trim((string) $data['instructions']) : null,
        ]);

        $settings->save();

        return redirect()->route('pharmacy.payment_settings.edit')->with('success', 'Configurações de pagamento guardadas.');
    }
}
