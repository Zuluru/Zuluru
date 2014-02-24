<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Options', true));
?>

<?php
$deposit = Set::extract('/Price[allow_deposit=1]', $event);
$deposit = !empty($deposit);
?>
<div class="registrations form">
<h2><?php echo __('Registration Options', true) . ': ' . $event['Event']['name']; ?></h2>
<p><?php __('Please select your preference from among the following options. If your desired option is not available, there will be an explanation of why, and possibly also a link to what you can do to resolve that.'); ?></p>
<div class="related">
	<table class="multi_row_list">
	<tr>
		<th><?php __('Description'); ?></th>
		<th><?php __('Registration Opens'); ?></th>
		<th><?php __('Registration Closes'); ?></th>
		<th><?php __('Cost'); ?></th>
		<?php if ($deposit): ?>
		<th><?php __('Deposit'); ?></th>
		<?php endif; ?>
		<th><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($event['Price'] as $key => $price):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $price['name'];?></td>
		<td><?php echo $this->ZuluruTime->DateTime ($price['open']); ?></td>
		<td><?php echo $this->ZuluruTime->DateTime ($price['close']); ?></td>
		<td><?php
		$cost = $price['cost'] + $price['tax1'] + $price['tax2'];
		if ($cost > 0) {
			echo '$' . $cost;
		} else {
			echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
		}
		?></td>
		<?php if ($deposit): ?>
		<td><?php
		if ($price['allow_deposit']) {
			echo '$' . $price['minimum_deposit'];
			if (!$price['fixed_deposit']) {
				echo '+';
			}
		} else {
			__('N/A');
		}
		?></td>
		<?php endif; ?>
		<td class="actions"><?php
		if ($rule_allowed[$key]['allowed']) {
			echo $this->Html->link(__('Register now!', true),
					array('controller' => 'registrations', 'action' => 'register', 'event' => $event['Event']['id'], 'option' => $price['id']),
					array('title' => __('Register for ', true) . $event['Event']['name'] . ' ' . $price['name'])
			);
		}
		?></td>
	</tr>
	<?php if (!empty($price['description'])): ?>
	<tr<?php echo $class;?>>
		<td colspan="<?php echo 5 + $deposit; ?>"><?php echo $price['description']; ?></td>
	</tr>
	<?php endif; ?>
	<tr<?php echo $class;?>>
		<td colspan="<?php echo 5 + $deposit; ?>"><?php echo $rule_allowed[$key]['message']; ?></td>
	</tr>
	<?php endforeach; ?>
	</table>
</div>
</div>
