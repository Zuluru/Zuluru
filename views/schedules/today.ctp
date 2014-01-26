<div id="gamestoday">
<p><?php echo (empty($games) ? __('No games today', true) :
		$this->Html->link(sprintf(__('%d games today', true), $games), array('action' => 'day', 'date' => date('Y-m-d')), array('target' => '_top'))); ?></p>
</div>
