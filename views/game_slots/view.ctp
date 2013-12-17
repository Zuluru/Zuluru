<?php
$this->Html->addCrumb (__('Game Slot', true));
$this->Html->addCrumb (__('View', true));
?>

<div class="gameSlots view">
<h2><?php  __('Game Slot');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(Configure::read('ui.field_cap')); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('fields/block', array('field' => $gameSlot['Field'], 'display_field' => 'long_name')); ?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Date'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date ($gameSlot['GameSlot']['game_date']); ?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Start'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->time ($gameSlot['GameSlot']['game_start']); ?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game End'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->time ($gameSlot['GameSlot']['display_game_end']); ?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count($gameSlot['Game']) < 2 ? 'Game' : 'Games'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			if (empty($gameSlot['Game'])) {
				__('Unassigned');
			} else {
				$games = array();
				foreach ($gameSlot['Game'] as $game) {
					Game::_readDependencies($game);
					$line = $this->Html->link($game['id'], array('controller' => 'games', 'action' => 'view', 'game' => $game['id'])) . ': ';

					if ($game['home_team'] === null) {
						$line .= $game['home_dependency'];
					} else {
						$line .= $this->element('teams/block', array('team' => $game['HomeTeam']));
					}

					$line .= __(' vs ', true);

					if ($game['away_team'] === null) {
						$line .= $game['away_dependency'];
					} else {
						$line .= $this->element('teams/block', array('team' => $game['AwayTeam']));
					}

					$line .= ' (' . $this->element('divisions/block', array('division' => $game['Division'], 'field' => 'full_league_name')) . ')';
					$games[] = $line;
				}
				echo implode('<br />', $games);
			}
			?>
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Game Slot', true)), array('action' => 'edit', 'slot' => $gameSlot['GameSlot']['id'])); ?> </li>
	</ul>
</div>

<?php if (!empty($gameSlot['DivisionGameslotAvailability'])):?>
<div class="related">
	<h3><?php __('Available to Divisions');?></h3>
	<table class="list">
	<?php
		$i = 0;
		foreach ($gameSlot['DivisionGameslotAvailability'] as $division):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->element('divisions/block', array('division' => $division['Division'], 'field' => 'full_league_name'));?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
