<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Services\Nfc\NfcService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;

class NfcApiController extends Controller
{

    use CustomAuthorizesRequests;
    public function __construct(public NfcService $nfcService)
    {}

    /**
     * @throws AuthorizationException
     */
    public function save(Request $request): JsonResponse
    {
        $this->authorize('create_nfc');

        try {

            $validator = Validator::make($request->all(), [
                'title' => ['nullable', 'string'],
                'identifier' => ['required', 'string'],

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->toArray()
                ]);
            }

            $validatedData = $validator->validated();

            $userNfc = $this->nfcService->verifyNfc($validatedData['identifier']);

            if($userNfc){
                throw new Exception(__('index.nfc_token_already_exist'), 400);
            }

            DB::beginTransaction();

            $this->nfcService->saveNfcDetail($validatedData);

            DB::commit();

            return AppHelper::sendSuccessResponse(__('index.nfc_added_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
