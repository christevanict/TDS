<?php

namespace Database\Seeders;

use App\Models\MasterAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Customer
        MasterAccount::create([
            'name'=>'account_receivable',
            'table_name'=>'customer',
            'account'=>'1100.01'
        ]);
        MasterAccount::create([
            'name'=>'account_dp',
            'table_name'=>'customer',
            'account'=>'2100.02'
        ]);
        MasterAccount::create([
            'name'=>'account_add_tax',
            'table_name'=>'customer',
            'account'=>'2410.01'
        ]);
        MasterAccount::create([
            'name'=>'account_add_tax_bonded_zone',
            'table_name'=>'customer',
            'account'=>'2410.01'
        ]);

        //Item
        MasterAccount::create([
            'name'=>'account_barang_rusak',
            'table_name'=>'item',
            'account'=>'7200.03'
        ]);
    }
}
