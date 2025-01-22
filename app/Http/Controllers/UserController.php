<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'name' => 'John Doe',
            'email' => 'example@emaple.com',
            'created_at' => now(),
        ]);
    }

    public function show($id){
        $user = User::find($id);

        if($user){
            return response()->json($user);
        }else{
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $user = User::create($request->all());

        dd($user);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id){
        $user = User::find($id);

        if($user){
            $user->update($request->all());
            return response()->json($user);
        }else{
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function destroy($id){
        $user = User::find($id);

        if($user){
            $user->delete();
            return response()->json([
                'message' => 'User deleted'
            ]);
        }else{
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }
}
