<?php

namespace App\Http\Controllers;

use App\User;
use DB;
use Hash;
use Illuminate\Http\Request;
use Session;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = User::where('uuid', auth()->user()->uuid)->first();

        $data['title'] = 'Change Profile';
        $data['user'] = $user;

        return view('profile.edit')->with($data);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username,' . auth()->user()->uuid . ',uuid',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->user()->uuid . ',uuid',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::where('uuid', auth()->user()->uuid)->first();

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
            notify()->success('Profile Updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            Session::flash('server_error', $th->getMessage());
        }
        return redirect()->route('profile.edit');
    }
}
