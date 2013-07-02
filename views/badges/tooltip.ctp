<h2><?php
echo $this->ZuluruHtml->icon("{$badge['Badge']['icon']}_64.png",
		array('class' => 'profile_photo')
);
echo $badge['Badge']['name'];
?></h2>
<p><?php echo $badge['Badge']['description']; ?></p>
<dl>
<dt><?php __('Awarded to'); ?></dt>
<dd><?php echo count($badge['Person']) . ' ' . __('people', true); ?></dd>
</dl>

<p><?php
echo $this->Html->link(__('Details', true), array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['Badge']['id']));
?></p>