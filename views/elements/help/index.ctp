<p>Help is available in the following areas:</p>
<ul>
	<li><?php echo $this->Html->link (__('People', true), array('controller' => 'help', 'action' => 'people')); ?></li>
<?php if (Configure::read('feature.registration')): ?>
	<li><?php echo $this->Html->link (__('Registration', true), array('controller' => 'help', 'action' => 'registration')); ?></li>
<?php endif; ?>
	<li><?php echo $this->Html->link (__('Teams', true), array('controller' => 'help', 'action' => 'teams')); ?></li>
	<li><?php echo $this->Html->link (__('Games', true), array('controller' => 'help', 'action' => 'games')); ?></li>
<?php if ($is_admin || $is_coordinator): ?>
	<li><?php echo $this->Html->link (__('Schedules', true), array('controller' => 'help', 'action' => 'schedules')); ?></li>
	<li><?php echo $this->Html->link (__('Leagues', true), array('controller' => 'help', 'action' => 'leagues')); ?></li>
<?php endif; ?>
<?php if ($is_admin): ?>
	<li><?php echo $this->Html->link (__('Fields', true), array('controller' => 'help', 'action' => 'fields')); ?></li>
	<li><?php echo $this->Html->link (__('Rules Engine', true), array('controller' => 'help', 'action' => 'rules')); ?></li>
<?php endif; ?>
</ul>
