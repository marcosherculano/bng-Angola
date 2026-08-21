<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MonthlyFee;
use App\Models\Pharmacy;
use App\Models\PharmacyBranch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class BngSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@bng.ao'],
            [
                'name' => 'Administrador BNG',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
                'status' => 'approved',
                'phone' => '+244900000000',
                'approved_at' => $now,
            ]
        );

        $client1 = User::query()->updateOrCreate(
            ['email' => 'joao@bng.ao'],
            [
                'name' => 'João Sebastião',
                'password' => Hash::make('Cliente@1234'),
                'role' => 'client',
                'status' => 'approved',
                'age' => 34,
                'phone' => '+244911111111',
                'province' => 'Luanda',
                'approved_at' => $now,
                'approved_by' => $admin->id,
            ]
        );

        $client2 = User::query()->updateOrCreate(
            ['email' => 'maria@bng.ao'],
            [
                'name' => 'Maria Afonso',
                'password' => Hash::make('Cliente@1234'),
                'role' => 'client',
                'status' => 'approved',
                'age' => 28,
                'phone' => '+244922222222',
                'province' => 'Benguela',
                'approved_at' => $now,
                'approved_by' => $admin->id,
            ]
        );

        $userPharmacy1 = User::query()->updateOrCreate(
            ['email' => 'farmacia.central@bng.ao'],
            [
                'name' => 'Responsável Farmácia Central',
                'password' => Hash::make('Farmacia@1234'),
                'role' => 'pharmacy_normal',
                'status' => 'approved',
                'phone' => '+244933333333',
                'province' => 'Luanda',
                'approved_at' => $now,
                'approved_by' => $admin->id,
            ]
        );

        $farmaciaCentral = Pharmacy::query()->updateOrCreate(
            ['user_id' => $userPharmacy1->id],
            [
                'business_name' => 'Farmácia Central',
                'nif' => '5000000001',
                'alvara' => 'ALV001',
                'phone' => '+244933333333',
                'email' => 'central@bng.ao',
                'province' => 'Luanda',
                'city' => 'Luanda',
                'neighborhood' => 'Ingombota',
                'street' => 'Rua Principal',
                'latitude' => -8.83833333,
                'longitude' => 13.23444444,
                'type' => 'normal',
                'is_active' => true,
                'subscription_plan' => 'pro',
                'monthly_fee' => 25000,
                'approved_at' => $now,
                'approved_by' => $admin->id,
                'trial_starts_at' => $now->copy()->subDays(40),
                'trial_ends_at' => $now->copy()->subDays(10),
            ]
        );

        MonthlyFee::query()->updateOrCreate(
            ['pharmacy_id' => $farmaciaCentral->id, 'cycle_start' => $now->copy()->subDays(10)->toDateString()],
            [
                'cycle_end' => $now->copy()->addDays(20)->toDateString(),
                'due_at' => $now->copy()->addDays(20),
                'amount' => 25000,
                'status' => 'approved',
                'approved_at' => $now->copy()->subDays(10),
                'approved_by' => $admin->id,
            ]
        );

        $userPharmacy2 = User::query()->updateOrCreate(
            ['email' => 'farmacia.bemestar@bng.ao'],
            [
                'name' => 'Responsável Farmácia Bem Estar',
                'password' => Hash::make('Farmacia@1234'),
                'role' => 'pharmacy_normal',
                'status' => 'approved',
                'phone' => '+244944444444',
                'province' => 'Huambo',
                'approved_at' => $now,
                'approved_by' => $admin->id,
            ]
        );

        $farmaciaBemEstar = Pharmacy::query()->updateOrCreate(
            ['user_id' => $userPharmacy2->id],
            [
                'business_name' => 'Farmácia Bem Estar',
                'nif' => '5000000002',
                'alvara' => 'ALV002',
                'phone' => '+244944444444',
                'email' => 'bemestar@bng.ao',
                'province' => 'Huambo',
                'city' => 'Huambo',
                'neighborhood' => 'Centro',
                'street' => 'Avenida',
                'latitude' => -12.77555555,
                'longitude' => 15.73944444,
                'type' => 'normal',
                'is_active' => true,
                'subscription_plan' => 'basic',
                'monthly_fee' => 10000,
                'approved_at' => $now,
                'approved_by' => $admin->id,
                'trial_starts_at' => $now,
                'trial_ends_at' => $now->copy()->addDays(30),
            ]
        );

        $userMatrix = User::query()->updateOrCreate(
            ['email' => 'rede.saude@bng.ao'],
            [
                'name' => 'Responsável Rede Saúde Angola',
                'password' => Hash::make('Farmacia@1234'),
                'role' => 'pharmacy_matrix',
                'status' => 'approved',
                'phone' => '+244955555555',
                'province' => 'Luanda',
                'approved_at' => $now,
                'approved_by' => $admin->id,
            ]
        );

        $redeSaude = Pharmacy::query()->updateOrCreate(
            ['user_id' => $userMatrix->id],
            [
                'business_name' => 'Rede Saúde Angola',
                'nif' => '5000000003',
                'alvara' => 'ALV003',
                'phone' => '+244955555555',
                'email' => 'rede@bng.ao',
                'province' => 'Luanda',
                'city' => 'Luanda',
                'neighborhood' => 'Maianga',
                'street' => 'Avenida Principal',
                'latitude' => -8.83000000,
                'longitude' => 13.24000000,
                'type' => 'matrix',
                'is_active' => true,
                'subscription_plan' => 'premium',
                'monthly_fee' => 50000,
                'approved_at' => $now,
                'approved_by' => $admin->id,
                'trial_starts_at' => $now,
                'trial_ends_at' => $now->copy()->addDays(30),
            ]
        );

        $userBranch = User::query()->updateOrCreate(
            ['email' => 'filial.viana@bng.ao'],
            [
                'name' => 'Responsável Filial Viana',
                'password' => Hash::make('Farmacia@1234'),
                'role' => 'pharmacy_branch',
                'status' => 'approved',
                'phone' => '+244966666666',
                'province' => 'Luanda',
                'approved_at' => $now,
                'approved_by' => $admin->id,
            ]
        );

        PharmacyBranch::query()->updateOrCreate(
            ['user_id' => $userBranch->id],
            [
                'matrix_id' => $redeSaude->id,
                'branch_name' => 'Filial Viana',
                'nif' => '5000000004',
                'alvara' => 'ALV004',
                'phone' => '+244966666666',
                'email' => 'viana@bng.ao',
                'province' => 'Luanda',
                'city' => 'Viana',
                'neighborhood' => 'Viana Centro',
                'street' => 'Rua da Filial',
                'latitude' => -8.90333333,
                'longitude' => 13.37000000,
                'is_active' => true,
            ]
        );

        $this->seedMedicines($farmaciaCentral->id);
        $this->seedMedicines($farmaciaBemEstar->id);
        $this->seedMedicines($redeSaude->id);
    }

    private function seedMedicines(int $pharmacyId): void
    {
        $meds = [
            ['name' => 'Paracetamol 500mg', 'category' => 'Analgésico', 'price' => 500, 'stock' => 25],
            ['name' => 'Ibuprofeno 400mg', 'category' => 'Anti-inflamatório', 'price' => 800, 'stock' => 18],
            ['name' => 'Amoxicilina 500mg', 'category' => 'Antibiótico', 'price' => 1500, 'stock' => 12],
            ['name' => 'Vitamina C', 'category' => 'Vitaminas e Suplementos', 'price' => 600, 'stock' => 30],
            ['name' => 'Omeprazol 20mg', 'category' => 'Gastrointestinal', 'price' => 1200, 'stock' => 10],
        ];

        foreach ($meds as $m) {
            Medicine::query()->updateOrCreate(
                ['pharmacy_id' => $pharmacyId, 'name' => $m['name']],
                [
                    'barcode' => null,
                    'category' => $m['category'],
                    'description' => null,
                    'price' => $m['price'],
                    'stock' => $m['stock'],
                    'requires_prescription' => false,
                    'image_path' => null,
                    'is_available' => true,
                ]
            );
        }
    }
}
