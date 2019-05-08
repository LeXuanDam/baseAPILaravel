<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 4/19/2019
 * Time: 11:50
 */

namespace App\Helper;
use \Firebase\JWT\JWT;

class JONWebToken
{
    const KEY = 'asiod12312oASJO23OO412jasofij';
    public static function encode($token){
        $token->time = time() + 86400;
        return JWT::encode($token, self::KEY);
    }
    public static function decode($token){
        return JWT::decode($token, self::KEY , array('HS256'));
    }
    public static function user($key = null)
    {
        try{
            $token = static::decode(static::getToken());
            return $token;
        }
        catch (\Exception $e){
            return null;
        }
    }
    public static function getToken()
    {
        return request()->headers->get('Access-Token');
    }
}
