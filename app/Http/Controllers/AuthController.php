<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json('Email or Password is worng', 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_member' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'detail_alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required|email',
            'password' => 'required|same:konfirmasi_password',
            'konfirmasi_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                $validator->errors(), 422
            ]);
        }

        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        unset($input['konfirmasi_password']);
        $member = Member::create($input);

        return response()->json([
            'data' => $member
        ]);
    }

    public function login_member(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                $validator->errors(), 422
            ]);
        }

        // $email = $request->email;
        // $password = $request->password;

        // $credentials = $request->only('email', 'password');

        // dd($credentials);

        // $member = Member::where('email', $request->email)->first();
        // if ($member) {

        //     if (Hash::check($request->password, $member->password)) {
        //         $request->session()->regenerate();
        //         return response()->json([
        //             'message' => 'Success',
        //             'data' => $member
        //         ]);
        //     } else {
        //         return response()->json([
        //             'message' => 'Success',
        //             'data' => 'Password Salah'
        //         ]);
        //     }
        // } else {
        //     return response()->json([
        //         'message' => 'Success',
        //         'data' => 'Email Salah'
        //     ]);
        // }
        $member = Member::where('email', $request->email)->first();
        // if (Auth::attempt($credentials)) {
        if ($member) {
            // dd('member di temukan');
            if (Hash::check($request->password, $member->password)) {
                $request->session()->regenerate();
                // $member = Member::where('email', $request->email)->first();
                return response()->json([
                    'message' => 'Success',
                    'data' => $member
                ]);
            } else {
                return response()->json([
                    'message' => 'failed',
                    'data' => 'Password Salah'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'failed',
                'data' => 'Email Salah'
            ]);
        }
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function logout_member()
    {
        Session::flash();

        redirect('/login');
    }
}
