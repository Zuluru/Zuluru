<li><span class="name link_like"><?php echo $facility['name'] . ' ' . $field['num']; ?></span>
<div<?php if (!isset($expanded) || !$expanded) echo ' class="hidden"'; ?>>
<?php
foreach ($weeks as $key => $week) {
	foreach ($times as $key2 => $start) {
		if ($this->data['GameSlot']['length'] > 0) {
			$end = $this->ZuluruTime->time(strtotime("$week $start") + ($this->data['GameSlot']['length'] - $this->data['GameSlot']['buffer']) * 60);
		} else if (empty($this->data['GameSlot']['game_end'])) {
			$end = __('dark', true) . ' (' . $this->ZuluruTime->time(local_sunset_for_date($week)) . ')';
		} else {
			$end = $this->ZuluruTime->time($this->data['GameSlot']['game_end']);
		}
		echo $this->Form->input("GameSlot.Create.{$field['id']}.$key.$key2", array(
				'div' => false,
				'label' => "$week $start - $end",
				'type' => 'checkbox',
				'hiddenField' => false,
				'checked' => true,
		));
	}
}
?>
</div>
</li>
