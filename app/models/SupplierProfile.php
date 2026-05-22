<?php

class SupplierProfile
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    private function getUserId($data)
    {
        if (!empty($data['user_id'])) {
            return (int)$data['user_id'];
        }

        $this->db->dbquery('SELECT user_id FROM users WHERE email = :email LIMIT 1');
        $this->db->dbbind(':email', $data['email']);
        $user = $this->db->getsingledata();

        return $user ? (int)$user['user_id'] : null;
    }

    private function getSupplierByUserId($userId)
    {
        $this->db->dbquery('SELECT supplier_id FROM suppliers WHERE user_id = :user_id LIMIT 1');
        $this->db->dbbind(':user_id', $userId);

        return $this->db->getsingledata();
    }

    private function saveInitialService($supplierId, $data)
    {
        if (empty($data['service_category'])) {
            return true;
        }

        $this->db->dbquery('SELECT id FROM services WHERE supplier_id = :supplier_id AND name = :name LIMIT 1');
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':name', $data['service_category']);
        $existingService = $this->db->getsingledata();

        if ($existingService) {
            return true;
        }

        $this->db->dbquery(
            'INSERT INTO services(supplier_id, name, description, is_active)
             VALUES(:supplier_id, :name, :description, :is_active)'
        );
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':name', $data['service_category']);
        $this->db->dbbind(':description', $data['description']);
        $this->db->dbbind(':is_active', 0);

        return $this->db->dbexecute();
    }

    public function save($data)
    {
        $userId = $this->getUserId($data);

        if (!$userId) {
            return false;
        }

        $this->db->dbquery('UPDATE users SET phone = :phone, address = :address WHERE user_id = :user_id');
        $this->db->dbbind(':phone', $data['phone']);
        $this->db->dbbind(':address', $data['location']);
        $this->db->dbbind(':user_id', $userId);
        $this->db->dbexecute();

        $supplier = $this->getSupplierByUserId($userId);

        if ($supplier) {
            $supplierId = (int)$supplier['supplier_id'];
            $this->db->dbquery(
                'UPDATE suppliers
                 SET shop_name = :shop_name, description = :description, status = :status, is_available = :is_available
                 WHERE supplier_id = :supplier_id'
            );
            $this->db->dbbind(':supplier_id', $supplierId);
        } else {
            $this->db->dbquery(
                'INSERT INTO suppliers(user_id, shop_name, description, status, is_available)
                 VALUES(:user_id, :shop_name, :description, :status, :is_available)'
            );
            $this->db->dbbind(':user_id', $userId);
        }

        $this->db->dbbind(':shop_name', $data['business_name']);
        $this->db->dbbind(':description', $data['description']);
        $this->db->dbbind(':status', 'pending');
        $this->db->dbbind(':is_available', 0);

        if (!$this->db->dbexecute()) {
            return false;
        }

        if (empty($supplierId)) {
            $supplierId = (int)$this->db->lastinsertid();
        }

        return $this->saveInitialService($supplierId, $data);
    }
}
