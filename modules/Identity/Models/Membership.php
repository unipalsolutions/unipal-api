<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Identity\Enums\CompanyRole;

class Membership extends Pivot
{
    protected $table = 'company_user';

    protected function casts(): array
    {
        return ['role' => CompanyRole::class];
    }
}
