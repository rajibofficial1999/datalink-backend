<?php

namespace App\Http\Controllers\Api;

use App\Enums\Sites;
use App\Enums\VideoCallingTypes;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetApiDataController extends Controller
{
    public function getSiteAndVideoCallingCredentials(string $site, string $videoCalling, string $user_access_token): JsonResponse
    {
        $siteType = Sites::findByName($site);
        $videoCallingType = VideoCallingTypes::findByValue($videoCalling);

        $siteType = $siteType ?? Sites::findByValue($site);
        $videoCallingType = $videoCallingType ?? Sites::findByValue($videoCalling);

        if($siteType){
            $siteTypeDetails = $siteType->details();
        }

        if($videoCallingType){
            $videoCallingTypeDetails = $videoCallingType->details();
        }

        $user = User::findUserByAccessToken($user_access_token);
        if (!$user) {
            return response()->json(['subscription_expired' => 'User access token is not valid.', 422]);
        }

        if(!$this->hasSubscription($user)) {
            return response()->json(['subscription_expired' => 'User subscription has expired.'], 403);
        }

        return response()->json([
            'site' => $siteTypeDetails ?? null,
            'videoCalling' => $videoCallingTypeDetails ?? null,
        ], Response::HTTP_OK);
    }

    protected function hasSubscription(User $user): bool
    {
        if(!$user->subscriptionDetails){
            return false;
        }

        if($user->subscriptionDetails['is_expired']){
            return false;
        }

        return true;
    }
}
