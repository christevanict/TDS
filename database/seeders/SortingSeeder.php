<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class SortingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = DB::table('sorting')->where("transaction_type","Purchase")->count();
        if($cek == 0){
            DB::table('sorting')->insert([
                [
                    'transaction_type' => "Purchase",
                    'number' => 1,
                    'times' => 1,
                ]
            ]);
        }

        $cek = DB::table('sorting')->where("transaction_type","Purchase Return")->count();
        if($cek == 0){
            DB::table('sorting')->insert([
                [
                    'transaction_type' => "Purchase Return",
                    'number' => 2,
                    'times' => -1,
                ]
            ]);
        }

        $cek = DB::table('sorting')->where("transaction_type","Sales")->count();
        if($cek == 0){
            DB::table('sorting')->insert([
                [
                    'transaction_type' => "Sales",
                    'number' => 3,
                    'times' => -1,
                ]
            ]);
        }

        $cek = DB::table('sorting')->where("transaction_type","Sales Return")->count();
        if($cek == 0){
            DB::table('sorting')->insert([
                [
                    'transaction_type' => "Sales Return",
                    'number' => 4,
                    'times' => 1,
                ]
            ]);
        }
    }
}
