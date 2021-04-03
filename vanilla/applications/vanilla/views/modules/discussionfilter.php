<?php if (!defined('APPLICATION')) exit();

$Controller = Gdn::controller();
$Session = Gdn::session();
$Title = property_exists($Controller, 'Category') ? val('Name', $Controller->Category, '') : '';
if ($Title == '')
    $Title = t('All Discussions');

$Bookmarked = t('My Bookmarks');
$MyDiscussions = t('My Discussions');
$MyDrafts = t('My Drafts');
$MyWatches = t('Watching');
$CountBookmarks = 0;
$CountDiscussions = 0;
$CountDrafts = 0;
$CountWatchedCategories = 0;
$CountWatches = 0;

if ($Session->isValid()) {
    $CountBookmarks = $Session->User->CountBookmarks;
    $CountWatchedCategories = $Session->User->CountWatchedCategories;
    $CountWatches = $CountBookmarks + $CountWatchedCategories;
    $CountDiscussions = $Session->User->CountDiscussions;
    $CountDrafts = $Session->User->CountDrafts;
}
// function_exists('filterCountString') was moved to 'bootstrap.before.php' to re-use it

if (c('Vanilla.Discussions.ShowCounts', true)) {
    //$Bookmarked .= filterCountString($CountBookmarks, '/discussions/UserBookmarkCount', ['cssclass' => 'User-CountBookmarks']);
    $MyDiscussions .= filterCountString($CountDiscussions);
    $MyDrafts .= filterCountString($CountDrafts);
}
?>
<div class="BoxFilter BoxDiscussionFilter">
    <span class="sr-only BoxFilter-HeadingWrap">
        <h2 class="BoxFilter-Heading">
            <?php echo t('Quick Links'); ?>
        </h2>
    </span>
    <ul role="nav" class="FilterMenu">
        <?php
        $Controller->fireEvent('BeforeDiscussionFilters');
        if (c('Vanilla.Categories.Use')) {
            $menuOptions = [];

            $CssClass = 'AllCategories';
            $isActive = false;
            if($Controller instanceof CategoriesController || $Controller instanceof DiscussionController
                || $Controller instanceof PostController) {
               $isActive = true;
            }

            $menuOptions['AllCategories']['Url'] = anchor('Public Forums', '/categories');
            $menuOptions['AllCategories']['IsActive'] = $isActive;
            $menuOptions['AllCategories']['CssClass'] = $CssClass;

            $Controller->EventArguments['Menu'] = &$menuOptions;
            $Controller->fireEvent('BeforeRenderDiscussionFilters');
            foreach($menuOptions as $key => $value) {
                if($menuOptions[$key]['IsActive'] === true) {
                    $menuOptions[$key]['CssClass'] .= ' Active';
                }
                echo '<li class="' . $menuOptions[$key]['CssClass'] . '">' . $menuOptions[$key]['Url'] . '</li> ';
            }
        }
        /*
           <li id="RecentDiscussions" class="Discussions<?php echo strtolower($Controller->ControllerName) == 'discussionscontroller' && strtolower($Controller->RequestMethod) == 'index' && strpos(strtolower($Controller->Request->path()) , 'discussions') === 0? ' Active' : ''; ?>">
           <?php echo Gdn_Theme::link('forumroot', sprite('SpDiscussions').' '.t('Recent Discussions')); ?></li>
        */
        $Controller->fireEvent('BeforeUserLinksDiscussionFilters');
        ?>


        <?php if (($CountDiscussions > 0 || $Controller->RequestMethod == 'mine') && c('Vanilla.Discussions.ShowMineTab', true)) {
            ?>
            <li class="MyDiscussions<?php echo $Controller->ControllerName == 'discussionscontroller' && $Controller->RequestMethod == 'mine' ? ' Active' : ''; ?>"><?php echo anchor(sprite('SpMyDiscussions').' '.$MyDiscussions, '/discussions/mine'); ?></li>
            <?php
        }
        echo myDraftsMenuItem($CountDrafts);
        echo myWatchingMenuItem($CountWatches);
        // echo myBookmarksMenuItem($CountBookmarks);
        $Controller->fireEvent('AfterDiscussionFilters');
        ?>
    </ul>
</div>
