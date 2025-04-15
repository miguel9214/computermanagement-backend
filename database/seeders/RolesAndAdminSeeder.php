<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    public function run()
    {
         // Asegúrate de usar el mismo guard que tu autenticación JWT
         $guard = 'api';

         // Crear roles
         Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
         Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
    }
}

