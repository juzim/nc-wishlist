<?php
 namespace OCA\Wishlist\Controller;

 use OCP\IRequest;
 use OCP\AppFramework\Controller;
 use OCA\Wishlist\Service\WishService; 
 use OCP\AppFramework\Http\DataResponse;
 use OCA\Wishlist\Db\Wish;

 class WishController extends Controller {

    /** @var WishService */
	private $service;

	/** @var string */
	private $userId;


     public function __construct(string $AppName, IRequest $request, WishService $service, $UserId){
         parent::__construct($AppName, $request);
         $this->service = $service;
         $this->userId = $UserId;
     }

     /**
      * @NoAdminRequired
      */
     public function index() {
        return new DataResponse($this->service->findAll($this->userId));
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     */					
    public function show(int $id) {			
        return $this->handleNotFound(function () use ($id) {
            return $this->service->find($id, $this->userId);
        });			
    }

     /**
     * @NoAdminRequired
     *
     * @param string $title
     * @param string $content
     * @param string $link
     */
    // public function create(string $title, string $content = '', string $link = null, string $targetUser) {
        // return $this->service->create($title, $content, $link, $this->userId, $targetUser);
    // }
    public function create(string $title, string $content = '', string $link = null) {
        return $this->service->create($title, $content, $link, $this->userId, $this->userId);
    }        

    /**
     * @NoAdminRequired
     *
     * @param int $id
     * @param string $title
     * @param string $content
     */
    public function update(int $id, string $title, string $comment = '', string $link = NULL, $targetUser) {
        return $this->handleNotFound(function () use ($id, $title, $comment, $link) {
            return $this->service->update($id, $title, $comment, $link, $targetUser);
        });
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function destroy(int $id) {
        return $this->handleNotFound(function () use ($id) {
            return $this->service->delete($id, $this->userId);
        });
    }

}