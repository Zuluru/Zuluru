<div class="install">
	<h2><?php echo $title_for_layout; ?></h2>

	<p>You may want to edit the contents of
	<code><?php	echo CONFIGS; ?>schema/data/regions_data.php</code>
	before proceeding. There is currently no way in Zuluru to edit this list once installed
	(you could make changes directly in the database later on, but the easiest way is to edit this file now).</p>
	<p>If you are not located in Canada or the United States, you will want to edit the contents of
	<code><?php	echo CONFIGS; ?>schema/data/countries_data.php</code> and
	<code><?php	echo CONFIGS; ?>schema/data/provinces_data.php</code>
	before proceeding.</p>
	<p>DO NOT edit any of the other data files, as doing so may break Zuluru functionality.</p>
	<?php
		echo $this->Html->link(__('Click here to build your database', true), array(
			'plugin' => 'install',
			'controller' => 'install',
			'action' => 'data',
			'run' => 1,
		));
	?>
</div>
