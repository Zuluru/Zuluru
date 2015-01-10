<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Statistics', true));

$multi_sport = (count(Configure::read('options.sport')) > 1);
?>

<div class="people statistics">
<h2><?php __('People Statistics');?></h2>

<h3><?php __('People by Account Status'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Status'); ?></th>
			<th><?php __('People'); ?></th>
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

<h3><?php __('People by Account Class'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Class'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($group_count as $group):
	if (count($affiliates) > 1 && $group['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $group['Affiliate']['id'];
?>
		<tr>
			<th colspan="2">
				<h4 class="affiliate"><?php echo $group['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;
?>
		<tr>
			<td><?php
			if (empty($group['Group']['name'])) {
				__('None');
			} else {
				echo $group['Group']['name'];
			}
			?></td>
			<td><?php echo $group[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<h3><?php __('Players by Gender'); ?></h3>
<table class="list">
	<thead>
		<tr>
	<?php if ($multi_sport): ?>
			<th><?php __('Sport'); ?></th>
	<?php endif; ?>
			<th><?php __('Gender'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($gender_count as $gender):
	if (count($affiliates) > 1 && $gender['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $gender['Affiliate']['id'];
		if ($total):
?>
		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
?>
		<tr>
			<th colspan="<?php echo 2 + $multi_sport; ?>">
				<h4 class="affiliate"><?php echo $gender['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

	if ($multi_sport && $gender['Skill']['sport'] != $sport):
		$sport = $gender['Skill']['sport'];
		if ($total):
?>
		<tr>
			<td></td>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $gender[0]['count'];
?>
		<tr>
	<?php if ($multi_sport): ?>
			<td><?php echo $sport_title; $sport_title = ''; ?></td>
	<?php endif; ?>
			<td><?php echo $gender['Person']['gender']; ?></td>
			<td><?php echo $gender[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
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
	<?php if ($multi_sport): ?>
			<th><?php __('Sport'); ?></th>
	<?php endif; ?>
			<th><?php __('Age'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($age_count as $age):
	if (count($affiliates) > 1 && $age['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $age['Affiliate']['id'];
		if ($total):
?>
		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
?>
		<tr>
			<th colspan="<?php echo 2 + $multi_sport; ?>">
				<h4 class="affiliate"><?php echo $age['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

	if ($multi_sport && $age['Skill']['sport'] != $sport):
		$sport = $age['Skill']['sport'];
		if ($total):
?>
		<tr>
			<td></td>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $age[0]['count'];
?>
		<tr>
	<?php if ($multi_sport): ?>
			<td><?php echo $sport_title; $sport_title = ''; ?></td>
	<?php endif; ?>
			<td><?php echo $age[0]['age_bucket'] . ' to ' . ($age[0]['age_bucket'] + 4); ?></td>
			<td><?php echo $age[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
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
	<?php if ($multi_sport): ?>
			<th><?php __('Sport'); ?></th>
	<?php endif; ?>
			<th><?php __('Year'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($started_count as $started):
	if (count($affiliates) > 1 && $started['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $started['Affiliate']['id'];
		if ($total):
?>
		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
?>
		<tr>
			<th colspan="<?php echo 2 + $multi_sport; ?>">
				<h4 class="affiliate"><?php echo $started['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

	if ($multi_sport && $started['Skill']['sport'] != $sport):
		$sport = $started['Skill']['sport'];
		if ($total):
?>
		<tr>
			<td></td>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $started[0]['count'];
?>
		<tr>
	<?php if ($multi_sport): ?>
			<td><?php echo $sport_title; $sport_title = ''; ?></td>
	<?php endif; ?>
			<td><?php echo $started['Skill']['year_started']; ?></td>
			<td><?php echo $started[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
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
	<?php if ($multi_sport): ?>
			<th><?php __('Sport'); ?></th>
	<?php endif; ?>
			<th><?php __('Skill Level'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($skill_count as $skill):
	if (count($affiliates) > 1 && $skill['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $skill['Affiliate']['id'];
		if ($total):
?>
		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
?>
		<tr>
			<th colspan="<?php echo 2 + $multi_sport; ?>">
				<h4 class="affiliate"><?php echo $skill['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

	if ($multi_sport && $skill['Skill']['sport'] != $sport):
		$sport = $skill['Skill']['sport'];
		if ($total):
?>
		<tr>
			<td></td>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $skill[0]['count'];
?>
		<tr>
	<?php if ($multi_sport): ?>
			<td><?php echo $sport_title; $sport_title = ''; ?></td>
	<?php endif; ?>
			<td><?php echo $skill['Skill']['skill_level']; ?></td>
			<td><?php echo $skill[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
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
	<?php if ($multi_sport): ?>
			<th><?php __('Sport'); ?></th>
	<?php endif; ?>
			<th><?php __('City'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($city_count as $city):
	if (count($affiliates) > 1 && $city['Affiliate']['id'] != $affiliate_id):
		$affiliate_id = $city['Affiliate']['id'];
		if ($total):
?>
		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
?>
		<tr>
			<th colspan="<?php echo 2 + $multi_sport; ?>">
				<h4 class="affiliate"><?php echo $city['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

	if ($multi_sport && $city['Skill']['sport'] != $sport):
		$sport = $city['Skill']['sport'];
		if ($total):
?>
		<tr>
			<td></td>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $city[0]['count'];
?>
		<tr>
	<?php if ($multi_sport): ?>
			<td><?php echo $sport_title; $sport_title = ''; ?></td>
	<?php endif; ?>
			<td><?php
			if (empty($city['Person']['addr_city'])) {
				__('Unspecified');
			} else {
				echo $city['Person']['addr_city'];
			}
			?></td>
			<td><?php echo $city[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
<?php if ($multi_sport): ?>
			<td></td>
<?php endif; ?>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

</div>