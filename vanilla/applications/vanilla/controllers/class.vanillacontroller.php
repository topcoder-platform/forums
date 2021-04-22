<?php
/**
 * Vanilla controller
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

/**
 * Master application controller for Vanilla, extended by all others except Settings.
 */
class VanillaController extends Gdn_Controller {

    const ROOT_CATEGORY =  ['Name' => 'Public Forums', 'Url'=>'/'];
    const CHALLENGE_FORUMS_URLCODE = 'challenges-forums';
    /**
     * Include JS, CSS, and modules used by all methods.
     *
     * Always called by dispatcher before controller's requested method.
     *
     * @since 2.0.0
     * @access public
     */
    public function initialize() {
        // Set up head
        $this->Head = new HeadModule($this);
        $this->addJsFile('jquery.js');
        $this->addJsFile('jquery.form.js');
        $this->addJsFile('jquery.popup.js');
        $this->addJsFile('jquery.popin.js');
        $this->addJsFile('jquery.gardenhandleajaxform.js');
        $this->addJsFile('jquery.atwho.js');
        $this->addJsFile('global.js');
        $this->addCssFile('style.css');
        $this->addCssFile('vanillicon.css', 'static');

        // Add modules
//      $this->addModule('MeModule');
        $this->addModule('GuestModule');
        $this->addModule('SignedInModule');

        parent::initialize();
    }

    /**
     * Check a category level permission.
     *
     * @param int|array|object $category The category to check the permission for.
     * @param string|array $permission The permission(s) to check.
     * @param bool $fullMatch Whether or not several permissions should be a full match.
     */
    protected function categoryPermission($category, $permission, $fullMatch = true) {
        if (!CategoryModel::checkPermission($category, $permission, $fullMatch)) {
            $categoryID = is_numeric($category) ? $category : val('CategoryID', $category);

            $this->permission($permission, $fullMatch, 'Category', $categoryID);
        }
    }

    /**
     * Check to see if we've gone off the end of the page.
     *
     * @param int $offset The offset requested.
     * @param int $totalCount The total count of records.
     * @throws Exception Throws an exception if the offset is past the last page.
     */
    protected function checkPageRange(int $offset, int $totalCount) {
        if ($offset > 0 && $offset >= $totalCount) {
            throw notFoundException();
        }
    }

    public function checkChallengeForums($CategoryID) {
        $Category = CategoryModel::categories($CategoryID);
        $ancestors = CategoryModel::getAncestors($CategoryID);
        if(val('GroupID', $Category) > 0) {
            return true;
        }

        foreach ($ancestors as $id => $ancestor) {
            if($ancestor['UrlCode'] == self::CHALLENGE_FORUMS_URLCODE) {
                return true;
            }
            if($ancestor['GroupID'] > 0) {
                return true;
            }
        }

        return false;
    }

    protected function buildBreadcrumbs($CategoryID) {
        $Category = CategoryModel::categories($CategoryID);
        $ancestors = CategoryModel::getAncestors($CategoryID);
        $parentCategoryID = val('ParentCategoryID', $Category);
        if(val('GroupID', $Category) > 0) {
            $challenge = $this->data('Challenge');
            $track = $challenge ? $challenge['Track']: false;
            $temp = [];
            $GroupCategoryID =  $this->data('Breadcrumbs.Options.GroupCategoryID');
            foreach ($ancestors as $id => $ancestor) {
                if($ancestor['GroupID'] > 0) {
                    if($GroupCategoryID == $ancestor['CategoryID']) {// root category for a group
                        array_push($temp,  ['Name' => $ancestor['Name'], 'Url'=>'/group/'.$ancestor['GroupID']]);
                    } else {
                       $temp[$ancestor['CategoryID']] = $ancestor;
                   }
                } else {
                    if($ancestor['UrlCode'] == self::CHALLENGE_FORUMS_URLCODE) {
                        array_push($temp,  ['Name' => 'Challenge Forums', 'Url'=>'/groups/mine?filter=challenge']);
                    }else if($ancestor['UrlCode'] == 'groups') {
                        array_push($temp,  ['Name' => 'Group Forums', 'Url'=>'/groups/mine?filter=regular']);
                    } else {
                        if($track) {
                            switch ($ancestor['UrlCode']) {
                                case 'development-forums':
                                case 'data-science-forums':
                                case 'design-forums':
                                    array_push($temp, ['Name' => $track, 'Url'=>'/groups/mine?filter=challenge']);
                                    break;
                                default:
                                    $temp[$ancestor['CategoryID']] = $ancestor;
                            }
                        }
                    }
                }
            }
            return $temp;
        } else {
            $urlCode = val('UrlCode', $Category);
            if($urlCode == self::CHALLENGE_FORUMS_URLCODE) {
                return $ancestors;
            }

            // Check if ancestors contains 'challenges-forums'
            foreach ($ancestors as $id => $ancestor) {
                if($ancestor['UrlCode'] == self::CHALLENGE_FORUMS_URLCODE) {
                    return $ancestors;
                }
            }

            // FIX https://github.com/topcoder-platform/forums/issues/487
            // Go to a parent category at a home page
            foreach ($ancestors as $id => $ancestor) {
                if ($ancestor['ParentCategoryID'] == -1) {
                     $ancestors[$id]['Url'] = url('/categories/#Category_'.$parentCategoryID, true);
                }
            }

            return $ancestors;
        }
    }

    protected function log($message, $context = [], $level = Logger::DEBUG) {
        //  if(c('Debug')) {
        Logger::log($level, sprintf('%s : %s',get_class($this), $message), $context);
        //  }
    }
}
