<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function signUp(Request $request){
        DB::beginTransaction();

        try{
            User::insert([
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "password" => Hash::make($request->input('password')),
            ]);

            $credentials = $request->only(['email', 'password']);
            if(! $token = auth()->attempt($credentials)) throw new \Exception('トークンの取得に失敗しました。') ;

            User::where('email', $request->input('email'))->update(['token' => 'Bearer '.$token]);

            DB::commit();
            
            return response()->json([ 
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth("api")->factory()->getTTL() * 60
                ], 200);    
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['errormessage' => $e ]);
        }
    }

    public function login(Request $request){
        try{
            $credentials = $request->only('email', 'password');

            if (! $token = auth()->attempt($credentials)) throw new \Exception('パスワード又はメールアドレスが間違っています');

            return response()->json([ 
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth("api")->factory()->getTTL() * 60
                ], 200);
        }catch(\Exception $e){
            return response()->json(['error' => $e], 500);        
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json(['message' => $request->input('success')]);
    }

    public function imgUpdate(Request $request){
        $imgPath = $request->file('icon')->store('public/img');

        User::where('token', $request->headers->get('Authorization'))
                ->update(['imgPath' => 'backend/storage/app/'.$imgPath]);
        
        return response()->json(['imgPath' => "backend/storage/app/".$imgPath]);
    }

    public function getUser(Request $request){
        $user = User::where('token', $request->headers->get('Authorization'))->first();

        return response()->json([ 
                    'name' => $user->name,
                    'imgPath' => $user->imgPath,
                ], 200);
    }

    public function editUser(Request $request){
        User::where('token', $request->headers->get('Authorization'))
                ->update(['name' => $request->input('name')]);
    }

}
