<?php
namespace OCA\Wishlist\Tests\Unit\Controller;

require_once __DIR__ . '/WishControllerTest.php';

class WishApiControllerTest extends WishControllerTest {

    public function setUp() {
        parent::setUp();
        $this->controller = new WishApiController(
            'wishlist', $this->request, $this->service, $this->userId
        );
    }

}