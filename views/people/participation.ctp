<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Participation Statistics', true));
?>

<div class="people participation">
<h2><?php echo __('Participation Statistics', true);?></h2>

<?php
if (!isset($participation)):
	echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
	$years = array_combine(range(date('Y'), $min), range(date('Y'), $min));
	echo $this->Form->input('start', array(
			'label' => __('Include details starting in', true),
			'options' => $years,
	));
	echo $this->Form->input('end', array(
			'label' => __('Up to and including', true),
			'options' => $years,
	));
	echo $this->Form->input('download', array(
			'type' => 'checkbox',
	));
	echo $this->Html->para(null, __('Note that this report is time- and memory-intensive, and multi-year reports may cause it to crash, depending on php.ini settings.', true));
	echo $this->Form->end(__('Submit', true));
else:
?>

<table class="list">
	<tr>
		<th><?php __('User ID'); ?></th>
		<th><?php __('First Name'); ?></th>
		<th><?php __('Last Name'); ?></th>
		<th><?php __('Gender'); ?></th>
		<th><?php __('Skill Level'); ?></th>
		<th><?php __('Birthdate'); ?></th>
		<th><?php __('Year Started'); ?></th>
		<th><?php __('City'); ?></th>
		<?php for ($year = $this->data['start']; $year <= $this->data['end']; ++ $year): ?>
		<?php foreach ($seasons_found as $name => $season): ?>
		<?php if ($season['season']): ?>
		<th><?php echo $year . ' ' . __($name, true) . ' ' . __('captain', true); ?></th>
		<th><?php echo $year . ' ' . __($name, true) . ' ' . __('player', true); ?></th>
		<?php endif; ?>
		<?php if ($season['tournament']): ?>
		<th><?php echo $year . ' ' . __($name, true) . ' ' . __('tournament', true) . ' ' . __('captain', true); ?></th>
		<th><?php echo $year . ' ' . __($name, true) . ' ' . __('tournament', true) . ' ' . __('player', true); ?></th>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endfor; ?>
		<?php foreach ($event_names as $event): ?>
		<th><?php echo $event; ?></th>
		<?php endforeach; ?>
	</tr>
<?php
$i = 0;
foreach ($participation as $person):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'id')); ?></td>
		<td><?php echo $person['Person']['first_name']; ?></td>
		<td><?php echo $person['Person']['last_name']; ?></td>
		<td><?php echo $person['Person']['gender']; ?></td>
		<td><?php echo $person['Person']['skill_level']; ?></td>
		<td><?php echo $person['Person']['birthdate']; ?></td>
		<td><?php echo $person['Person']['year_started']; ?></td>
		<td><?php echo $person['Person']['addr_city']; ?></td>
		<?php for ($year = $this->data['start']; $year <= $this->data['end']; ++ $year): ?>
		<?php foreach ($seasons_found as $name => $season): ?>
		<?php if ($season['season']): ?>
		<td><?php echo $person['Division'][$year][$name]['season']['captain']; ?></td>
		<td><?php echo $person['Division'][$year][$name]['season']['player']; ?></td>
		<?php endif; ?>
		<?php if ($season['tournament']): ?>
		<td><?php echo $person['Division'][$year][$name]['tournament']['captain']; ?></td>
		<td><?php echo $person['Division'][$year][$name]['tournament']['player']; ?></td>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endfor; ?>
		<?php foreach (array_keys($event_names) as $event): ?>
		<td><?php echo array_key_exists($event, $person['Event']) ? 1 : '&nbsp;'; ?></td>
		<?php endforeach; ?>
	</tr>
<?php endforeach; ?>
</table>
<?php
endif;
?>
</div>
