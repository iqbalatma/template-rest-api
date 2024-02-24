<?php

namespace App\Services\V1\Management;

use App\Contracts\Abstracts\Services\BaseService;
use App\Exceptions\ForbiddenActionException;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Iqbalatma\LaravelServiceRepo\Exceptions\EmptyDataException;

/**
 * @method  Role getServiceEntity()
 */
class RoleService extends BaseService
{
    protected $repository;
    protected Role $role;
    protected array $roleBeforeUpdate;

    public function __construct()
    {
        parent::__construct();
        $this->repository = new RoleRepository();
    }

    /**
     * @return Collection
     */
    public function getAllData(): Collection
    {
        return RoleRepository::getAllData();
    }

    /**
     * @param string $id
     * @return Role
     * @throws EmptyDataException
     */
    public function getDataBy(string $id): Role
    {
        $this->checkData($id);
        return $this->getServiceEntity();
    }

    /**
     * @param array $requestedData
     * @return Role
     */
    public function addNewData(array $requestedData): Role
    {
        $requestedData["is_mutable"] = true;
        DB::beginTransaction();
        /** @var Role $role */
        $this->role = RoleRepository::addNewData($requestedData);
        $this->syncRolePermission($requestedData, $this->role)
            ->addNewDataAudit(); #process audit
        DB::commit();

        return $this->role;
    }


    /**
     * @param string $id
     * @param array $requestedData
     * @return Role
     * @throws EmptyDataException|ForbiddenActionException
     */
    public function updateDataById(string $id, array $requestedData): Role
    {
        $this->checkData($id);
        DB::beginTransaction();
        $this->role = $this->getServiceEntity();
        $this->roleBeforeUpdate = $this->role->toArray();

        if ($this->role->name === \App\Enums\Role::SUPERADMIN->value){
            throw new ForbiddenActionException("Role {$this->role->name} cannot be updated");
        }

        if ($this->role->is_mutable) {
            $this->role->fill($requestedData)->save();
        }

        $this->syncRolePermission($requestedData, $this->role)
            ->updateDataByIdAudit();
        DB::commit();

        return $this->role;
    }

    /**
     * @param string $id
     * @return int
     * @throws EmptyDataException|ForbiddenActionException
     */
    public function deleteDataById(string $id): int
    {
        $this->checkData($id);
        $role = $this->getServiceEntity();

        if ($role->name === \App\Enums\Role::SUPERADMIN->value || !$role->is_mutable){
            throw new ForbiddenActionException("Role " . $role->name . " cannot be deleted");
        }

        return $role->delete();
    }


    /**
     * @param array $requestedData
     * @param Role $role
     * @return RoleService
     */
    private function syncRolePermission(array &$requestedData, Role $role): self
    {
        if (isset($requestedData["permission_ids"])) {
            $role->permissions()->sync($requestedData["permission_ids"]);
        }

        return $this;
    }


    /**
     * @return void
     */
    private function addNewDataAudit():void
    {
        $this->auditService->setAction("ADD_NEW_DATA_ROLE")
            ->setMessage("Add single data role")
            ->setObject($this->role)
            ->log(
                ["role" => null],
                ["role" => $this->role],
            );
    }


    /**
     * @return void
     */
    private function updateDataByIdAudit():void
    {
        $this->auditService->setAction("UPDATE_DATA_ROLE")
            ->setMessage("Update single data role")
            ->setObject($this->role)
            ->log(
                ["role" => array_intersect_key($this->roleBeforeUpdate, $this->role->getChanges())],
                ["role" => $this->role->getChanges()],
            );
    }
}
