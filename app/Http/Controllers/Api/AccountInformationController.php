<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\AccountManage;
use App\Models\AccountInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Enums\Sites;

class AccountInformationController extends Controller
{
    public function __construct(protected AccountManage $accountService) {}

    public function store(Request $request): JsonResponse
    {
        $data = $this->accountService->create($request->all());

        if($data['success']){
            $account = $data['account'];
            $site = Sites::findByValue($account['site']);
            $site = $site ?? Sites::findByName($account['site']);
            $data['site_details'] = $site->details();
        }

        return response()->json($data, $data['status_code']);
    }

    public function show(string $access_token): JsonResponse
    {
        $account = AccountInformation::findByAccessToken($access_token);

        return response()->json($account, Response::HTTP_OK);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $this->accountService->update($request);

        return response()->json($data, $data['status_code']);
    }
}
