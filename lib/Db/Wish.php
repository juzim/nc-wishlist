<?php
namespace OCA\Wishlist\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Wish extends Entity implements JsonSerializable {

    protected $title;
    protected $userId;
    protected $comment;
    protected $link;
    protected $createdAt;
    protected $createdBy;
    protected $grabbedBy;
    protected $price;

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'comment' => $this->comment,
            'link' => $this->link,
            'createdAt' => $this->createdAt,
            'createdBy' => $this->createdBy,
            'userId' => $this->userId,
            'grabbedBy' => $this->grabbedBy,
            'price' => $this->price,
        ];
    }
}