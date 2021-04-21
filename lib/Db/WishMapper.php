<?php
namespace OCA\Wishlist\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class WishMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'wishes', Wish::class);
    }

    public function find(int $id, string $userId) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where(
                    $qb->expr()->eq('id', $qb->createNamedParameter($id))
            )->andWhere(
                $qb->expr()->eq('created_by', $qb->createNamedParameter($userId))
            );

        return $this->findEntity($qb);
    }

    public function findGlobal(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where(
                $qb->expr()->eq('id', $qb->createNamedParameter($id))
            );

        return $this->findEntity($qb);
    }

    public function findAll(string $userId) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where(
            $qb->expr()->orX(
                $qb->expr()->neq('user_id', $qb->createNamedParameter($userId)),
                $qb->expr()->eq('created_by', $qb->createNamedParameter($userId))
            
            )   
           );

        return $this->findEntities($qb);
    }
}