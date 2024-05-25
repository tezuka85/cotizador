<?php


use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get roles and permissions
        $structure_permissions = include 'rolesandpermissions/Permissions.php';
        $roles       = include  'rolesandpermissions/Roles.php';

        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        foreach ($structure_permissions as $structure){
            \App\Models\PermissionCustom::create([
                'title' => $structure['permissions']['title'],
                'name'   => $structure['permissions']['name'],
                'module_id' => $structure['module_id'],
                'class_method' => $structure['class_method']
            ]);
        }

        // Create roles and assignment permissions

        foreach ($roles as $name => $levels){
            foreach ($levels as $level => $permissions){
                $role = Role::create([
                    'level' => $level,
                    'title' => $name,
                    'name' => snake_case($name.$level),
                    'guard_name'=>'web'
                ]);
                foreach ($permissions as $permission){
                    try{
                        if($permission == '*'){
                            $role->givePermissionTo(\App\Models\PermissionCustom::all());
                        }else{
                            $role->givePermissionTo($permission);
                        }
                    }catch (\Illuminate\Database\QueryException $exception){
                        \Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyException($exception);
                    }
                }
            }
        }

        // Create two modules
        \App\Models\Module::create([
            'id' => 1,
            'name' => 'Dashboard',
            'class' => 'DashboardController',
        ]);

        \App\Models\Module::create([
            'id' => 2,
            'name' => 'Nuevo comercio',
            'class' => 'NuevoComercioController',
        ]);

        \App\Models\Module::create([
            'id' => 3,
            'name' => 'Administrar Menú',
            'class' => 'MenuController'
        ]);

        \App\Models\Menu::create([
            'id' => 1,
            'name' => 'Dashboard',
            'module_id' => '1',
            'method' => 'index',
            'icon' => 'fa fa-home'
        ]);

        \App\Models\Menu::create([
            'id' => 2,
            'name' => 'Nuevo Comercio',
            'module_id' => '2',
            'method' => 'wizard',
            'icon' => 'fa fa-building'
        ]);

        \App\Models\Menu::create([
            'id' => 3,
            'name' => 'Administrar Menú',
            'module_id' => '3',
            'method' => 'index',
            'icon' => 'fa fa-sitemap'
        ]);

        // Test
        $user = \App\User::find(1);
        //die(env('PROFILE_SUPER_ADMIN'));
        $user->assignRole(env('PROFILE_SUPER_ADMIN'));

    }
}
