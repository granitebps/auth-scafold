<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use DB;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::whereNotIn('role', ['admin'])->paginate(10);
        $data['title'] = 'Users List';
        $data['users'] = $users;

        return view('admin.user.index')->with($data);
    }

    public function create()
    {
        $data['title'] = 'Users List';

        return view('admin.user.create')->with($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'requiredstring|max:255',
            'username' => 'required|string|max:255|unique:users',
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
            return redirect()->route('user.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('user.create')->withInput();
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $data['title'] = 'Users List';
        $data['users'] = $user;

        return view('admin.user.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'requiredstring|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();
            return redirect()->route('user.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('user.edit', ['user' => $id])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $user->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
        return redirect()->route('user.index');
    }
}
