<?php
/**
 * Categories module
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

/**
 * Renders the discussion categories.
 */
class CategoriesModule extends Gdn_Module {

    /** @var int Inclusive. */
    public $startDepth = 1;

    /** @var int Inclusive. */
    public $endDepth;

    /** @var bool Whether or not to collapse categories that contain other categories. */
    public $collapseCategories = true;

    /**
     * @var int|null The ID of the root category.
     */
    public $root = null;

    public function __construct($sender = '') {
        parent::__construct($sender);
        $this->_ApplicationFolder = 'vanilla';

        $this->Visible = c('Vanilla.Categories.Use') && !c('Vanilla.Categories.HideModule');
    }

    public function assetTarget() {
        return 'Panel';
    }

    /**
     * Get the data for this module.
     */
    protected function getData() {
        // Allow plugins to set different data.
        $this->fireEvent('GetData');
        if ($this->Data) {
            return;
        }

        $categoryModel = new CategoryModel();
        $categories = $categoryModel
            ->setJoinUserCategory(true)
            ->getChildTree($this->root, ['collapseCategories' => $this->collapseCategories]);
        $categories = CategoryModel::flattenTree($categories);

        $categories = array_filter($categories, function ($category) {
            return val('PermsDiscussionsView', $category) && val('Following', $category);
        });

        $displayedCategories = $this->filterCategories($categories);
        $data = new Gdn_DataSet($displayedCategories, DATASET_TYPE_ARRAY);
        $data->datasetType(DATASET_TYPE_OBJECT);
        $this->Data = $data;

    }

    /**
     * Filter Categories: hide 'Challenges Forums'
     * @param $categories
     * @return array
     */
    private function filterCategories($categories) {
        $hiddenCategories = ['challenges-forums', 'groups'];
        foreach ($categories as &$category) {
            $categoryID = $category['CategoryID'];
            $categoryUrlCode = $category['UrlCode'];
            $isHidden = in_array($categoryUrlCode, $hiddenCategories);
            $category['isDisplayed'] = !$isHidden;
        }

        return array_filter($categories, function($e) {
            return ($e['isDisplayed'] == true);
        });
    }

    public function filterDepth(&$categories, $startDepth, $endDepth) {
        if ($startDepth != 1 || $endDepth) {
            foreach ($categories as $i => $category) {
                if (val('Depth', $category) < $startDepth || ($endDepth && val('Depth', $category) > $endDepth)) {
                    unset($categories[$i]);
                }
            }
        }
    }

    public function toString() {
        if (!$this->Data) {
            $this->getData();
        }

        $this->filterDepth($this->Data->result(), $this->startDepth, $this->endDepth);

        return parent::toString();
    }
}
