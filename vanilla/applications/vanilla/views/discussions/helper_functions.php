<?php use Vanilla\Formatting\DateTimeFormatter;

if (!defined('APPLICATION')) exit();

if (!function_exists('AdminCheck')) {
    /**
     *
     *
     * @param null $discussion
     * @param bool|FALSE $wrap
     * @return string
     */
    function adminCheck($discussion = null, $wrap = FALSE) {
        static $useAdminChecks = NULL;
        if ($useAdminChecks === null) {
            $useAdminChecks = c('Vanilla.AdminCheckboxes.Use') && Gdn::session()->checkPermission('Garden.Moderation.Manage');
        }
        if (!$useAdminChecks) {
            return '';
        }

        static $canEdits = [], $checked = NULL;
        $result = '';

        if ($discussion) {
            if (!isset($canEdits[$discussion->CategoryID])) {
                $canEdits[$discussion->CategoryID] = val('PermsDiscussionsEdit', CategoryModel::categories($discussion->CategoryID));
            }

            if ($canEdits[$discussion->CategoryID]) {
                // Grab the list of currently checked discussions.
                if ($checked === null) {
                    $checked = (array)Gdn::session()->getAttribute('CheckedDiscussions', []);

                    if (!is_array($checked)) {
                        $checked = [];
                    }
                }

                if (in_array($discussion->DiscussionID, $checked))
                    $itemSelected = ' checked="checked"';
                else
                    $itemSelected = '';

                $result = <<<EOT
<span class="AdminCheck"><input type="checkbox" name="DiscussionID[]" value="{$discussion->DiscussionID}" $itemSelected /></span>
EOT;
            }
        } else {
            $result = '<span class="AdminCheck"><input type="checkbox" name="Toggle" /></span>';
        }

        if ($wrap) {
            $result = $wrap[0].$result.$wrap[1];
        }

        return $result;
    }
}

if (!function_exists('BookmarkButton')) {
    /**
     *
     *
     * @param $discussion
     * @return string
     */
    function bookmarkButton($discussion) {
        if (!Gdn::session()->isValid()) {
            return '';
        }

        // Bookmark link
        // FIX : https://github.com/topcoder-platform/forums/issues/577
        // If User is watching a category then show it as bookmarked
        $categoryModel = new CategoryModel();
        $category = CategoryModel::categories($discussion->CategoryID);
        $groupID = val('GroupID', $category);
        // No changes for Challenge Forums
        if($groupID && $groupID > 0) {
            // New value should be opposite
            $hasWatched = $discussion->Bookmarked == 1;
            $newValue = $hasWatched ? 0 : 1;
            $title = t($hasWatched ? 'Stop watching forum' : 'Watch forum');
            $icon = '<span class="tooltiptext">'.$title.'</span>'.watchIcon($hasWatched);
            return anchor(
                $icon,
                '/discussion/bookmark/' . $discussion->DiscussionID . '/?tkey=' . Gdn::session()->transientKey() . '&bookmarked=' . $newValue,
                'Hijack  watchButton ' . ($hasWatched ? ' isWatching tooltip' : 'tooltip'),
                []
            );
        } else {
            $notificationPreferences = $categoryModel->getCategoryNotificationPreferences($discussion->CategoryID, Gdn::session()->UserID);
            $categoryNotificationPreferences = $notificationPreferences[$discussion->CategoryID];
            $newEmailDiscussionKey = 'Preferences.Email.NewComment.' . $discussion->CategoryID;
            //$newPopupDiscussionKey = 'Preferences.Popup.NewComment.' . $discussion->CategoryID;
            $hasWatchedCategory = val($newEmailDiscussionKey, $categoryNotificationPreferences);

            if($hasWatchedCategory == '1') { // The watched category
                $hasWatched  = true;
                $newValue = 0;
            } else if($hasWatchedCategory == '2') {// The watched category except some discussions
                if ($discussion->Bookmarked === null) {
                    $hasWatched  = true;
                    $newValue = 0;
                } else if ($discussion->Bookmarked == 0) {
                    $hasWatched  = false;
                    $newValue = 1;
                } else if ($discussion->Bookmarked == 1) {
                    $hasWatched  = true;
                    $newValue = 0;
                }
            } else {
                $hasWatched = false;
                if ($discussion->Bookmarked === null) {
                    $newValue = 1;
                } else if ($discussion->Bookmarked == 0) {
                    $newValue = 1;
                } else if ($discussion->Bookmarked == 1) {
                    $hasWatched = true;
                    $newValue = 0;
                }
            }

            $title = t($hasWatched ? 'Stop watching forum' : 'Watch forum');
            $icon = '<span class="tooltiptext">'.$title.'</span>'.watchIcon($hasWatched, '');
            return anchor(
                $icon,
                '/discussion/bookmark/' . $discussion->DiscussionID . '/?tkey=' . Gdn::session()->transientKey() . '&bookmarked=' . $newValue,
                'Hijack  watchButton ' . ($hasWatched ? ' isWatching tooltip' : 'tooltip'),
                []
            );
        }
    }
}

if (!function_exists('CategoryLink')) :
    /**
     *
     *
     * @param $discussion
     * @param string $prefix
     * @return string
     */
    function categoryLink($discussion, $prefix = ' ') {
        $category = CategoryModel::categories(val('CategoryID', $discussion));
        if ($category) {
            return wrap($prefix.anchor(htmlspecialchars($category['Name']), $category['Url']), 'span', ['class' => 'MItem Category']);
        }
    }

endif;

if (!function_exists('DiscussionHeading')) :
    /**
     *
     *
     * @return string
     */
    function discussionHeading() {
        return t('Discussion');
    }

endif;

if (!function_exists('WriteDiscussion')) :
    /**
     *
     *
     * @param $discussion
     * @param $sender
     * @param $session
     */
    function writeDiscussion($discussion, $sender, $session) {
        $cssClass = cssClass($discussion);
        $discussionUrl = $discussion->Url;
        $category = CategoryModel::categories($discussion->CategoryID);

        if ($session->UserID) {
           // FIX: don't navigate to an editor
           // $discussionUrl .= '#latest';
        }
        $sender->EventArguments['DiscussionUrl'] = &$discussionUrl;
        $sender->EventArguments['Discussion'] = &$discussion;
        $sender->EventArguments['CssClass'] = &$cssClass;

        $first = userBuilder($discussion, 'First');
        $last = userBuilder($discussion, 'LastDiscussionComments');
        $lastDate = val('LastDiscussionCommentsDate', $discussion);
        $sender->EventArguments['FirstUser'] = &$first;
        $sender->EventArguments['LastUser'] = &$last;

        $sender->fireEvent('BeforeDiscussionName');

        $discussionName = $discussion->Name;
        // If there are no word character detected in the title treat it as if it is blank.
        if (!preg_match('/\w/u', $discussionName)) {
            $discussionName = t('Blank Discussion Topic');
        }
        $sender->EventArguments['DiscussionName'] = &$discussionName;

        static $firstDiscussion = true;
        if (!$firstDiscussion) {
            $sender->fireEvent('BetweenDiscussion');
        } else {
            $firstDiscussion = false;
        }

        $discussion->CountPages = ceil($discussion->CountComments / $sender->CountCommentsPerPage);
        ?>
        <li id="Discussion_<?php echo $discussion->DiscussionID; ?>" class="<?php echo $cssClass; ?>">
            <?php
            if (!property_exists($sender, 'CanEditDiscussions')) {
                $sender->CanEditDiscussions = val('PermsDiscussionsEdit', CategoryModel::categories($discussion->CategoryID)) && c('Vanilla.AdminCheckboxes.Use');
            }
            $sender->fireEvent('BeforeDiscussionContent');
            ?>
            <span class="Options">
      <?php
      echo optionsList($discussion);
      echo bookmarkButton($discussion);
      ?>
   </span>

            <div class="ItemContent Discussion">
                <div class="Title" role="heading" aria-level="3">
                    <?php
                    echo adminCheck($discussion, ['', ' ']).anchor($discussionName, $discussionUrl);
                    $sender->fireEvent('AfterDiscussionTitle');
                    ?>
                </div>
                <?php
                $sender->fireEvent('BeforeDiscussionMetaData');
                ?>
                <div class="Meta Meta-Discussion">
                    <?php
                    writeTags($discussion);
                    ?>
                    <?php
                    echo newComments($discussion);

                    $sender->fireEvent('AfterCountMeta');

                    if ($discussion->LastDiscussionCommentsUserID != '') {
                        $dateFormatted = Gdn::getContainer()->get(DateTimeFormatter::class)->formatDate($lastDate, false, '%a, %b %e %Y');
                        $timeFormatted = Gdn::getContainer()->get(DateTimeFormatter::class)->formatDate($lastDate, false, '%I:%M %p');
                        echo '<span class="MItem LastCommentBy">'.sprintf(t('Most recent by %1$s on %2$s at %3$s'), userAnchor($last),$dateFormatted, $timeFormatted).'</span>';
                    } else {
                        $dateFormatted = Gdn::getContainer()->get(DateTimeFormatter::class)->formatDate($discussion->FirstDate, false, DateTimeFormatter::FORCE_FULL_FORMAT);
                        echo ' <span class="MItem LastCommentBy">'.sprintf(t('Started by %1$s'), userAnchor($first)).'</span> ';
                        echo ' <span class="MItem LastCommentDate">'.$dateFormatted;
                        if ($source = val('Source', $discussion)) {
                            echo ' '.sprintf(t('via %s'), t($source.' Source', $source));
                        }
                        echo '</span> ';
                    }

                    if ($sender->data('_ShowCategoryLink', true) && $category && c('Vanilla.Categories.Use') &&
                        CategoryModel::checkPermission($category, 'Vanilla.Discussions.View')) {

                        echo wrap(
                            anchor(htmlspecialchars($discussion->Category),
                            categoryUrl($discussion->CategoryUrlCode)),
                            'span',
                            ['class' => 'MItem Category '.$category['CssClass']]
                        );
                    } ?>
                    <span class="fill-remaining-space"></span>
                    <span class="MItem MCount CommentCount"><?php
                        printf(pluralTranslate($discussion->CountComments,
                            '%s comment html', '%s comments html', t('%s comment'), t('%s comments')),
                            bigPlural($discussion->CountComments, '%s comment'));
                        ?></span>
                    <span class="MItem MiddleDot">&#183;</span>
                    <span class="MItem MCount ViewCount"><?php
                        printf(pluralTranslate($discussion->CountViews,
                            '%s view html', '%s views html', t('%s view'), t('%s views')),
                            bigPlural($discussion->CountViews, '%s view'));
                        ?></span>
                    <span class="MItem MCount DiscussionScore Hidden"><?php
                        $score = $discussion->Score;
                        if ($score == '') $score = 0;
                        printf(plural($score, '%s point', '%s points', bigPlural($score, '%s point')));
                        ?></span>
                    <?php
                    $sender->fireEvent('DiscussionMeta');
                    ?>

                </div>
            </div>
            <?php $sender->fireEvent('AfterDiscussionContent'); ?>
        </li>
    <?php
    }
endif;

if (!function_exists('WriteDiscussionSorter')) :
    /**
     *
     *
     * @param null $selected
     * @param null $options
     */
    function writeDiscussionSorter($selected = null, $options = null) {
        deprecated('writeDiscussionSorter', 'DiscussionSortFilterModule', 'March 2016');

        if ($selected === null) {
            $selected = Gdn::session()->getPreference('Discussions.SortField', 'DateLastComment');
        }
        $selected = stringBeginsWith($selected, 'd.', TRUE, true);

        $options = [
            'DateLastComment' => t('Sort by Last Comment', 'by Last Comment'),
            'DateInserted' => t('Sort by Start Date', 'by Start Date')
        ];

        ?>
        <span class="ToggleFlyout SelectFlyout">
        <?php
        if (isset($options[$selected])) {
            $text = $options[$selected];
        } else {
            $text = reset($options);
        }
        echo wrap($text.' '.sprite('', 'DropHandle'), 'span', ['class' => 'Selected']);
        ?>
            <div class="Flyout MenuItems">
                <ul>
                    <?php
                    foreach ($options as $sortField => $sortText) {
                        echo wrap(anchor($sortText, '#', ['class' => 'SortDiscussions', 'data-field' => $sortField]), 'li');
                    }
                    ?>
                </ul>
            </div>
         </span>
        <?php
    }
endif;

if (!function_exists('WriteMiniPager')) :
    /**
     *
     *
     * @param $discussion
     */
    function writeMiniPager($discussion) {
        if (!property_exists($discussion, 'CountPages')) {
            return;
        }

        if ($discussion->CountPages > 1) {
            echo '<span class="MiniPager">';
            if ($discussion->CountPages < 5) {
                for ($i = 0; $i < $discussion->CountPages; $i++) {
                    writePageLink($discussion, $i + 1);
                }
            } else {
                writePageLink($discussion, 1);
                writePageLink($discussion, 2);
                echo '<span class="Elipsis">...</span>';
                writePageLink($discussion, $discussion->CountPages - 1);
                writePageLink($discussion, $discussion->CountPages);
                // echo anchor('Go To Page', '#', 'GoToPageLink');
            }
            echo '</span>';
        }
    }
endif;

if (!function_exists('WritePageLink')):
    /**
     *
     *
     * @param $discussion
     * @param $pageNumber
     */
    function writePageLink($discussion, $pageNumber) {
        echo anchor($pageNumber, discussionUrl($discussion, $pageNumber));
    }
endif;

if (!function_exists('NewComments')) :
    /**
     *
     *
     * @param $discussion
     * @return string
     */
    function newComments($discussion) {
        if (!Gdn::session()->isValid())
            return '';

        if ($discussion->CountUnreadComments === TRUE) {
            $title = htmlspecialchars(t("You haven't read this yet."));

            return ' <span class="MItem"><strong class="HasNew JustNew NewCommentCount" title="'.$title.'">'.t('new discussion', 'new').'</strong></span>';
        } elseif ($discussion->CountUnreadComments > 0) {
            $title = htmlspecialchars(plural($discussion->CountUnreadComments, "%s new comment since you last read this.", "%s new comments since you last read this."));
            return ' <span class="MItem"><strong class="HasNew NewCommentCount" title="'.$title.'">'.plural($discussion->CountUnreadComments, '%s new', '%s new plural', bigPlural($discussion->CountUnreadComments, '%s new', '%s new plural')).'</strong></span>';
        }
        return '';
    }
endif;

if (!function_exists('tag')) :
    /**
     *
     *
     * @param $discussion
     * @param $column
     * @param $code
     * @param bool|false $cssClass
     * @return string|void
     */
    function tag($discussion, $column, $code, $cssClass = FALSE) {
        $discussion = (object)$discussion;

        if (is_numeric($discussion->$column) && !$discussion->$column)
            return '';
        if (!is_numeric($discussion->$column) && strcasecmp($discussion->$column, $code) != 0)
            return;

        if (!$cssClass)
            $cssClass = "Tag-$code";

        return ' <span class="Tag '.$cssClass.'" title="'.htmlspecialchars(t($code)).'">'.t($code).'</span> ';
    }
endif;

if (!function_exists('writeTags')) :
    /**
     *
     *
     * @param $discussion
     * @throws Exception
     */
    function writeTags($discussion) {
        Gdn::controller()->fireEvent('BeforeDiscussionMeta');

        echo tag($discussion, 'Announce', 'Announcement');
        echo tag($discussion, 'Closed', 'Closed');

        Gdn::controller()->fireEvent('AfterDiscussionLabels');
    }
endif;

if (!function_exists('writeFilterTabs')) :
    /**
     *
     *
     * @param $sender
     */
    function writeFilterTabs($sender) {
        $session = Gdn::session();
        $title = property_exists($sender, 'Category') ? val('Name', $sender->Category, '') : '';
        if ($title == '') {
            $title = t('All Discussions');
        }
        $bookmarked = t('My Bookmarks');
        $myDiscussions = t('My Discussions');
        $myDrafts = t('My Drafts');
        $countBookmarks = 0;
        $countDiscussions = 0;
        $countDrafts = 0;

        if ($session->isValid()) {
            $countBookmarks = $session->User->CountBookmarks;
            $countDiscussions = $session->User->CountDiscussions;
            $countDrafts = $session->User->CountDrafts;
        }

        if (c('Vanilla.Discussions.ShowCounts', true)) {
            $bookmarked .= countString($countBookmarks, '/discussions/UserBookmarkCount');
            $myDiscussions .= countString($countDiscussions);
            $myDrafts .= countString($countDrafts);
        }

        ?>
        <div class="Tabs DiscussionsTabs">
            <?php
            if (!property_exists($sender, 'CanEditDiscussions')) {
                $sender->CanEditDiscussions = $session->checkPermission('Vanilla.Discussions.Edit', true, 'Category', 'any') && c('Vanilla.AdminCheckboxes.Use');
            }
            if ($sender->CanEditDiscussions) {
                ?>
                <span class="Options"><span class="AdminCheck">
                    <input type="checkbox" name="Toggle"/>
                </span></span>
            <?php } ?>
            <ul>
                <?php $sender->fireEvent('BeforeDiscussionTabs'); ?>
                <li<?php echo strtolower($sender->ControllerName) == 'discussionscontroller' && strtolower($sender->RequestMethod) == 'index' ? ' class="Active"' : ''; ?>><?php echo anchor(t('All Discussions'), 'discussions', 'TabLink'); ?></li>
                <?php $sender->fireEvent('AfterAllDiscussionsTab'); ?>

                <?php
                if (c('Vanilla.Categories.ShowTabs')) {
                    $cssClass = '';
                    if (strtolower($sender->ControllerName) == 'categoriescontroller' && strtolower($sender->RequestMethod) == 'all') {
                        $cssClass = 'Active';
                    }

                    echo " <li class=\"$cssClass\">".anchor(t('Categories'), '/categories/all', 'TabLink').'</li> ';
                }
                ?>
                <?php if ($countBookmarks > 0 || $sender->RequestMethod == 'bookmarked') { ?>
                    <li<?php echo $sender->RequestMethod == 'bookmarked' ? ' class="Active"' : ''; ?>><?php echo anchor($bookmarked, '/discussions/bookmarked', 'MyBookmarks TabLink'); ?></li>
                    <?php
                    $sender->fireEvent('AfterBookmarksTab');
                }
                if (($countDiscussions > 0 || $sender->RequestMethod == 'mine') && c('Vanilla.Discussions.ShowMineTab', true)) {
                    ?>
                    <li<?php echo $sender->RequestMethod == 'mine'  && $sender->ControllerName == 'discussionscontroller' ? ' class="Active"' : ''; ?>><?php echo anchor($myDiscussions, '/discussions/mine', 'MyDiscussions TabLink'); ?></li>
                <?php
                }
                if ($countDrafts > 0 || $sender->ControllerName == 'draftscontroller') {
                    ?>
                    <li<?php echo $sender->ControllerName == 'draftscontroller' ? ' class="Active"' : ''; ?>><?php echo anchor($myDrafts, '/drafts', 'MyDrafts TabLink'); ?></li>
                <?php
                }
                $sender->fireEvent('AfterDiscussionTabs');
                ?>
            </ul>
        </div>
    <?php
    }
endif;

if (!function_exists('optionsList')) :
    /**
     * Build HTML for discussions options menu.
     *
     * @param $discussion
     * @return DropdownModule|string
     * @throws Exception
     */
    function optionsList($discussion) {
        if (Gdn::session()->isValid() && !empty(Gdn::controller()->ShowOptions)) {
            include_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussion', 'vanilla');
            return getDiscussionOptionsDropdown($discussion);
        }
        return '';
    }
endif;

if (!function_exists('writeOptions')) :
    /**
     * Render options that the user has for this discussion.
     */
    function writeOptions($discussion) {
        if (!Gdn::session()->isValid() || !Gdn::controller()->ShowOptions)
            return;

        echo '<span class="Options">';

        // Options list.
        echo optionsList($discussion);

        // Bookmark button.
        echo bookmarkButton($discussion);

        // Admin check.
        echo adminCheck($discussion);

        echo '</span>';
    }
endif;
