<?php

namespace App\Http\Controllers;

use App\User;
use DB;
use Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = User::findOrFail(auth()->id());

        $data['title'] = 'Change Profile';
        $data['user'] = $user;

        return view('profile.edit')->with($data);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'requiredstring|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail(auth()->id());

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
            ]);

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
        return redirect()->route('profile.edit');
    }
}
