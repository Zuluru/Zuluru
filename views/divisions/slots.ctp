<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb (__('Division Field Availability Report', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
?>

<div class="divisions slots">
<h2><?php echo __('Division Field Availability Report', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<p>Select a date below on which to view all available gameslots:</p>
<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
echo $this->Form->input('date', array(
		'label' => false,
		'options' => $dates,
));
echo $this->Js->submit(__('View', true), array('url' => Router::normalize($this->here), 'update' => '#SlotResults'));
echo $this->Form->end();
?>

<div id="SlotResults">
<?php endif; ?>

<?php if (isset ($slots)): ?>
<p><?php echo $this->ZuluruTime->fulldate($date); ?></p>
<table class="list">
	<tr>
		<th>ID</th>
		<th>Field</th>
		<th>Game</th>
		<th>Home</th>
		<th>Away</th>
		<th>Field Region</th>
<?php if (Configure::read('feature.region_preference')): ?>
		<th>Home Pref</th>
<?php endif; ?>
	</tr>
<?php $unused = 0; ?>
<?php foreach ($slots as $slot): ?>
	<tr>
		<td><?php __($slot['GameSlot']['id']); ?></td>
		<td><?php echo $this->Html->link ($slot['Field']['long_code'],
				array('controller' => 'fields', 'action' => 'view', 'field' => $slot['Field']['id']),
				array('title' => $slot['Field']['long_name'])); ?></td>
<?php if (!$slot['Game']['id']): ?>
<?php ++$unused; ?>
		<td colspan="3">---- <?php __('field open'); ?> ----</td>
<?php else: ?>
		<td><?php echo $this->Html->link ($slot['Game']['id'],
				array('controller' => 'games', 'action' => 'view', 'game' => $slot['Game']['id'])); ?></td>
		<td><?php echo $this->ZuluruHtml->link ($slot['Game']['HomeTeam']['name'],
				array('controller' => 'teams', 'action' => 'view', 'team' => $slot['Game']['HomeTeam']['id']),
				array('max_length' => 16)); ?></td>
		<td><?php echo $this->ZuluruHtml->link ($slot['Game']['AwayTeam']['name'],
				array('controller' => 'teams', 'action' => 'view', 'team' => $slot['Game']['AwayTeam']['id']),
				array('max_length' => 16)); ?></td>
<?php endif; ?>
		<td><?php __($slot['Field']['Facility']['Region']['name']); ?></td>
<?php if (Configure::read('feature.region_preference')): ?>
		<td><?php if ($slot['Game']['id']) __($slot['Game']['HomeTeam']['region_preference']); ?></td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>
</table>
<?php printf (__('There are %s fields available for use this week, currently %s of these are unused.', true), count($slots), $unused); ?>
<?php endif; ?>

<?php if (!$this->params['isAjax']): ?>
</div>

</div>
<?php endif; ?>
