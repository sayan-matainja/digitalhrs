<?php

namespace App\Services\Client;

use App\Models\User;
use App\Repositories\ClientRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class ClientService
{
    private ClientRepository $clientRepo;

    public function __construct(ClientRepository $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    public function getAllClientsList($filterParameters,$select = ['*'], $with = [])
    {
        return $this->clientRepo->getAllClients($filterParameters,$select, $with);
    }

    public function getTopClientsOfCompany()
    {
        return $this->clientRepo->getTopClientsOfCompany();
    }

    public function getAllActiveClients($select = ['*'], $with = [])
    {
        return $this->clientRepo->getAllActiveClients($select, $with);
    }

    public function getClientByBranch($branchId,$select = ['*'])
    {
        return $this->clientRepo->getBranchClients($branchId, $select);
    }

    public function findClientDetailById($id, $select = ['*'], $with = [])
    {
        try {
            $clientDetail = $this->clientRepo->findClientDetailById($id, $select, $with);
            if (!$clientDetail) {
                throw new Exception(__('message.client_not_found'), 400);
            }
            return $clientDetail;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function saveClientDetail($validatedData)
    {
        if (isset(auth()->user()->branch_id)) {
            $validatedData['branch_id'] = auth()->user()->branch_id;
        }
        return $this->clientRepo->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateClientDetail($validatedData, $clientId): bool
    {

        $clientDetail = $this->findClientDetailById($clientId);

        if(is_null($clientDetail['branch_id']) && isset(auth()->user()->branch_id)){
            $validatedData['branch_id'] = auth()->user()->branch_id;
        }
        return $this->clientRepo->update($clientDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function toggleIsActiveStatus($id): bool
    {

        $clientDetail = $this->findClientDetailById($id);
        $this->clientRepo->toggleIsActiveStatus($clientDetail);

        return true;

    }

    /**
     * @throws Exception
     */
    public function deleteClientDetail($id): bool
    {

        $clientDetail = $this->findClientDetailById($id);

        $this->clientRepo->delete($clientDetail);

        return true;

    }


}

