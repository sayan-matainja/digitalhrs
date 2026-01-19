<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Requests\User\UserLoginRequest;
use App\Services\Auth\AuthService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Helpers\AppHelper;

class AuthApiController
{
    private AuthService $authService;
    private UserRepository $userRepo;

    public function __construct(AuthService $authService, UserRepository $userRepo)
    {
        $this->authService = $authService;
        $this->userRepo = $userRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            $data = $this->authService->checkCredential($validatedData);
            $user = $data['user'];
            $credentials = array(
                $data['credential']['login_type'] => $validatedData['username'],
                'password' => $validatedData['password']
            );

            if (!$this->getAttempt($credentials)) {
                throw new Exception(__('index.invalid_login_credentials'), 401);
            }


            $tokens = $user->createToken('MyToken' . $user->id)->accessToken;
            $validatedData['id'] = $user->id;
            $this->authService->updateUserLoginDetail($validatedData);
            DB::commit();
            return AppHelper::sendSuccessResponse(
                __('index.authenticated'),
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'workspace_type' => $user->workspace_type,
                        'avatar' => ($user->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$user->avatar) : asset('assets/images/img.png'),
                    ],
                    'tokens' => $tokens
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                return AppHelper::sendErrorResponse($e->getMessage(), 422, $e->errors());
            }
            if ($e instanceof GuzzleException) {
                return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
            }
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    protected function getAttempt(array $credentials): bool
    {
        return auth()->attempt($credentials);
    }

    public function logout()
    {
        try {
            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            if(!$userDetail){
                throw new Exception(__('index.user_detail_not_found'),401);
            }

            $isAuthorizeLogin = AppHelper::isAuthorizeLogin();



            DB::beginTransaction();
                $userToken = $this->getToken();
                $update['logout_status'] = User::LOGOUT_STATUS[ $isAuthorizeLogin ? 'pending' : 'approve'];
                $update['uuid'] = null;
                $update['fcm_token'] = null;
                $this->userRepo->update($userDetail,$update);
                $userToken->revoke();
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.partial_logout_successful'));
        } catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    protected function getToken()
    {
        return Auth::user()->token();
    }


}



