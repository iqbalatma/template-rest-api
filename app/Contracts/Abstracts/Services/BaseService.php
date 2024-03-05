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

    /**
     * @return Collection|array
     */
    public function getAllData(): Collection|array
    {
        return $this->repository->getAllData();
    }

    /**
     * @param string $id
     * @return Model
     * @throws EmptyDataException
     */
    public function getDataById(string $id): Model
    {
        $this->checkData($id);
        return $this->getServiceEntity();
    }

    /**
     * @param array $requestedData
     * @return Model
     */
    public function addNewData(array $requestedData): Model
    {
        return $this->repository->addNewData($requestedData);
    }

    /**
     * @param string $id
     * @param array $requestedData
     * @return Model
     * @throws EmptyDataException
     */
    public function updateDataById(string $id, array $requestedData): Model
    {
        $this->checkData($id);

        $entity = $this->getServiceEntity();

        $entity->fill($requestedData)->save();

        return $entity;
    }

    /**
     * @param string $id
     * @return int
     * @throws EmptyDataException|DeleteDataThatStillUsedException
     */
    public function deleteDataId(string $id): int
    {
        $this->checkData($id);
        $data = $this->getServiceEntity();

        if ($data->relationshipsCheckBeforeDelete) {
            foreach ($data->relationshipsCheckBeforeDelete as $relation) {
                if ($data->{$relation}()->exists()) {
                    throw new DeleteDataThatStillUsedException();
                }
            }
        }

        return $this->getServiceEntity()->delete();
    }
}
