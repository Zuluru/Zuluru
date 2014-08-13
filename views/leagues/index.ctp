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
		$season = null;
		if (count($affiliates) > 1):
?>
	<tr>
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

	if ($league['League']['long_season'] != $season && count($seasons) > 1):
		$season = $league['League']['long_season'];
		$days = array_unique(Set::extract("/League[long_season=$season]/../Division/Day/name", $leagues));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $season)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $days)); ?>">
		<th colspan="2"><?php echo $season; ?></th>
	</tr>
<?php
	endif;

	Configure::load("sport/{$league['League']['sport']}");

	// If the league has only a single division, we'll merge the details
	$collapse = (count($league['Division']) == 1);
	if ($collapse):
		$class = 'inner-border';
		$is_coordinator = in_array($league['Division'][0]['id'], $this->UserCache->read('DivisionIDs'));
	else:
		$class = '';
		$days = array_unique(Set::extract('/Division/Day/name', $league));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $season)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $days)); ?>">
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
		$days = array_unique(Set::extract('/Day/name', $division));
?>
	<tr class="<?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $season)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $days)); ?>">
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
