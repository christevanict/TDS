<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Company;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function index(){
        $users = Users::orderBy('id','asc')->get();
        $companies = Company::orderByDesc('company_code')->get();
        $roles = Role::all();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_user'];
        $depts = Department::orderBy('department_code', 'asc')->get();
        return view('master.users',compact(
            'users',
            'companies',
            'depts',
            'roles',
            'privileges',
        ));
    }
    public function insert(Request $request){
        DB::beginTransaction();
        try {
            if(Users::
                where('username',$request->username)
                ->orWhere('email',$request->email)
                ->count()<1){
                Users::create([
                    'username' => $request->username,
                    'fullname' => $request->fullname,
                    'department' => $request->department,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'status'=>1,
                    'email'=>$request->email,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);
                DB::commit();
                return redirect()->back()->with('success', 'User added successfully!');
            }else{
                return redirect()->back()->with('error', 'Username or email must not be same');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Please try again');
        }

    }
    public function update(Request $request, $id){
        DB::beginTransaction();
        try {
            if($request->username=='superadminICT'){
                return redirect()->back()->with('error', 'Cannot update this user');
            }
            if(Users::
                where('username',$request->username)
                ->orWhere('email',$request->email)
                ->count()<2){
                    $changePassword= $request->password?true:false;
                    $user  = Users::where('username',$id)->first();
                    $user->username = $request->username;
                    $user->fullname = $request->fullname;
                    if($changePassword){
                        $user->password = Hash::make($request->password);
                    }
                    $user->email = $request->email;
                    $user->role = $request->role;
                    $user->department = $request->department;
                    $user->save();
                    DB::commit();
                }else{
                    return redirect()->back()->with('error', 'Username or email must not be same');
                }

            return redirect()->back()->with('success', 'User edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Please try again');
        }
    }

    public function inactive($id){
        DB::beginTransaction();
        try {
            $user  = Users::where('username',$id);
            $user->status = $user->status==1?0:1;
            $user->save();
            DB::commit();
            return redirect()->back()->with('success', 'User edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Please try again');
        }
    }
    public function delete($id){
        DB::beginTransaction();
        try {
            $user  = Users::where('username',$id);
            $user->delete();
            DB::commit();
            return redirect()->back()->with('success', 'User edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Please try again');
        }
    }

}
