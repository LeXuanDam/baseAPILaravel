<?php

namespace App\Http\Controllers;

use App\User;
use App\UserServices;
use Illuminate\Http\Request;
use App\Helper\JONWebToken as JWT;
use Validator;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $response;

    function __construct(ResponseService $response)
    {
        $this->response = $response;
    }

    /**
     * @OA\Post(
     *   path="/api/register",
     *   tags={"User"},
     *   summary="Register",
     *   operationId="register",
     *   @OA\Parameter(
     *     name="company",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *     @OA\Parameter(
     *     name="represent",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="email",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="tel",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="address",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *     @OA\Parameter(
     *     name="address_number",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *    @OA\Parameter(
     *     name="postcode",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *     @OA\Parameter(
     *     name="certificate_image",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="file",
     *     ),
     *   ),
     *    @OA\Parameter(
     *     name="services",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *     )
     *   )
     * )
     */

    public function register(Request $request)
    {
        $params = $request->all();
        $validate = $this->validateRegister($request);
        if($validate) return $validate;
        $params['email'] = isset($params['email']) ? $params['email'] : null;
        $user = User::where('user_name', $params['tel'])->orWhere('email', $params['email'])->exists();
        if ($user) {
            return $this->response->json(false, 'User exists');
        }
        $params['services'] = isset($params['services']) ? $params['services'] : [];
        $params['user_name'] = $params['tel'];
        $params['password'] = User::getHashPassword($params['user_name'], $params['tel']);
        $params['confirm'] = isset($params['confirm']) ? $params['confirm'] : 0;
        DB::beginTransaction();
        $certificateImg = $this->uploadFile($request);
        $user = User::insertUser($certificateImg, $params);
        UserServices::insertUserServices($params, $user);
        return $this->response->json(true, 'Register success', [
            'access_token' => JWT::encode($user)
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/login",
     *   tags={"User"},
     *   summary="Login",
     *   operationId="login",
     *   @OA\Parameter(
     *     name="user_name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *      format="password",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *     )
     *   )
     * )
     */

    public function login(Request $request)
    {
        $params = $request->all();
        $validate = $this->validateLogin($request);
        if($validate) return $validate;
        $user = User::select('id', 'user_name', 'tel', 'company', 'postcode')
            ->where('user_name', $params['user_name'])
            ->first();
        if (!$user) {
            return $this->response->json(false, 'user not exists');
        }
        if (!User::passwordVerify($params)) {
            return $this->response->json(false, 'password incorrect');
        }
        return $this->response->json(true, 'Login success', [
            'access_token' => JWT::encode($user)
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/profile",
     *   tags={"User"},
     *   summary="Profile",
     *   operationId="profile",
     *   @OA\Parameter(
     *     name="company",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *     @OA\Parameter(
     *     name="represent",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="email",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="tel",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="address",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *     @OA\Parameter(
     *     name="address_number",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *    @OA\Parameter(
     *     name="postcode",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *     @OA\Parameter(
     *     name="certificate_image",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="file",
     *     ),
     *   ),
     *    @OA\Parameter(
     *     name="user_funds",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *    @OA\Parameter(
     *     name="number_of_staff",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *    @OA\Parameter(
     *     name="business_type",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *     )
     *   )
     * )
     */

    public function profile(Request $request){
        $params = $request->all();
        $validate = $this->validateRegister($request);
        if($validate) return $validate;
        $token = JWT::user();
        $certificateImg = $this->uploadFile($request);
        User::updateUser($certificateImg, $request, $token);
        return $this->response->json(true, 'update profile success');
    }

    protected function uploadFile($request)
    {
        if ($request->hasFile('certificate_image')) {
            $path = Storage::putFile('certificateImg', $request->file('certificate_image'));
            return $path;
        }
        return null;
    }

    private function validateRegister($request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'represent' => 'required',
            'tel' => 'required',
            'postcode' => 'required',
            'address' => 'required',
            'address_number' => 'required',
            'email' => 'email|max:45',
            'certificate_image' => 'mimes:jpeg,bmp,png,jpg,gif'

        ], [
            'user_name.required' => 'The user name field is required.',
            'represent.required' => 'The represent field is required.',
            'tel.required' => 'The tel field is required.',
            'postcode.required' => 'The postcode field is required.',
            'address.required' => 'The address field is required.',
            'address_number.required' => 'The address_number field is required.',
            'email.email' => 'The email field is required.',
            'email.max' => 'The email max 45 character.',
            'certificate_image.mimes' => 'The email field is required.',
        ]);
        if ($validator->fails()) {
            return $this->response(false, $validator->errors()->toArray());
        }
    }

    private function validateLogin($request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required',
        ], [
            'user_name.required' => 'The user name field is required.',
            'password.required' => 'The password field is required.',
        ]);
        if ($validator->fails()) {
            return $this->response->json(false, $validator->errors()->toArray());
        }
    }
}
