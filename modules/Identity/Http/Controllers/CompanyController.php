<?php

namespace Modules\Identity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Identity\Http\Resources\CompanyResource;

class CompanyController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CompanyResource::collection($request->user()->accessibleCompanies());
    }
}
