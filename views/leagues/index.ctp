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
<table class="list">
<?php
$affiliate_id = null;

$seasons = array_unique(Set::extract('/League/long_season', $leagues));

foreach ($leagues as $league):
	$is_manager = $is_logged_in && in_array($league['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));

	if ($league['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $league['League']['affiliate_id'];
		$season = null;
		if (count($affiliates) > 1):
?>
	<tr>
		<th<?php if (!$is_admin) echo ' colspan="2"'; ?>>
			<h3 class="affiliate"><?php echo $league['League']['Affiliate']['name']; ?></h3>
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
		<?php endif; ?>
	</tr>
<?php
	endif;

	if ($league['League']['long_season'] != $season && count($seasons) > 1):
		$season = $league['League']['long_season'];
?>
	<tr>
		<th colspan="2"><?php echo $season; ?></th>
	</tr>
<?php
	endif;

	Configure::load("sport/{$league['League']['sport']}");

	// If the league has only a single division, we'll merge the details
	$collapse = (count($league['Division']) == 1);
	if ($collapse):
		$class = 'inner-border';
	else:
		$class = '';
?>
	<tr>
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
?>
	<tr>
		<td class="<?php echo $class;?>"><?php
			if ($collapse) {
				$name = $league['League']['name'];
				if (!empty($division['name'])) {
					$name .= " ({$division['name']})";
				}
				echo $this->Html->tag('strong',
					$this->element('leagues/block', array('league' => $league['League'], 'field' => 'name')));
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
<?php if ($is_logged_in && count($years) > 1): ?>
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
