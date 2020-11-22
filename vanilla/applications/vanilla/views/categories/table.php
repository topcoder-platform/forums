<?php if (!defined('APPLICATION')) return;
$userID = Gdn::session()->UserID;
$categoryID = isset($this->Category) ? $this->Category->CategoryID : null;
?>
<h1 class="H HomepageTitle"><?php echo $this->data('Title').followButton($categoryID).watchButton($categoryID); ?></h1>
<div class="P PageDescription"><?php echo $this->description(); ?></div>
<?php
$this->fireEvent('AfterDescription');
$this->fireEvent('AfterPageTitle');
echo '<div class="PageControls Top">';
if ($this->data('EnableFollowingFilter')) {
    echo categoryFilters();
}
echo categorySorts();
echo '</div>';
$categories = $this->data('CategoryTree');
writeCategoryTable($categories);
?>
