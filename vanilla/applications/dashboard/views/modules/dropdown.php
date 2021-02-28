<?php
/** @var DropdownModule $dropdown */
$dropdown = $this;
$trigger = $dropdown->getTrigger();
?><span class="ToggleFlyout <?php echo $dropdown->getCssClass(); ?>"><?php
    if (($trigger['type'] ?? '') === 'button') :
    ?><span class="Button-Options">
        <span class="OptionsTitle" title="<?php echo t('Options'); ?>">
            <?php echo $trigger['text']; ?>
        </span>
        <?php echo sprite('SpFlyoutHandle', 'Arrow'); ?>
    </span>
    <?php else :
        $text = $trigger['text'] ?? false;
        $url = $trigger['url'] ?? false;
        $icon = $trigger['icon'] ?? false;
        $cssClass = $trigger['cssClass'] ?? false;
        $attributes = $trigger['attributes'] ?? false;
        $alert = !empty($dropdown->data('DashboardCount', '')) ? wrap($dropdown->data('DashboardCount', ''), 'span', ['class' => 'Alert']) : '';
        echo anchor($icon.$text.$alert, $url, $cssClass, $attributes);
    endif; ?>
    <ul class="Flyout MenuItems list-reset <?php echo $dropdown->getListCssClass(); ?>" role="menu" aria-labelledby="<?php echo $dropdown->getTriggerId(); ?>">
        <?php foreach($dropdown->getItems() as $item) {
            if (($item['type'] ?? '') == 'group') { ?>
                <li role="presentation" class="dropdown-header <?php echo $item['cssClass'] ?? ''; ?>">
                    <?php if ($iIcon = ($item['icon'] ?? false)) {
                        echo icon($iIcon);
                    }
                    echo $item['text'] ?? '';
                    if ($iBadge = ($item['badge'] ?? false)) {
                        echo badge($iBadge);
                    } ?>
                </li>
            <?php } ?>
            <?php  if (($item['type'] ?? '') == 'link') { ?>
                <li role="presentation" <?php
                    if ($ilistItemCssClass = ($item['listItemCssClass'] ?? false) || empty($item['icon'])) {
                       ?>class="<?php
                        echo trim($item['listItemCssClass'].(empty($item['icon']) ? ' no-icon' : '')); ?>"<?php
                    } ?>>
                    <?php
                     if(($item['url'] ?? '') == '') {?>
                     <div role="menuitem" class="dropdown-menu-link <?php echo  $item['cssClass'] ?? ''; ?>" tabindex="-1" <?php echo attribute($item['attributes'] ?? []) ?>>
                     <?php
                         echo $item['text']??'';
                      ?>
                     </div>
                         <?php  } else {
                    ?>
                    <a role="menuitem" class="dropdown-menu-link <?php echo  $item['cssClass'] ?? ''; ?>" tabindex="-1" href="<?php echo url($item['url'] ?? ''); ?>" <?php echo attribute($item['attributes'] ?? []) ?>><?php
                        if ($iIcon = ($item['icon'] ?? false)) {
                            echo icon($iIcon);
                        }
                        echo $item['text'] ?? '';
                        if ($iBadge = ($item['badge'] ?? false)) {
                            echo ' '.wrap($iBadge, 'span', ['class' => 'Alert']);
                        }
                        ?></a>
                    <?php } ?>
                </li>
            <?php }
            if (($item['type'] ?? '') == 'divider') { ?>
                <li role="presentation" <?php if ($iCssClass = ($item['cssClass'] ?? false)) { ?> class="<?php echo $iCssClass; ?>"<?php } ?>>
                    <hr />
                </li>
            <?php }
        } ?>
    </ul>
</span>
