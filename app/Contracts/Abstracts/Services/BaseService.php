<?php

namespace App\Contracts\Abstracts\Services;

use Iqbalatma\LaravelAudit\AuditService;

class BaseService extends \Iqbalatma\LaravelServiceRepo\BaseService
{
    protected AuditService $auditService;

    public function __construct()
    {
        $this->auditService = AuditService::init();
    }
}
