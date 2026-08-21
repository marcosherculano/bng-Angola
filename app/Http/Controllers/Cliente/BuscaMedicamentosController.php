<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineInventory;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BuscaMedicamentosController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = MedicineInventory::query()
            ->with(['medicine', 'owner']);

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->whereHas('medicine', function ($m) use ($term) {
                $m->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%");
                });
            });
        }

        if ($request->filled('province')) {
            $province = trim((string) $request->input('province'));
            $query->whereHasMorph('owner', [Pharmacy::class, PharmacyBranch::class], function ($q) use ($province) {
                $q->where('province', $province);
            });
        }

        if ($request->filled('category')) {
            $category = trim((string) $request->input('category'));
            $query->whereHas('medicine', function ($m) use ($category) {
                $m->where('category', $category);
            });
        }

        if ($request->boolean('available_only')) {
            $query->where('is_available', true);
        }

        if ($request->boolean('in_stock_only')) {
            $query->where('stock', '>', 0);
        }

        $query->where(function ($q) {
            $q->whereHasMorph('owner', [Pharmacy::class], function ($ph) {
                $ph->where('is_active', true);
            })->orWhereHasMorph('owner', [PharmacyBranch::class], function ($br) {
                $br->where('is_active', true);
                if (Schema::hasColumn('pharmacy_branches', 'status')) {
                    $br->where('status', 'approved');
                }
            });
        });

        if ($request->filled('pharmacy_id')) {
            $pid = (int) $request->input('pharmacy_id');
            $query->where(function ($q) use ($pid) {
                $q->where(function ($sub) use ($pid) {
                    $sub->where('owner_type', 'pharmacy')
                        ->where('owner_id', $pid);
                })->orWhere(function ($sub) use ($pid) {
                    $sub->where('owner_type', 'pharmacy_branch')
                        ->whereIn('owner_id', PharmacyBranch::query()->where('matrix_id', $pid)->select('id'));
                });
            });
        }

        $sort = trim((string) $request->input('sort', 'price_asc'));
        if ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'stock_desc') {
            $query->orderByDesc('stock');
        } elseif ($sort === 'name_asc') {
            $query->join('medicines', 'medicines.id', '=', 'medicine_inventories.medicine_id')
                ->orderBy('medicines.name');
        } else {
            $query->orderBy('price');
        }

        $inventories = $query->select('medicine_inventories.*')->paginate(20)->withQueryString();

        $provinces = Pharmacy::query()
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        $branchProvinces = PharmacyBranch::query()
            ->where('is_active', true)
            ->when(Schema::hasColumn('pharmacy_branches', 'status'), function ($q) {
                $q->where('status', 'approved');
            })
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        $provinces = $provinces->merge($branchProvinces)->unique()->values();

        $categories = Medicine::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('cliente.busca', [
            'inventories' => $inventories,
            'provinces' => $provinces,
            'categories' => $categories,
        ]);
    }
}
