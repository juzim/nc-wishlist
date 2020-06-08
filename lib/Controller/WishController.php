<?php
 namespace OCA\Wishlist\Controller;

 use OCP\IRequest;
 use OCP\IUserManager;
 use OCP\AppFramework\Controller;
 use OCA\Wishlist\Service\WishService; 
 use OCP\AppFramework\Http\DataResponse;
 use OCA\Wishlist\Db\Wish;

 class WishController extends Controller {

    /** @var WishService */
	private $service;


    /** @var IUserManager */
	private $userManager;

	/** @var string */
	private $userId;

    use Errors;

    public function __construct(string $AppName, IRequest $request, WishService $service, IUserManager $userManager, $UserId){
         parent::__construct($AppName, $request);
         $this->service = $service;
         $this->userId = $UserId;
         $this->userManager = $userManager;
     }

    /**
    * @NoAdminRequired
    */
    public function index() {
        $wishes = $this->service->findAll($this->userId);

        $ids = [];

        foreach ($wishes as $wish) {
            $ids[] = $wish->getUserId();
        }

        $users = [];
        foreach (array_unique($ids) as $id) {
            $user = $this->userManager->get($id);
            $users[$id] = [
                "uid" => $user->getUID(),
                "name" => $user->getDisplayName(),
            ];
        }

        return new DataResponse([
            "userId" => $this->userId,
            "wishes" => $wishes,
            "users" => $users,
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
     * @param string $userId
     * @param string $price
     */
    public function create(string $title, string $comment = '', string $link = NULL, string $userId, string $price = NULL) {
        return $this->service->create($title, $comment, $link, $this->userId, $userId, $price);
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