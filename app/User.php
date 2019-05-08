<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    const KEY_HASH = "hataraclub@hash_2019";
    protected $table = 'users';
    protected $hidden = ['password','created_at','deleted_at','updated_at'];

    public static function insertUser($certificateImg, $params)
    {
        $user = new User();
        $user->company = $params['company'];
        $user->user_name = $params['user_name'];
        $user->password = $params['password'];
        $user->represent = $params['represent'];
        $user->email = $params['email'];
        $user->tel = $params['tel'];
        $user->postcode = $params['postcode'];
        $user->address = $params['address'];
        $user->address_number = $params['address_number'];
        $user->confirm = $params['confirm'];
        $user->certificate_image = $certificateImg;
        $user->save();
        return $user;
    }

    public static function updateUser($certificateImg, $params,$token)
    {
        $update_data = [
            'company' => $params['company'],
            'represent' => $params['represent'],
            'user_funds' => $params['user_funds'],
            'number_of_staff' => $params['number_of_staff'],
            'business_type' => $params['business_type'],
            'email' => $params['email'],
            'tel' => $params['tel'],
            'postcode' => $params['postcode'],
            'address' => $params['address'],
            'address_number' => $params['address_number'],
            'certificate_image' => $certificateImg,
        ];
        if(isset($params['password'])){
            $update_data['password'] = static::getHashPassword($token->user_name,$params['password']);
        }
        User::where('id',$token->id)->update($update_data);
    }
    public static function getHashPassword($username, $password)
    {
        return hash('sha512', $username . $password . self::KEY_HASH);
    }

    public static function passwordVerify($params)
    {
        return hash('sha512', $params['user_name'] . $params['password'] . self::KEY_HASH);
    }
}
