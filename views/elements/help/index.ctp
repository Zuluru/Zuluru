<p><?php echo ZULURU; ?> has online help throughout the system, accessed by clicking the <?php echo $this->ZuluruHtml->icon('help_16.png');
?> icons on any page. These bits of documentation are also collected here in two forms: as guides for various user types and grouped by functional area.</p>
<h2>User Guides</h2>
<p>
<ul>
	<li><?php echo $this->Html->link (__('New Users', true), array('controller' => 'help', 'action' => 'guide', 'new_user')); ?></li>
	<li><?php echo $this->Html->link (__('Advanced Users', true), array('controller' => 'help', 'action' => 'guide', 'advanced')); ?></li>
	<li><?php echo $this->Html->link (__('Captains', true), array('controller' => 'help', 'action' => 'guide', 'captain')); ?></li>
<?php if ($is_admin || $is_coordinator): ?>
	<li><?php echo $this->Html->link (__('Coordinators', true), array('controller' => 'help', 'action' => 'guide', 'coordinator')); ?></li>
<?php endif; ?>
<?php if ($is_admin): ?>
	<li><?php __('Administrators'); ?></li>
	<ul>
		<li><?php echo $this->Html->link (__('Site Setup and Configuration', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'setup')); ?></li>
		<li><?php echo $this->Html->link (__('Player Management', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'players')); ?></li>
		<li><?php echo $this->Html->link (__('League Management', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'leagues')); ?></li>
		<li><?php echo $this->Html->link (sprintf(__('%s Management', true), Configure::read('ui.field_cap')), array('controller' => 'help', 'action' => 'guide', 'administrator', 'fields')); ?></li>
		<li><?php echo $this->Html->link (__('Registration', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'registration')); ?></li>
	</ul>
<?php endif; ?>
</ul>
</p>
<h2>Functional Areas</h2>
<p>
<ul>
	<li><?php echo $this->Html->link (__('People', true), array('controller' => 'help', 'action' => 'people')); ?></li>
<?php if (Configure::read('feature.registration')): ?>
<?php if ($is_admin): ?>
	<li><?php echo $this->Html->link (__('Events', true), array('controller' => 'help', 'action' => 'events')); ?></li>
<?php endif; ?>
	<li><?php echo $this->Html->link (__('Registration', true), array('controller' => 'help', 'action' => 'registration')); ?></li>
<?php endif; ?>
<?php if ($is_admin): ?>
	<li><?php echo $this->Html->link (__('Waivers', true), array('controller' => 'help', 'action' => 'waivers')); ?></li>
<?php endif; ?>
	<li><?php echo $this->Html->link (__('Teams', true), array('controller' => 'help', 'action' => 'teams')); ?></li>
	<li><?php echo $this->Html->link (__('Games', true), array('controller' => 'help', 'action' => 'games')); ?></li>
<?php if ($is_admin || $is_coordinator): ?>
	<li><?php echo $this->Html->link (__('Schedules', true), array('controller' => 'help', 'action' => 'schedules')); ?></li>
	<li><?php echo $this->Html->link (__('Leagues', true), array('controller' => 'help', 'action' => 'leagues')) .
		' ' . __('and', true) . ' ' .
		$this->Html->link (__('Divisions', true), array('controller' => 'help', 'action' => 'divisions')); ?></li>
<?php endif; ?>
<?php if ($is_admin): ?>
	<li><?php echo $this->Html->link (__('Facilities', true), array('controller' => 'help', 'action' => 'facilities')) .
		' ' . __('and', true) . ' ' .
		$this->Html->link (__(Configure::read('ui.fields_cap'), true), array('controller' => 'help', 'action' => 'fields')); ?></li>
	<li><?php echo $this->Html->link (__('Rules Engine', true), array('controller' => 'help', 'action' => 'rules')); ?></li>
	<li><?php echo $this->Html->link (__('Configuration', true), array('controller' => 'help', 'action' => 'settings')); ?></li>
<?php endif; ?>
</ul>
</p>
