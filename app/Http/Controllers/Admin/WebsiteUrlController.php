<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Category;
use App\Enums\LoginUrlEndpoint;
use App\Enums\Sites;
use App\Enums\VideoCallingTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebsiteUrlStoreRequest;
use App\Models\Domain;
use App\Models\WebsiteUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WebsiteUrlController extends Controller
{

    protected array $websiteUrls;

    public function index($site = null, string $category = null): JsonResponse
    {
        $site = $site ?? Sites::EROS_ADS->value;

        $category = $category ?? Category::LOGIN->value;

        $authUser = request()->user();

        $userAvailableSites = [];
        if (!$authUser->isSuperAdmin) {
            $userPackageDetails = request()->user()->package->details();
            $userAvailableSites = $userPackageDetails['sites'];
        }

        $domainOwner = $authUser->isUser ? $authUser->team : $authUser;

        $domainsWithUrls = Domain::query()
            ->when($authUser->isAdmin || $authUser->isUser, function ($query) use ($domainOwner) {
                return $query->where('is_default', true)->orWhere('user_id', $domainOwner?->id);
            })
            ->with(['websiteUrls' => function ($query) use ($category, $site) {
                return $query->where('site', $site)->where('category', $category);
            }])
            ->get();


        return response()->json([
            'websiteUrls' => $domainsWithUrls,
            'sites' => Sites::cases(),
            'user' => $site
        ], Response::HTTP_OK);

        $websiteUrls = $domainsWithUrls->flatMap(function ($domain) use ($site, $userAvailableSites, $authUser) {
            return $domain->websiteUrls->map(function ($websiteUrl) use ($site, $userAvailableSites, $authUser) {

                if (!$authUser->isSuperAdmin) {
                    if (!in_array($site, $userAvailableSites)) {
                        $websiteUrl['url'] = null;
                    }
                }

                return $websiteUrl;
            });
        });

        return response()->json([
            'websiteUrls' => $websiteUrls,
            'sites' => Sites::cases(),
            'user' => $site
        ], Response::HTTP_OK);
    }

    public function store(WebsiteUrlStoreRequest $request): JsonResponse
    {
        Gate::authorize('create', WebsiteUrl::class);

        $data = $request->validated();

        $domain = Domain::findOrFail($data['domain']);

        $categoriesStatus = $this->prepareCategories($data['categories']);

        $sites = $data['sites'];

        foreach ($sites as $site) {
            if ($categoriesStatus[Category::LOGIN->value]) {
                $this->prepareLoginUrls(
                    Category::LOGIN->value,
                    $domain,
                    $site,
                );
            }

            if ($categoriesStatus[Category::VIDEO_CALLING->value]) {
                $this->prepareVideoUrls(
                    Category::VIDEO_CALLING->value,
                    $domain,
                    $site,
                );
            }
        }

        $urlCollection = collect($this->websiteUrls);

        $websiteUrls = WebsiteUrl::whereDomainId($domain->id)->pluck('url');

        $filterUrlItems = $urlCollection->whereNotIn('url', $websiteUrls);

        if (count($filterUrlItems) === 0) {
            throw ValidationException::withMessages(['domain' => 'All URLs have already created for selected site and domain.']);
        }

        $domain->websiteUrls()->createMany($filterUrlItems->toArray());

        return response()->json(['success' => "Website URLs has created successfully."], Response::HTTP_CREATED);
    }

    protected function prepareLoginUrls(string $category, Domain $domain, string $site): void
    {
        foreach (LoginUrlEndpoint::cases() as $case) {

            $this->websiteUrls[] = [
                'category' => $category,
                'category_type' => $category,
                'site' => $site,
                'domain' => $domain->name,
                'url' => "https://{$domain->name}/{$case->value}",
            ];
        }
    }

    protected function prepareVideoUrls(string $category, Domain $domain, string $site): void
    {
        foreach (VideoCallingTypes::cases() as $case) {

            $siteType = Sites::findByValue($site);
            $details = $siteType->details();

            $this->websiteUrls[] = [
                'category' => $category,
                'category_type' =>  $case->value,
                'site' => $site,
                'domain' => $domain->name,
                'url' => "https://{$domain->name}/{$details['name']}/invite/{$case->value}",
            ];
        }
    }

    protected function prepareCategories(array $categories): array
    {
        $pageItems = [
            Category::LOGIN->value => false,
            Category::VIDEO_CALLING->value => false,
        ];

        foreach ($categories as $category) {
            if (Arr::has($pageItems, $category)) {
                $pageItems[$category] = true;
            }
        }

        return $pageItems;
    }
}
