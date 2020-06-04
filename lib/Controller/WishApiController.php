<?php
namespace OCA\Wishlist\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\Wishlist\Service\NoteService;

class NoteApiController extends ApiController {

    private $service;
    private $userId;

    use Errors;

    public function indindexex($AppName, IRequest $request,
                                NoteService $service, $UserId){
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->userId = $UserId;
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function index() {
        return new DataResponse($this->service->findAll($this->userId));
    }

    /**
     * @CORS
     * @NoCSRFRequiredindex
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function show($id) {
        return $this->handleNotFound(function () use ($id) {
            return $this->service->find($id, $this->userId);
        });
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param string $title
     * @param string $comment
     * @param string $link
     */
    public function create($title, $comment, $link) {
        return $this->service->create($title, $comment, $link, $this->userId);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param int $id
     * @param string $title
     * @param string $comment
     * @param string $link
     */
    public function update($id, $title, $comment, $link) {
        return $this->handleNotFound(function () use ($id, $title, $comment, $link) {
            return $this->service->update($id, $title, $comment, $link, $this->userId);
        });
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function destroy($id) {
        return $this->handleNotFound(function () use ($id) {
            return $this->service->delete($id, $this->userId);
        });
    }

}