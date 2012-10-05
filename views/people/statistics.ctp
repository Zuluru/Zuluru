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
$affiliate_id = null;
foreach ($status_count as $status):
	if (count($affiliates) > 1 && $status['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $status['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $status['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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
$affiliate_id = null;
foreach ($group_count as $group):
	if (count($affiliates) > 1 && $group['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $group['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $group['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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
$affiliate_id = null;
foreach ($gender_count as $gender):
	if (count($affiliates) > 1 && $gender['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $gender['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $gender['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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
$affiliate_id = null;
foreach ($age_count as $age):
	if (count($affiliates) > 1 && $age['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $age['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $age['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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
$affiliate_id = null;
foreach ($started_count as $started):
	if (count($affiliates) > 1 && $started['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $started['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $started['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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
$affiliate_id = null;
foreach ($skill_count as $skill):
	if (count($affiliates) > 1 && $skill['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $skill['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $skill['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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
$affiliate_id = null;
foreach ($city_count as $city):
	if (count($affiliates) > 1 && $city['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $city['Affiliate']['id'];
		if ($total):
?>
		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $city['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

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