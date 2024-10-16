<?php

namespace App\Services\WorkerService\LoginWorkerService;

use App\Models\Worker;
use Illuminate\Support\Facades\Validator;

class LoginWorkerService{

    protected $model;
    function __construct()
    {
        $this->model = new Worker();
    }

    function validation($request){
      $validator =   Validator::make($request->all(),$request->rules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        return $validator;
    }

    function isValidData($data){
        if (!$token = auth()->guard('worker')->attempt($data->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $token;
    }



    function getStatus($email){
        $worker = $this->model->where('email',$email)->first();
        $status = $worker->status;
        
        return $status;
    }

    function isVerified($email){
        $worker = $this->model->where('email',$email)->first();
        $verified_at = $worker->verified_at;

        return $verified_at;
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->guard('worker')->user()
        ]);
    }

    function login($request)
    {
        $data =  $this->validation($request);
        $token = $this->isValidData($data);
        if ($this->isVerified($request->email) == null) {
            return response()->json(["message" => "your account is not verified"], 422);
        } elseif ($this->getStatus($request->email) == 0) {
            return response()->json(["message" => "your account is pending"], 422);
        }
        return $this->createNewToken($token);
    }
    
}
