<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Error;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder RBAC 5-tier yang IDEMPOTEN.
 *
 * - Semua baris dibuat dengan firstOrCreate sehingga aman dijalankan berulang.
 * - Diakhiri dengan reset cache permission (permission:cache-reset).
 * - super-admin memperoleh seluruh permission lewat Gate::before, tetapi tetap
 *   diberi permission eksplisit agar tampil konsisten di panel.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $guard = 'web';

        // 1. Pastikan seluruh permission ada.
        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, $guard);
        }

        // Cache dibersihkan sekali lagi setelah semua permission dibuat,
        // agar Role::syncPermissions menemukan nama permission di database
        // (findOrCreate dapat meninggalkan cache dalam keadaan tidak konsisten).
        $registrar->forgetCachedPermissions();

        // 2. Pastikan seluruh peran ada dan sinkronkan permission-nya.
        foreach (RoleName::cases() as $roleName) {
            $role = Role::findOrCreate($roleName->value, $guard);

            $role->syncPermissions(
                array_map(
                    static fn (PermissionName $permission): string => $permission->value,
                    $this->permissionsForRole($roleName),
                )
            );
        }

        $registrar->forgetCachedPermissions();
        Artisan::call('permission:cache-reset');

        // Seeder harus tetap dapat dijalankan langsung dari test tanpa
        // melewati console command. `$command` tidak bertipe nullable di
        // deklarasi Laravel, jadi keberadaannya diperiksa lewat properti
        // yang sudah diinisialisasi (uninitialized property = belum dijalankan
        // dari console command).
        try {
            $this->command->info(sprintf(
                'RBAC siap: %d peran, %d permission.',
                count(RoleName::cases()),
                count(PermissionName::cases()),
            ));
        } catch (Error) {
            // Dijalankan langsung (mis. dari test) — tidak ada output console.
        }
    }

    /**
     * Daftar permission untuk satu peran.
     *
     * super-admin mendapat gabungan seluruh permission; peran lain diambil
     * dari peta PermissionName.
     *
     * @return list<PermissionName>
     */
    private function permissionsForRole(RoleName $role): array
    {
        if ($role === RoleName::SUPER_ADMIN) {
            return PermissionName::cases();
        }

        return PermissionName::byRole()[$role->value] ?? [];
    }
}
