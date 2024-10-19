<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Domain;

class SiteController extends Controller
{
    public function index(Domain $domain)
    {
        return $domain->websiteUrls()->pluck('site');
    }
}
