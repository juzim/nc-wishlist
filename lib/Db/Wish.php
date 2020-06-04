<?php
namespace OCA\Wishlist\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Wish extends Entity implements JsonSerializable {

    protected $title;
    protected $userId;
    protected $comment;
    protected $link;

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'comment' => $this->comment,
            'link' => $this->link,
        ];
    }
}