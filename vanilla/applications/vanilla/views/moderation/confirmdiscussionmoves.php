<?php if (!defined('APPLICATION')) exit(); ?>
    <h1><?php echo $this->data('Title'); ?></h1>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
    <div class="Wrap">
<?php
$CountAllowed = val('CountAllowed', $this->Data, 0);
$CountNotAllowed = val('CountNotAllowed', $this->Data, 0);
$CountCheckedDiscussions = val('CountCheckedDiscussions', $this->Data, 0);

if ($CountNotAllowed > 0) {
    echo wrap(sprintf(
        t('You do not have permission to move %1$s of the selected discussions.'),
        $CountNotAllowed
    ), 'p');

    echo wrap(sprintf(
        t('You are about to move %1$s of the %2$s of the selected discussions.'),
        $CountAllowed,
        $CountCheckedDiscussions
    ), 'p');
} else {
    echo wrap(sprintf(
        t('You are about to move %s.'),
        plural($CountCheckedDiscussions, '%s discussion', '%s discussions')
    ), 'p');
}
?>

    <?php
    echo '<div class="P">';
    echo '<div class="Category">';
    echo $this->Form->label('Category', 'CategoryID'), ' ';
    $options = [
    'Value' => $this->Data('CategoryID'),
    'IncludeNull' => true,
    'DiscussionType' => $this->Data('DiscussionType'),
    ];
    echo $this->Form->categoryDropDown('CategoryID', $options);
    echo '</div>';
    echo '</div>';

    echo '<div class="P">'.
        $this->Form->checkBox('RedirectLink', 'Leave a redirect link.', ['display' => 'before']).
        '</div>';
    ?>


<?php
echo '<div class="Buttons Buttons-Confirm">';
echo $this->Form->button('Cancel', ['type' => 'button', 'class' => 'Button Close']);
echo $this->Form->button('Move', ['type' => 'submit', 'class' => 'Button Primary Move']);
echo '</div>';
echo '</div>';

