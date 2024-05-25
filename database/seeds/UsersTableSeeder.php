<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('usuarios')->insert([
            'nombres' => 'Eduardo',
            'apellidos' => 'Cruz',
            'email' => 'eduardo.cruz@claroshop.com',
            'password' => bcrypt('secret'),
        ]);
    }
}
