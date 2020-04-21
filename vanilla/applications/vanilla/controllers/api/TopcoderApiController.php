<?php

use Garden\Web\Exception\ClientException;

/**
 * API Controller for the `/tc-categories` resource.
 */
class TopcoderApiController extends AbstractApiController{

    /** @var UserMetaModel */
    private $userMetaModel;
    /** @var UserMetaModel */
    private $categoryModel;
    /**
     * TcCategoriesApiController constructor.
     *
     * @param UserMetaModel $userMetaModel
     * @param CategoryModel $categoryModel
     */
    public function __construct(UserMetaModel $userMetaModel, CategoryModel $categoryModel) {
        $this->userMetaModel = $userMetaModel;
        $this->categoryModel = $categoryModel;
    }

    /**
     * Lookup a single category by its numeric ID
     *
     * @param int $id The category ID
     * @throws NotFoundException if the category cannot be found.
     * @return array
     */
    private function category($id) {
        $category = CategoryModel::categories($id);
        if (empty($category)) {
            throw new NotFoundException('Category');
        }
        return $category;
    }

    /**
     * Add the "follow" status on a category for the user.
     *
     * @param int $id The target category's ID.
     * @param $userId The target user's ID.
     * @param array $body
     * @return array
     * @throws ClientException
     * @throws \Garden\Schema\ValidationException
     * @throws \Garden\Web\Exception\HttpException
     * @throws \Vanilla\Exception\PermissionException
     */
    public function put_watch($userId,$id, array $body) {
        $this->permission('Garden.SignIn.Allow');
        $schema = ['watched:b' => 'The category-watched status for the user.'];
        $in = $this->schema($schema, 'in');
        $out = $this->schema($schema, 'out');
        $body = $in->validate($body);
        $newEmailCommentKey = 'Preferences.Email.NewComment.'.$id;
        $newEmailDiscussionKey = 'Preferences.Email.NewDiscussion.'.$id;
        $newPopupCommentKey = 'Preferences.Popup.NewComment.'.$id;
        $newPopupDiscussionKey = 'Preferences.Popup.NewDiscussion.'.$id;
        $isDiscussionFollowed = count($this->userMetaModel->getUserMeta($userId,$newEmailDiscussionKey)) > 0;

        // Is this a new follow?
        if ($body['watched'] && !$isDiscussionFollowed) {
            $category = $this->category($id);
            $this->permission('Vanilla.Discussions.View', $category['PermissionCategoryID']);
        }
        // null is used to remove data
        $followed = $body['watched'] ? 1 : null;
        $this->userMetaModel->setUserMeta($userId, $newEmailCommentKey , $followed);
        $this->userMetaModel->setUserMeta($userId, $newEmailDiscussionKey, $followed);
        $this->userMetaModel->setUserMeta($userId, $newPopupCommentKey , $followed);
        $this->userMetaModel->setUserMeta($userId, $newPopupDiscussionKey , $followed);

        $result = $out->validate([
            'watched' => count($this->userMetaModel->getUserMeta($userId,$newEmailDiscussionKey)) > 0
        ]);
        return $result;
    }
}