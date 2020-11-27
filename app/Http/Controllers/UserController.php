<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\User\UserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * @var int
     */
    private  $status_code = 200;

    /**
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function signUp(UserRequest $request)
    {



        $user_status = User::where('email', $request->email)->first();
        if(!is_null($user_status)) {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! email already registered"]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'role' => $request->role,
        ]);

        if(!is_null($user)) {
            return response()->json(["status" => $this->status_code, "success" => true, "message" => "Registration completed successfully", "data" => $user]);
        }

        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "failed to register"]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logIn(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );

        if($validator->fails()) {
            return response()->json(["status" => "failed", "validation_error" => $validator->errors()]);
        }

        // check if entered email exists in db
        $email_status = User::where('email', $request->email)->first();

        // if email exists then we will check password for the same email

        if(!is_null($email_status)) {
            $password_status = User::where('email', $request->email)
                ->where('password', md5($request->password))
                ->first();

            // if password is correct
            if(!is_null($password_status)) {
                $user = $this->userDetail($request->email);

                return response()->json(['status' => $this->status_code, 'success' => true, 'message' => 'You have logged in successfully', "data" => $user]);
            }

            else {
                return response()->json(['status' => 'failed', 'success' => false, 'message' => 'Unable to login. Incorrect password.']);
            }
        }

        else {
            return response()->json(['status' => 'failed', 'success' => false, 'message' => 'Unable to login. Email doesnt exist.']);
        }
    }


    /**
     * @param $email
     * @return mixed
     */
    public function userDetail($email) {
        $user = array();
        if($email != '') {
            $user = User::where('email', $email)->first();
            return $user;
        }
    }
}
