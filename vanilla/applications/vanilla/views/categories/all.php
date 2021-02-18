<?php if (!defined('APPLICATION')) exit();
include_once $this->fetchViewLocation('helper_functions', 'categories');
$title = $this->data('Title');
if (!is_null($this->Category)) {
    $title .= followButton($this->Category->CategoryID);
    $title .= watchButton($this->Category->CategoryID);
}
echo '<h1 class="H HomepageTitle">'.$title.'</h1>';
// if ($description = $this->description()) {
    //echo wrap($description, 'div', ['class' => 'P PageDescription']);
// }
$this->fireEvent('AfterPageTitle');
echo '<div class="PageControls Top">';
if ($this->data('EnableFollowingFilter')) {
    echo categoryFilters();
}
if (!is_null($this->Category) && $this->Category->DisplayAs == 'Discussions') {
    echo categorySorts();
}
echo '</div>';
$categories = $this->data('CategoryTree');
writeCategoryList($categories, 1);
