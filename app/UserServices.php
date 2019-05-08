<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserServices extends Model
{
    protected $table = 'user_services';
    protected $hidden = ['created_at','deleted_at','updated_at'];
    public static function insertUserServices($params, $user)
    {
        try {
            if (count($params['services']) > 0) {
                $insert = [];
                foreach ($params['services'] as $entry) {
                    $insert[] = [
                        'user_id' => $user->id,
                        'service_id' => $entry,
                        'status' => 2,
                        'created_at' => date('Y/m/d H:i:s',time())];
                }
                static::insert($insert);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
//            return $this->response->json(false, 'insert database error');
        }
        return true;
    }
}
