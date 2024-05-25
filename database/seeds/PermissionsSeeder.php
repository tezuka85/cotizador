<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrador = Role::create([
            'name' => 'Administrador',
            'guard_name' => 'api',
        ]);

        $comercio = Role::create([
            'name' => 'Comercio',
            'guard_name' => 'api',
        ]);

        $permissions = ['crear_usuario','crear_comercio', 'crear_negociacion', 'generar_cotizacion', 'generar_guia',
            'consultar_track','crear_pick_up'];

        foreach ($permissions as $permission){
            Permission::create([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        $administrador->syncPermissions($permissions);
        $comercio->givePermissionTo('generar_cotizacion');
        $comercio->givePermissionTo('generar_guia');
        $comercio->givePermissionTo('consultar_track');
        $comercio->givePermissionTo('crear_pick_up');

        $user = User::find(1);
        $user->assignRole('Administrador');
    }
}
