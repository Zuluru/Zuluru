<?php
$this->Html->addCrumb (__('Badges', true));
if ($badge['Badge']['category'] == 'assigned') {
	$this->Html->addCrumb (__('Assign', true));
} else {
	$this->Html->addCrumb (__('Nominate', true));
}
if (count($affiliates) > 1) {
	$this->Html->addCrumb ($badge['Affiliate']['name']);
}
$this->Html->addCrumb ($badge['Badge']['name']);
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="badges form">
<?php echo $this->Form->create('BadgesPerson', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php
		if (count($affiliates) > 1) {
			echo "{$badge['Affiliate']['name']} ";
		}
 		if ($badge['Badge']['category'] == 'assigned') {
 			echo sprintf(__('Assign "%s" Badge to %s', true), $badge['Badge']['name'], $person['Person']['full_name']);
 		} else {
 			echo sprintf(__('Nominate %s for the "%s" Badge', true), $person['Person']['full_name'], $badge['Badge']['name']);
 		}
 		?></legend>
		<p><?php echo $this->ZuluruHtml->icon($badge['Badge']['icon'] . '_64.png') . ' ' . $badge['Badge']['description']; ?></p>
	<?php if ($badge['Badge']['category'] == 'nominated'): ?>
	<p>Most badges are a sign of prestige, and are not simply granted to everyone. Here you can provide a reason why this person deserves this badge, which will be provided to the administrator to aid their decision. If approved, this reason will also be visible to anyone logged into the system as part of the nominee's permanent record.</p>
	<?php elseif ($badge['Badge']['visibility'] == BADGE_VISIBILITY_ADMIN): ?>
		<p>This badge is only visible to admins. If you add a reason here, it will be visible to other admins to explain why the badge was assigned. This is typically used in the case of "red flagging" or similar situations.</p>
	<?php endif; ?>

	<?php
		echo $this->Form->input('reason', array(
				'cols' => 70,
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
