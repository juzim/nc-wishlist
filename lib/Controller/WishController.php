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

    use Errors;

    public function __construct(string $AppName, IRequest $request, WishService $service, $UserId){
         parent::__construct($AppName, $request);
         $this->service = $service;
         $this->userId = $UserId;
     }

    /**
    * @NoAdminRequired
    */
    public function index() {
        return new DataResponse([
            "userId" => $this->userId,
            "wishes" => $this->service->findAll($this->userId)
        ]);
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
     * @param string $comment
     * @param string $link
     * @param string $targetUser
     * @param string $price
     */
    public function create(string $title, string $comment = '', string $link = NULL, string $targetUser, string $price = NULL) {
        return $this->service->create($title, $comment, $link, $this->userId, $targetUser, $price);
    }        

    /**
     * @NoAdminRequired
     *
     * @param int $id
     * @param string $title
     * @param string $comment
     * @param string $link
     * @param string $userId
     * @param string $price
     * @param string $grabbedBy
     */
    public function update(
        int $id, 
        string $title, 
        string $comment = '', 
        string $link = NULL, 
        $userId, 
        string $price = NULL, 
        string $grabbedBy = NULL
    ) {
        return $this->handleNotFound(function () use ($id, $title, $comment, $link, $userId, $price, $grabbedBy) {
            return $this->service->update($id, $title, $comment, $this->userId, $userId, $link, $price, $grabbedBy);
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