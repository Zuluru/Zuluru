<?php if (!empty($people)): ?>
<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Coordinators'); ?></dt>
<dd<?php if ($i++ % 2 == 0) echo $class;?>>
<?php
$coordinators = array();
foreach ($people as $person) {
	$coordinator = $this->element('people/block', compact('person'));
	if ($is_admin || $is_manager) {
		$coordinator .= '&nbsp;' .
			$this->Html->tag('span',
				$this->ZuluruHtml->iconLink('coordinator_delete_24.png',
					array('controller' => 'divisions', 'action' => 'remove_coordinator', 'division' => $division['id'], 'person' => $person['id']),
					array('alt' => __('Remove', true), 'title' => __('Remove', true))),
				array('class' => 'actions'));
	}
	$coordinators[] = $coordinator;
}
echo implode ('<br />', $coordinators);
?></dd>
<?php endif; ?>
<?php if (!empty ($division['coord_list'])) : ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Coordinator Email List'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link ($division['coord_list'], "mailto:{$division['coord_list']}"); ?>

	</dd>
<?php endif; ?>
<?php if (!empty ($division['capt_list'])) : ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Captain Email List'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link ($division['capt_list'], "mailto:{$division['capt_list']}"); ?>

	</dd>
<?php endif; ?>
<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Status'); ?></dt>
<dd<?php if ($i++ % 2 == 0) echo $class;?>>
	<?php __($division['is_open'] ? 'Open' : 'Closed'); ?>

</dd>
<?php if ($division['open'] != '0000-00-00'): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('First Game'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->ZuluruTime->date($division['open']); ?>

	</dd>
<?php endif; ?>
<?php if ($division['close'] != '0000-00-00'): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Last Game'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->ZuluruTime->date($division['close']); ?>

	</dd>
<?php endif; ?>
<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Deadline'); ?></dt>
<dd<?php if ($i++ % 2 == 0) echo $class;?>>
	<?php echo $this->ZuluruTime->date(Division::rosterDeadline($division)); ?>

</dd>
<?php if (!empty ($division['Day'])): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count ($division['Day']) == 1 ? 'Day' : 'Days'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		$days = array();
		foreach ($division['Day'] as $day) {
			$days[] = __($day['name'], true);
		}
		echo implode (', ', $days);
		?>

	</dd>
<?php endif; ?>
<?php if (!empty ($division['ratio'])): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender Ratio'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php __(Inflector::Humanize ($division['ratio'])); ?>

	</dd>
<?php endif; ?>
<?php if ($is_admin || $is_manager): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Rule'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->tag('pre', $division['roster_rule'] . '&nbsp;'); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Method'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo Configure::read("options.roster_methods.{$division['roster_method']}"); ?>

	</dd>
<?php endif; ?>
<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Schedule Type'); ?></dt>
<dd<?php if ($i++ % 2 == 0) echo $class;?>>
	<?php
	__(Inflector::Humanize ($division['schedule_type']));
	echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', 'schedule_type', $division['schedule_type']));
	?>

</dd>
<?php
$fields = $league_obj->schedulingFields($is_admin || $is_manager, $is_coordinator);
foreach ($fields as $field => $options):
?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __($options['label']); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $division[$field];
		echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', $field));
		?>

	</dd>
<?php endforeach; ?>
<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rating Calculator'); ?></dt>
<dd<?php if ($i++ % 2 == 0) echo $class;?>>
	<?php
	__(Inflector::Humanize ($division['rating_calculator']));
	echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', 'rating_calculator', $division['rating_calculator']));
	?>

</dd>
<?php if ($is_admin || $is_manager || $is_coordinator): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Exclude Teams'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		__($division['exclude_teams'] ? 'Yes' : 'No');
		echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', 'exclude_teams'));
		?>

	</dd>
	<?php if ($division['email_after'] != 0): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Scoring reminder delay'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $division['email_after'] . ' ' . __('hours', true); ?>

		</dd>
	<?php endif; ?>
	<?php if ($division['finalize_after'] != 0): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game finalization delay'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $division['finalize_after'] . ' ' . __('hours', true); ?>

		</dd>
	<?php endif; ?>
<?php endif; ?>
<?php if (Configure::read('scoring.allstars')): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('All-star nominations'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php __(Inflector::Humanize ($division['allstars'])); ?>

	</dd>
	<?php if ($division['allstars'] != 'never'): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('All-star nominations from'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php __(Inflector::Humanize ($division['allstars_from'])); ?>

	</dd>
	<?php endif; ?>
<?php endif; ?>
