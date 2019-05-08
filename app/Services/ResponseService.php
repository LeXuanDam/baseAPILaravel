<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 5/7/2019
 * Time: 13:50
 */

namespace App\Services;


class ResponseService
{
    public function json($result, $message = null, $data = null)
    {
        return response()->json([
            'result' => $result,
            'message' => $message,
            'data' => $data
        ]);
    }
}
