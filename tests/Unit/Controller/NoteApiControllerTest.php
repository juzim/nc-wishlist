<?php

namespace OCA\Wishlist\Tests\Unit\Controller;

use OCA\Wishlist\Controller\NoteApiController;

class NoteApiControllerTest extends NoteControllerTest {
	public function setUp(): void {
		parent::setUp();
		$this->controller = new NoteApiController(
			'Wishlist', $this->request, $this->service, $this->userId
		);
	}
}
