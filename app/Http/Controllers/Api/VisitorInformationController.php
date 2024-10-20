<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\VisitorInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;
use App\Enums\Sites;
use App\Enums\VideoCallingTypes;

class VisitorInformationController extends Controller
{
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), $this->formRules());

        if ($validator->fails()) {
            return $this->throwErrors($validator->errors());
        }

        $requestData = $request->all();

        $user = User::findUserByAccessToken($requestData['user_access_token']);
        if (!$user) {
            return $this->addErrorAndThrow($validator, 'user_access_token', 'User access token is not valid.');
        }

        $site = Sites::findByValue($requestData['site']);
        $site = $site ?? Sites::findByName($requestData['site']);

        if (!$site) {
            return $this->addErrorAndThrow($validator, 'site', 'Site name is not valid.');
        }

        if (!$user->isSuperAdmin) {
            if (!$this->hasSubscription($user)) {
                return $this->addErrorAndThrow($validator, 'subscription_expired', 'User subscription has expired.', 403);
            }

            $userPackageDetails = $user->package->details();
            $userAvailableSites = $userPackageDetails['sites'];

            $response = [
                'success' => false,
                'site' => $userPackageDetails
            ];

            return response()->json($response, Response::HTTP_OK);

            if (!in_array($site, $userAvailableSites)) {
                return $this->addErrorAndThrow($validator, 'site', 'Site name is not valid.', 403);
            }
        }

        if (isset($requestData['video_calling_type'])) {
            $videoCallingType = VideoCallingTypes::findByValue($requestData['video_calling_type']);
            $videoCallingType = $videoCallingType ?? Sites::findByValue($requestData['video_calling_type']);

            if (!$videoCallingType) {
                return $this->addErrorAndThrow($validator, 'video_calling_type', 'video_calling_type name is not valid.');
            }
        }

        $data = null;
        if (filter_var($requestData['ip_address'], FILTER_VALIDATE_IP)) {
            $data = Location::get($requestData['ip_address']);
        }

        if ($data) {
            $data = $data->toArray();

            $data = $this->prepareData($user, $requestData['site'], $requestData['user_agent'], $data);

            $subValidator = Validator::make($data, $this->subRules());

            if ($subValidator->fails()) {
                return $this->throwErrors($validator->errors());
            }

            $visitorInfo = VisitorInformation::create($data);

            $response = [
                'success' => true,
                'visitor_information' => $visitorInfo
            ];

            if (isset($requestData['video_calling_type'])) {
                $response['video_calling_details'] = $videoCallingType->details();
                $response['site_details'] = $site->details();
            }

            return response()->json($response, Response::HTTP_OK);
        }

        return response()->json('', Response::HTTP_OK);
    }

    protected function formRules(): array
    {
        return [
            'user_access_token' => 'required|string',
            'site' => 'required|string|max:255',
            'ip_address' => 'required|string|max:255',
            'user_agent' => 'required|string',
        ];
    }

    protected function subRules(): array
    {
        return [
            'country' => 'required|max:255',
            'city' => 'nullable|max:255',
            'state_name' => 'nullable|max:255',
            'zip_code' => 'nullable|max:255',
        ];
    }

    protected function addErrorAndThrow($validator, string $key, string $message, int $status_code = 422): JsonResponse
    {
        $validator->errors()->add($key, $message);
        return $this->throwErrors($validator->errors(), $status_code);
    }

    protected function throwErrors($errors, int $status_code = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $status_code);
    }

    protected function prepareData(User $user, string $site, string $user_agent, array $data): array
    {
        $data['user_id'] = $user->id;
        $data['site'] = $site;
        $data['ip_address'] = $data['ip'];
        $data['country'] = $data['countryName'];
        $data['city'] = $data['cityName'];
        $data['state_name'] = $data['regionName'];
        $data['zip_code'] = $data['zipCode'];
        $data['user_agent'] = $user_agent;

        return $data;
    }

    protected function hasSubscription(User $user): bool
    {
        if (!$user->subscriptionDetails) {
            return false;
        }

        if ($user->subscriptionDetails['is_expired']) {
            return false;
        }

        return true;
    }
}
