<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Statistics', true));
?>

<div class="people statistics">
<h2><?php __('Player Statistics');?></h2>

<h3><?php __('Players by Account Status'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Status'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($status_count as $status):
	$total += $status[0]['count'];
?>
		<tr>
			<td><?php echo $status['Person']['status']; ?></td>
			<td><?php echo $status[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>

<h3><?php __('Players by Account Class'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Class'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($group_count as $group):
	$total += $group[0]['count'];
?>
		<tr>
			<td><?php echo $groups[$group['Person']['group_id']]; ?></td>
			<td><?php echo $group[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>

<h3><?php __('Players by Gender'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Gender'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($gender_count as $gender):
	$total += $gender[0]['count'];
?>
		<tr>
			<td><?php echo $gender['Person']['gender']; ?></td>
			<td><?php echo $gender[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>

<?php if (Configure::read('profile.birthdate')): ?>
<h3><?php __('Players by Age'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Age'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($age_count as $age):
	$total += $age[0]['count'];
?>
<tr>
			<td><?php echo $age[0]['age_bucket'] . ' to ' . ($age[0]['age_bucket'] + 4); ?></td>
			<td><?php echo $age[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<?php if (Configure::read('profile.year_started')): ?>
<h3><?php __('Players by Year Started'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Year'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($started_count as $started):
	$total += $started[0]['count'];
?>
		<tr>
			<td><?php echo $started['Person']['year_started']; ?></td>
			<td><?php echo $started[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<?php if (Configure::read('profile.skill_level')): ?>
<h3><?php __('Players by Skill Level'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Skill Level'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($skill_count as $skill):
	$total += $skill[0]['count'];
?>
		<tr>
			<td><?php echo $skill['Person']['skill_level']; ?></td>
			<td><?php echo $skill[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<?php if (Configure::read('profile.addr_city')): ?>
<h3><?php __('Players by City'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('City'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($city_count as $city):
	$total += $city[0]['count'];
?>
		<tr>
			<td><?php echo $city['Person']['addr_city']; ?></td>
			<td><?php echo $city[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

</div>