<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = [
            'master_role'=>[
                'read', 'create', 'update', 'cancel', 'delete', 'print', 'discount', 'price'
            ],
            'sales_order'=>[
                'read', 'create', 'update', 'cancel', 'delete', 'print', 'discount', 'price'
            ],
        ];
        Role::create([
            'role_number'=>'RO01',
            'name'=>'Admin',
            'privileges'=>$role
        ]);
    }
}
