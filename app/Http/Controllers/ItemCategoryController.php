<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemCategory;
use App\Models\Coa;
use App\Models\Company;
use App\Models\Item;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $itemCategories = ItemCategory::orderBy('item_category_code','asc')->get();
        $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_item'];
        return view('master.item-category',[
            'itemCategories' => $itemCategories,
            'coas' => $coas,
            'companies'=>$companies,
            'privileges'=>$privileges,
        ]);
    }

    private function generateCategoryNumber($company, $name) {
        $today = date('Ymd');
        $firstThreeLetters = strtoupper(Str::limit($name, 3, ''));
        $lastCategory = ItemCategory::whereRaw("SUBSTRING(item_category_code, 1,  3) = '".$firstThreeLetters."'")->orderBy('item_category_code', 'desc')
            ->first();
        if ($lastCategory) {
            $lastNumber = (int)substr($lastCategory->item_category_code, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return $firstThreeLetters .'_' . $newNumber;
    }


    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            $item_category_code = $this->generateCategoryNumber($request->company_code, $request->item_category_name);
            if(ItemCategory::where('item_category_code',$request->item_category_code)->count()<1){
                ItemCategory::create([
                    'item_category_code'=>$item_category_code,
                    'item_category_name'=>$request->item_category_name,
                    'company_code'=>$request->company_code,
                    'acc_number_purchase'=>$request->acc_number_purchase,
                    'acc_number_purchase_return'=>$request->acc_number_purchase_return,
                    'acc_number_purchase_discount'=>$request->acc_number_purchase_discount,
                    'acc_number_sales'=>$request->acc_number_sales,
                    'acc_number_sales_return'=>$request->acc_number_sales_return,
                    'acc_number_sales_discount'=>$request->acc_number_sales_discount,
                    'acc_number_grpo'=>$request->acc_number_grpo,
                    'acc_number_do'=>$request->acc_number_do,
                    'acc_number_wip'=>$request->acc_number_wip,
                    'acc_number_wip_variance'=>$request->acc_number_wip_variance,
                    'acc_barang_rusak'=>$request->acc_barang_rusak,
                    'account_inventory'=>$request->account_inventory,
                    'acc_cogs'=>$request->acc_cogs,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Item Category added successfully!');
            }else{
                return redirect()->back()->with('error', 'Item Category code  must not be same');

            }
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
                $itemCategory = ItemCategory::where('item_category_code',$id)->update([
                    // 'item_category_code'=>$request->item_category_code,
                        'item_category_name'=>$request->item_category_name,
                        'company_code'=>$request->company_code,
                        'acc_number_purchase'=>$request->acc_number_purchase,
                        'acc_number_purchase_return'=>$request->acc_number_purchase_return,
                        'acc_number_purchase_discount'=>$request->acc_number_purchase_discount,
                        'acc_number_sales'=>$request->acc_number_sales,
                        'acc_number_sales_return'=>$request->acc_number_sales_return,
                        'acc_number_sales_discount'=>$request->acc_number_sales_discount,
                        'acc_barang_rusak'=>$request->acc_barang_rusak,
                        'acc_number_grpo'=>$request->acc_number_grpo,
                        'acc_number_do'=>$request->acc_number_do,
                        'acc_number_wip'=>$request->acc_number_wip,
                        'acc_number_wip_variance'=>$request->acc_number_wip_variance,
                        'account_inventory'=>$request->account_inventory,
                        'acc_cogs'=>$request->acc_cogs,
                        'updated_by'=>Auth::user()->username,
                ]);


                DB::commit();

                return redirect()->back()->with('success', 'Item Category updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $itemCategory = ItemCategory::where('item_category_code',$id);
            $exist = Item::where('item_category',$id)->exists();
            if($exist){
                return redirect()->back()->with('error', 'Tidak bisa hapus karena sudah digunakan');
            }
            $itemCategory->delete();
                DB::commit();

                return redirect()->back()->with('success', 'Item Category deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
