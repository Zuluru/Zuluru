<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('List', true));
if (!empty($sport)) {
	$this->Html->addCrumb (__(Inflector::humanize($sport), true));
}
?>

<div class="leagues index">
<h2><?php __('Leagues');?></h2>
<?php if (empty($leagues)): ?>
<p class="warning-message">There are no leagues currently active. Please check back periodically for updates<?php if (!empty($years)) echo ' or use the links below to review historical information'; ?>.</p>
<?php else: ?>
<?php
$sports = array_unique(Set::extract('/League/sport', $leagues));
echo $this->element('selector', array(
		'title' => 'Sport',
		'options' => $sports,
));

$seasons = array_unique(Set::extract('/League/long_season', $leagues));
echo $this->element('selector', array(
		'title' => 'Season',
		'options' => $seasons,
));

$days = Set::extract('/Division/Day[id!=]', $leagues);
$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
ksort($days);
echo $this->element('selector', array(
		'title' => 'Day',
		'options' => $days,
));

?>
<table class="list">
<?php
$affiliate_id = null;

foreach ($leagues as $league):
	$is_manager = $is_logged_in && in_array($league['League']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));

	if ($league['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $league['League']['affiliate_id'];
		$sport = null;
		$affiliate_sports = array_unique(Set::extract("/League[affiliate_id=$affiliate_id]/sport", $leagues));
		$affiliate_seasons = array_unique(Set::extract("/League[affiliate_id=$affiliate_id]/long_season", $leagues));
		$affiliate_days = array_unique(Set::extract("/League[affiliate_id=$affiliate_id]/../Division/Day/name", $leagues));
		if (count($affiliates) > 1):
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $affiliate_sports)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $affiliate_seasons)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $affiliate_days)); ?>">
		<th<?php if (!$is_admin) echo ' colspan="2"'; ?>>
			<h3 class="affiliate"><?php echo $league['Affiliate']['name']; ?></h3>
		</th>
			<?php if ($is_admin): ?>
		<th class="actions">
			<?php
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('controller' => 'affiliates', 'action' => 'edit', 'affiliate' => $league['League']['affiliate_id'], 'return' => true),
					array('alt' => __('Edit', true), 'title' => __('Edit Affiliate', true)));
			?>
		</th>
			<?php endif; ?>
	</tr>
<?php
		endif;
	endif;

	if ($league['League']['sport'] != $sport):
		$sport = $league['League']['sport'];
		Configure::load("sport/$sport");
		$season = null;
		if (count($sports) > 1):
			$league_seasons = array_unique(Set::extract("/League[affiliate_id=$affiliate_id][sport=$sport]/long_season", $leagues));
			$league_days = array_unique(Set::extract("/League[affiliate_id=$affiliate_id][sport=$sport]/../Division/Day/name", $leagues));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $sport)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $league_seasons)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $league_days)); ?>">
		<th colspan="2"><?php echo Inflector::humanize($sport); ?></th>
	</tr>
<?php
		endif;
	endif;

	if ($league['League']['long_season'] != $season):
		$season = $league['League']['long_season'];
		if (count($seasons) > 1):
			$season_days = array_unique(Set::extract("/League[affiliate_id=$affiliate_id][sport=$sport][long_season=$season]/../Division/Day/name", $leagues));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $sport)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $season)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $season_days)); ?>">
		<th colspan="2"><?php echo $season; ?></th>
	</tr>
<?php
		endif;
	endif;

	// If the league has only a single division, we'll merge the details
	$collapse = (count($league['Division']) == 1);
	if ($collapse):
		$class = 'inner-border';
		$is_coordinator = in_array($league['Division'][0]['id'], $this->UserCache->read('DivisionIDs'));
	else:
		$class = '';
		$division_days = array_unique(Set::extract('/Division/Day/name', $league));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $sport)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $season)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $division_days)); ?>">
		<td<?php if (!($is_admin || $is_manager)) echo ' colspan="2"'; ?> class="inner-border">
			<strong><?php echo $this->element('leagues/block', array('league' => $league['League'], 'field' => 'name')); ?></strong>
		</td>
		<?php if ($is_admin || $is_manager): ?>
		<td class="actions inner-border"><?php echo $this->element('leagues/actions', compact('league')); ?></td>
		<?php endif; ?>
	</tr>
<?php
	endif;

	foreach ($league['Division'] as $division):
		$division_days = array_unique(Set::extract('/Day/name', $division));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $sport)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $season)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $division_days)); ?>">
		<td class="<?php echo $class;?>"><?php
			if ($collapse) {
				$name = $league['League']['name'];
				if (!empty($division['name'])) {
					$name .= " {$division['name']}";
				}
				echo $this->Html->tag('strong',
					$this->element('leagues/block', array('league' => $league['League'], 'name' => $name)));
			} else {
				echo '&nbsp;&nbsp;&nbsp;&nbsp;' .
					$this->element('divisions/block', array('division' => $division));
			}
		?></td>
		<td class="actions<?php echo " $class";?>">
			<?php echo $this->element('divisions/actions', compact('division', 'league', 'is_manager', 'collapse')); ?>
		</td>
	</tr>
<?php
	endforeach;
endforeach;
?>
</table>
<?php endif; ?>
</div>
<?php if ($is_logged_in && !empty($years)): ?>
<div class="actions">
	<ul>
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year[0]['year'], array('affiliate' => $affiliate, 'sport' => $sport, 'year' => $year[0]['year'])));
}
?>

	</ul>
</div>
<?php endif; ?>
