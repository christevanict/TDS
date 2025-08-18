<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $menus = [
            'purchase_order' => 'Purchase Order',
            'sales_order' => 'Sales Order',
            'purchase_invoice' => 'Purchase Invoice',
            'sales_invoice' => 'Sales Invoice'
        ];
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_role'];

        $roles = Role::orderBy('id','asc')->get();

        return view('master.role', compact('menus','roles','privileges'));
    }

    private function generateRoleNumber(){
        $lastRole = Role::orderBy('id','desc')->first();
        if ($lastRole) {
            $lastNumber = (int)substr($lastRole->role_number,-2);
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        return "RO{$newNumber}";
    }

    public function insert(Request $request)
    {
        DB::beginTransaction();
            try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'privileges' => 'required|array'
            ]);

            // Process privileges into correct format
            $processedPrivileges = [];
            foreach ($request->privileges as $menu => $actions) {
                $processedPrivileges[$menu] = array_keys($actions);
            }

            Role::create([
                'role_number' => $this->generateRoleNumber(),
                'name' => $validated['name'],
                'privileges' => $processedPrivileges
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Role created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Role creation failed: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $roleBefore = Role::findOrFail($id);
            $validated = $request->validate([
                'role_number' => 'required',
                'name' => 'required|string|max:255',
                'privileges' => 'required'
            ]);

            $processedPrivileges = [];
            foreach ($request->privileges as $menu => $actions) {
                $processedPrivileges[$menu] = array_keys($actions);
            }

            $roleBefore->update([
                'role_number' => $validated['role_number'],
                'name' => $validated['name'],
                'privileges' => $processedPrivileges
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Role updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Role updated: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);
            $userExist = Users::where('role',$role->role_number)->first();
            if (!$userExist) {
                $role->delete();
                DB::commit();
                return redirect()->back()->with('success', 'Role deleted successfully!');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Terdapat user dengan role ini.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
