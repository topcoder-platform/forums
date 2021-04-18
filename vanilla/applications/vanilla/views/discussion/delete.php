<?php if (!defined('APPLICATION')) exit(); ?>

<h1><?php echo $this->data('Title'); ?></h1>

<?php
echo $this->Form->open();
echo $this->Form->errors();

echo '<div class="P Message">'.sprintf(t('Are you sure you want to delete this %s?'), t('item')).'</div>';

echo '<div class="Buttons Buttons-Confirm">';
echo $this->Form->button('Cancel', ['type' => 'button', 'class' => 'Button Close']);
echo $this->Form->button('Delete', ['class' => 'Button Primary Delete']);
echo '</div>';
echo $this->Form->close();
?>
