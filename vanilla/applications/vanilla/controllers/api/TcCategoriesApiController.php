<?php

use Garden\Web\Exception\ClientException;

/**
 * API Controller for the `/tc-categories` resource.
 */
class TcCategoriesApiController extends CategoriesApiController {

    /** @var CategoryModel */
    private $categoryModel;

    /**
     * TcCategoriesApiController constructor.
     *
     * @param CategoryModel $categoryModel
     */
    public function __construct(CategoryModel $categoryModel) {
        $this->categoryModel = $categoryModel;
    }


    /**
     * Set the "follow" status on a category for the user.
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
    public function put_follow($id, $userId, array $body) {
        $this->permission('Garden.SignIn.Allow');
        $schema = ['followed:b' => 'The category-follow status for the user.'];
        $in = $this->schema($schema, 'in');
        $out = $this->schema($schema, 'out');
        $category = $this->category($id);
        $body = $in->validate($body);
        $followed = $this->categoryModel->getFollowed($userId);

        // Is this a new follow?
        if ($body['followed'] && !array_key_exists($id, $followed)) {
            $this->permission('Vanilla.Discussions.View', $category['PermissionCategoryID']);
            if (count($followed) >= $this->categoryModel->getMaxFollowedCategories()) {
                throw new ClientException('Already following the maximum number of categories.');
            }
        }

        $this->categoryModel->follow($userId, $id, $body['followed']);
        $result = $out->validate([
            'followed' => $this->categoryModel->isFollowed($userId, $id)
        ]);
        return $result;
    }
}