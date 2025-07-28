<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;    
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\NewAccessToken;

class UserService{
private $userRepository;

public function __construct(UserRepository $userRepository)
{
    $this->userRepository = $userRepository;
}

public function register(array $data):array{
if($this->userRepository->emailAlreadyExists($data['email'])){
    throw new \Exception('Email already exists',409);
}

    $user= $this->userRepository->create($data);
    $token=$this->createAuthToken($user);
    return [
        // 'user'=>$user,
        // 'token'=>$token->plainTextToken
        'data'=>[
            'user'=>$user,
            'token'=>$token->plainTextToken,
            'token_type'=>'Bearer',
            'expires_at'=>30*24*60*60  //30 days
        ]
    ];
}

public function login(array $credentials):array{
    if(!Auth::attempt($credentials)){
        throw new \Exception('Invalid credentials',401);
    }

    $user=$this->userRepository->findUserByEmail($credentials['email']);
    $token=$this->createAuthToken($user);
    return [
        'data' => [
            'user' => $user,
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer', 
            'expires_in' => 30 * 24 * 60 * 60
        ]
    ];
}

private function createAuthToken($user){
    $user->tokens()->delete();  //remove previous tokens
    return $user->createToken('auth_token',[],now()->addDays(30));
}

}

?>