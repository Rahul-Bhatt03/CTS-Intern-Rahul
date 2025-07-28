<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository{
    public function create(array $data):User{
        $data['password'] = Hash::make($data['password']);
          $data['role']=$data['role']??'employee';
        return User::create($data);
    }

    public function findUserByEmail(string $email):?User{
return $user=User::where('email',$email)->first();

    }

    public function emailAlreadyExists(string $email):bool{
        return User::where('email',$email)->exists();
    }
}

?>