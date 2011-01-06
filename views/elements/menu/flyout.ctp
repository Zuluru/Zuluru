<div class="cssplay_fly">
<ul>
<?php
foreach ($menu_items as $item)
{
	echo $this->element('menu/flyout_item', array('item' => $item));
}
?>
</ul>
</div>
