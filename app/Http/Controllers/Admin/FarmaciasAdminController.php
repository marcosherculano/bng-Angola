<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FarmaciasAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Pharmacy::query()->with('user');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('business_name', 'like', "%{$q}%")
                    ->orWhere('nif', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', trim((string) $request->input('type')));
        }

        if ($request->filled('province')) {
            $query->where('province', trim((string) $request->input('province')));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        $pharmacies = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.farmacias.index', [
            'pharmacies' => $pharmacies,
        ]);
    }

    public function updateMonthlyFee(Request $request, Pharmacy $pharmacy)
    {
        $data = $request->validate([
            'monthly_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $pharmacy->monthly_fee = (float) $data['monthly_fee'];
        $pharmacy->save();

        ActivityLogger::log(
            $request,
            'admin_pharmacy_monthly_fee_updated',
            'Mensalidade base actualizada (Farmácia ID '.$pharmacy->id.'): '.$pharmacy->business_name,
            Pharmacy::class,
            $pharmacy->id
        );

        return redirect()->route('admin.farmacias.index')->with('success', 'Mensalidade base actualizada.');
    }

    public function toggleActive(Request $request, Pharmacy $pharmacy)
    {
        $pharmacy->is_active = ! $pharmacy->is_active;
        $pharmacy->save();

        ActivityLogger::log(
            $request,
            'admin_pharmacy_toggle_active',
            'Farmácia '.($pharmacy->is_active ? 'activada' : 'desactivada').': '.$pharmacy->business_name,
            Pharmacy::class,
            $pharmacy->id
        );

        return redirect()->route('admin.farmacias.index')->with(
            'success',
            $pharmacy->is_active ? 'Farmácia activada.' : 'Farmácia desactivada.'
        );
    }

    public function updateAlvaraDocument(Request $request, Pharmacy $pharmacy)
    {
        $data = $request->validate([
            'alvara_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:5120'],
        ]);

        $oldPath = (string) ($pharmacy->alvara_document_path ?? '');

        $file = $request->file('alvara_document');
        $newPath = $file->store('pharmacies/alvara_documents', 'local');

        $pharmacy->alvara_document_path = $newPath;
        $pharmacy->save();

        if ($oldPath !== '' && $oldPath !== $newPath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        ActivityLogger::log(
            $request,
            'admin_pharmacy_alvara_document_updated',
            'Documento do alvará actualizado: '.$pharmacy->business_name,
            Pharmacy::class,
            $pharmacy->id
        );

        return redirect()->route('admin.farmacias.index')->with('success', 'Documento do alvará actualizado.');
    }

    public function alvaraDocument(Request $request, Pharmacy $pharmacy)
    {
        $path = (string) ($pharmacy->alvara_document_path ?? '');
        if ($path === '') {
            return redirect()->route('admin.farmacias.index')->with('error', 'Sem documento de alvará para esta farmácia.');
        }

        $disk = 'local';
        $downloadPath = $path;

        $candidates = [
            $path,
            ltrim($path, '/'),
            preg_replace('~^public/~', '', $path),
            preg_replace('~^storage/~', '', $path),
        ];

        $found = false;
        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }
            if (Storage::disk('local')->exists($candidate)) {
                $disk = 'local';
                $downloadPath = $candidate;
                $found = true;
                break;
            }
            if (Storage::disk('public')->exists($candidate)) {
                $disk = 'public';
                $downloadPath = $candidate;
                $found = true;
                break;
            }
        }

        if (! $found) {
            return redirect()->route('admin.farmacias.index')->with('error', 'Documento não encontrado no servidor. Path: '.$path);
        }

        ActivityLogger::log(
            $request,
            'admin_pharmacy_alvara_document_download',
            'Documento do alvará baixado: '.$pharmacy->business_name,
            Pharmacy::class,
            $pharmacy->id
        );

        return Storage::disk($disk)->download($downloadPath);
    }
}
