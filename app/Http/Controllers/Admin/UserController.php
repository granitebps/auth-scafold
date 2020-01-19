<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use DB;
use Hash;
use Illuminate\Http\Request;
use Session;

class UserController extends Controller
{
    /**
     * Get All Users
     *
     * @return Collection 
     */
    public function index()
    {
        $users = User::whereNotIn('role', ['admin'])->paginate(10);
        $data['title'] = 'Users List';
        $data['users'] = $users;

        return view('admin.user.index')->with($data);
    }

    public function create()
    {
        $data['title'] = 'Create User';

        return view('admin.user.create')->with($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();
            notify()->success('User Created');
            return redirect()->route('user.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Session::flash('server_error', $th->getMessage());
            return redirect()->route('user.create')->withInput();
        }
    }

    public function edit($id)
    {
        $user = User::where('uuid', $id)->first();

        $data['title'] = 'Edit User';
        $data['user'] = $user;

        return view('admin.user.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|max:255|unique:users,username,' . $id . ',uuid',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id . ',uuid',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::where('uuid', $id)->first();

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
            ]);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            DB::commit();
            notify()->success('User Updated');
            return redirect()->route('user.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Session::flash('server_error', $th->getMessage());
            return redirect()->route('user.edit', ['user' => $id])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::where('uuid', $id)->first();

            $user->delete();

            notify()->success('User Deleted');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Session::flash('server_error', $th->getMessage());
        }
        return redirect()->route('user.index');
    }
}
