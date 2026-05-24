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

    public function getByUserId($userId)
    {
        $this->db->dbquery(
            'SELECT suppliers.supplier_id,
                    suppliers.user_id,
                    suppliers.shop_name,
                    suppliers.description,
                    suppliers.status,
                    suppliers.agreement_accepted,
                    suppliers.agreement_accepted_at,
                    suppliers.agreement_version,
                    suppliers.payment_status,
                    suppliers.is_available,
                    (
                        SELECT services.name
                        FROM services
                        WHERE services.supplier_id = suppliers.supplier_id
                        ORDER BY services.id ASC
                        LIMIT 1
                    ) AS service_name
             FROM suppliers
             WHERE suppliers.user_id = :user_id
             LIMIT 1'
        );
        $this->db->dbbind(':user_id', (int)$userId);

        return $this->db->getsingledata();
    }

    public function getCategories()
    {
        $this->db->dbquery('SELECT id, name FROM categories ORDER BY name ASC');

        return $this->db->getmultidata();
    }

    private function getSupplierByUserId($userId)
    {
        $this->db->dbquery('SELECT supplier_id FROM suppliers WHERE user_id = :user_id LIMIT 1');
        $this->db->dbbind(':user_id', $userId);

        return $this->db->getsingledata();
    }

    private function saveInitialService($supplierId, $data)
    {
        if (empty($data['service_name'])) {
            return true;
        }

        $this->db->dbquery('SELECT id FROM services WHERE supplier_id = :supplier_id AND name = :name LIMIT 1');
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':name', $data['service_name']);
        $existingService = $this->db->getsingledata();

        if ($existingService) {
            $this->db->dbquery(
                'UPDATE services
                 SET category_id = :category_id,
                     description = :description,
                     price = :price,
                     thumbnail_url = :thumbnail_url,
                     is_active = :is_active
                 WHERE id = :id'
            );
            $this->db->dbbind(':id', (int)$existingService['id']);
            $this->db->dbbind(':category_id', $data['category_id']);
            $this->db->dbbind(':description', $data['service_description']);
            $this->db->dbbind(':price', $data['service_price']);
            $this->db->dbbind(':thumbnail_url', $data['thumbnail_url']);
            $this->db->dbbind(':is_active', 0);

            return $this->db->dbexecute() ? (int)$existingService['id'] : false;
        }

        $this->db->dbquery(
            'INSERT INTO services(supplier_id, category_id, name, description, price, thumbnail_url, is_active)
             VALUES(:supplier_id, :category_id, :name, :description, :price, :thumbnail_url, :is_active)'
        );
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':category_id', $data['category_id']);
        $this->db->dbbind(':name', $data['service_name']);
        $this->db->dbbind(':description', $data['service_description']);
        $this->db->dbbind(':price', $data['service_price']);
        $this->db->dbbind(':thumbnail_url', $data['thumbnail_url']);
        $this->db->dbbind(':is_active', 0);

        if (!$this->db->dbexecute()) {
            return false;
        }

        return (int)$this->db->lastinsertid();
    }

    public function updateServiceThumbnail($serviceId, $thumbnailUrl)
    {
        $this->db->dbquery('UPDATE services SET thumbnail_url = :thumbnail_url WHERE id = :id');
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':thumbnail_url', $thumbnailUrl);

        return $this->db->dbexecute();
    }

    public function addServiceMedia($serviceId, $fileUrl, $type = 'image')
    {
        $this->db->dbquery(
            'INSERT INTO service_media(service_id, file_url, type)
             VALUES(:service_id, :file_url, :type)'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':file_url', $fileUrl);
        $this->db->dbbind(':type', $type);

        return $this->db->dbexecute();
    }

    private function categoryExists($categoryId)
    {
        if (empty($categoryId)) {
            return false;
        }

        $this->db->dbquery('SELECT id FROM categories WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$categoryId);
        $category = $this->db->getsingledata();

        return !empty($category);
    }

    public function isValidCategory($categoryId)
    {
        return $this->categoryExists($categoryId);
    }

    public function save($data)
    {
        $userId = $this->getUserId($data);

        if (!$userId || !$this->categoryExists($data['category_id'])) {
            return false;
        }

        $this->db->dbquery('UPDATE users SET phone = :phone, address = :address WHERE user_id = :user_id');
        $this->db->dbbind(':phone', $data['phone']);
        $this->db->dbbind(':address', $data['business_address']);
        $this->db->dbbind(':user_id', $userId);
        $this->db->dbexecute();

        $supplier = $this->getSupplierByUserId($userId);

        if ($supplier) {
            $supplierId = (int)$supplier['supplier_id'];
            $this->db->dbquery(
                'UPDATE suppliers
                 SET shop_name = :shop_name,
                     description = :description,
                     verify_url = :verify_url,
                     status = :status,
                     agreement_accepted = :agreement_accepted,
                     agreement_accepted_at = :agreement_accepted_at,
                     agreement_version = :agreement_version,
                     payment_status = :payment_status,
                     is_available = :is_available
                 WHERE supplier_id = :supplier_id'
            );
            $this->db->dbbind(':supplier_id', $supplierId);
        } else {
            $this->db->dbquery(
                'INSERT INTO suppliers(
                    user_id,
                    shop_name,
                    description,
                    verify_url,
                    status,
                    agreement_accepted,
                    agreement_accepted_at,
                    agreement_version,
                    payment_status,
                    is_available
                 )
                 VALUES(
                    :user_id,
                    :shop_name,
                    :description,
                    :verify_url,
                    :status,
                    :agreement_accepted,
                    :agreement_accepted_at,
                    :agreement_version,
                    :payment_status,
                    :is_available
                 )'
            );
            $this->db->dbbind(':user_id', $userId);
        }

        $this->db->dbbind(':shop_name', $data['business_name']);
        $this->db->dbbind(':description', $data['business_description']);
        $this->db->dbbind(':verify_url', $data['business_url']);
        $this->db->dbbind(':status', 'pending');
        $this->db->dbbind(':agreement_accepted', !empty($data['agreement_accepted']) ? 1 : 0);
        $this->db->dbbind(':agreement_accepted_at', $data['agreement_accepted_at']);
        $this->db->dbbind(':agreement_version', $data['agreement_version']);
        $this->db->dbbind(':payment_status', 'unpaid');
        $this->db->dbbind(':is_available', 0);

        if (!$this->db->dbexecute()) {
            return false;
        }

        if (empty($supplierId)) {
            $supplierId = (int)$this->db->lastinsertid();
        }

        $serviceId = $this->saveInitialService($supplierId, $data);

        if (!$serviceId) {
            return false;
        }

        return [
            'supplier_id' => $supplierId,
            'service_id' => $serviceId,
        ];
    }
}
