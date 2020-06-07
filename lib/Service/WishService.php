<?php
namespace OCA\Wishlist\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Wishlist\Db\Wish;
use OCA\Wishlist\Db\WishMapper;
use OCA\Wishlist\Service\NotAllowedException;


class WishService {

    private $mapper;


    public function __construct(WishMapper $mapper){
        $this->mapper = $mapper;
    }

    public function findAll(string $userId) {
        // $userIds = $this->mapper->getAllUserIdsWithWishes($userId);
        return $this->mapper->findAll($userId);
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException ||
            $e instanceof NotAllowedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    public function find(int $id, string $userId) {
        try {

            return $this->mapper->find($id, $userId);

        // in order to be able to plug in different storage backends like files
        // for instance it is a good idea to turn storage related exceptions
        // into service related exceptions so controllers and service users
        // have to deal with only one type of exception
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function create(
        string $title, 
        string $comment = '', 
        string $link = null, 
        string $createdBy, 
        string $userId,
        string $price) {
        $wish = new Wish();	
        $wish->setTitle($title);
        $wish->setUserId($userId);
        $wish->setComment($comment);
        $wish->setLink($link);
        $wish->setPrice($price);
        $wish->setCreatedBy($createdBy);
        $wish->setCreatedAt((new \DateTime('now'))->format('Y-m-d H:i:s'));
        return $this->mapper->insert($wish);
    }

    public function update(
        int $id, 
        string $title, 
        string $comment, 
        string $userId, 
        string $targetUser, 
        string $link = NULL, 
        string $price = NULL,
        string $grabbedBy = NULL
    ) {
        try {
            $wish = $this->mapper->findGlobal($id);

            if ($wish->getGrabbedBy() !== $grabbedBy) {
                $wish = $this->mapper->findGlobal($id);
                if ($grabbedBy && $wish->getGrabbedBy()) {
                    throw new NotAllowedException('Wish is already grabbed');
                }

                if ($grabbedBy && $wish->getUserId === $userId) {
                    throw new NotAllowedException('Cannot grap your own wish');
                }

                if (!$grabbedBy && $wish->getGrabbedBy() !== $userId) {
                    throw new NotAllowedException('Wish is not grabbed by another user');
                }

                $wish->setGrabbedBy($grabbedBy);
            } else {  
                if ($wish->getCreatedBy() !== $userId) {
                    throw new NotAllowedException('Wish not owned by user');
                }
                $wish->setTitle($title);
                $wish->setComment($comment);
                $wish->setLink($link);
                $wish->setUserId($targetUser);
                $wish->setPrice($price);

            }

            
            
            
            return $this->mapper->update($wish);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function delete(int $id, string $userId) {
        try {
            $wish = $this->mapper->find($id, $userId);
            $this->mapper->delete($wish);
            return $wish;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }
}
