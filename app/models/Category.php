<?php

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAll()
    {
        $this->db->dbquery('SELECT id, name, slug, created_at FROM categories ORDER BY name ASC');
        return $this->db->getmultidata();
    }

    public function getById($id)
    {
        $this->db->dbquery('SELECT id, name, slug, created_at FROM categories WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$id);
        return $this->db->getsingledata();
    }

    public function create($name, $slug)
    {
        $this->db->dbquery('INSERT INTO categories (name, slug) VALUES (:name, :slug)');
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':slug', $slug);
        $this->db->dbexecute();

        return (int)$this->db->lastInsertId();
    }

    public function update($id, $name, $slug)
    {
        $this->db->dbquery('UPDATE categories SET name = :name, slug = :slug WHERE id = :id');
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':slug', $slug);
        $this->db->dbbind(':id', (int)$id);
        $this->db->dbexecute();

        return true;
    }

    public function delete($id)
    {
        $this->db->dbquery('DELETE FROM categories WHERE id = :id');
        $this->db->dbbind(':id', (int)$id);
        $this->db->dbexecute();

        return true;
    }

    public function nameExists($name, $excludeId = null)
    {
        if ($excludeId !== null) {
            $this->db->dbquery('SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) AND id != :excludeId LIMIT 1');
            $this->db->dbbind(':name', $name);
            $this->db->dbbind(':excludeId', (int)$excludeId);
        } else {
            $this->db->dbquery('SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) LIMIT 1');
            $this->db->dbbind(':name', $name);
        }

        return (bool)$this->db->getsingledata();
    }

    public function slugExists($slug, $excludeId = null)
    {
        if ($excludeId !== null) {
            $this->db->dbquery('SELECT id FROM categories WHERE slug = :slug AND id != :excludeId LIMIT 1');
            $this->db->dbbind(':slug', $slug);
            $this->db->dbbind(':excludeId', (int)$excludeId);
        } else {
            $this->db->dbquery('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
            $this->db->dbbind(':slug', $slug);
        }

        return (bool)$this->db->getsingledata();
    }

    public function getSupplierCount($categoryId)
    {
        $this->db->dbquery('SELECT COUNT(*) AS cnt FROM supplier_categories WHERE category_id = :id');
        $this->db->dbbind(':id', (int)$categoryId);
        $row = $this->db->getsingledata();

        return (int)($row['cnt'] ?? 0);
    }

    public function getServiceCount($categoryId)
    {
        $this->db->dbquery('SELECT COUNT(*) AS cnt FROM services WHERE category_id = :id');
        $this->db->dbbind(':id', (int)$categoryId);
        $row = $this->db->getsingledata();

        return (int)($row['cnt'] ?? 0);
    }

    public function getStats()
    {
        $this->db->dbquery('SELECT COUNT(*) AS total FROM categories');
        $row = $this->db->getsingledata();

        return [
            'total' => (int)($row['total'] ?? 0),
        ];
    }

    public static function slugify($value)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'category';
    }
}
