<?php

namespace App\Contracts\Abstracts\Services;

use App\Exceptions\DeleteDataThatStillUsedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Iqbalatma\LaravelAudit\AuditService;
use Iqbalatma\LaravelServiceRepo\Exceptions\EmptyDataException;

class BaseService extends \Iqbalatma\LaravelServiceRepo\BaseService
{
    protected AuditService $auditService;

    public function __construct()
    {
        $this->auditService = AuditService::init();
    }
//
//    /**
//     * @return Collection|array
//     */
//    public function getAllData(): Collection|array
//    {
//        return $this->repository->getAllData();
//    }
}
