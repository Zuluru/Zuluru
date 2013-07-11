<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Confirm Selections', true));
?>

<div class="schedules add">
<p>The following information will be used to create your games:</p>
<h3>What:</h3>
<p><?php
echo $desc;
if (array_key_exists('name', $this->data['Game'])) {
	printf(__(' (pool names are %s)', true), implode(', ', $this->data['Game']['name']));
}
?></p>
<?php
if (is_array($start_date)):
	asort($start_date);
?>
<h3>Rounds to be scheduled at:</h3>
<ol>
<?php foreach ($start_date as $round => $date): ?>
<li value="<?php echo $round; ?>"><?php echo $this->ZuluruTime->fulldatetime($date); ?></li>
<?php endforeach; ?>
</ol>
<?php else: ?>
<h3>Start date:</h3>
<p><?php echo $this->ZuluruTime->fulldate($start_date); ?></p>
<?php endif; ?>

<?php echo $this->element('schedules/exclude'); ?>

<h3>Publication:</h3>
<p>Games will <?php echo ($this->data['Game']['publish'] ? '' : 'NOT '); ?>be published.</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'finalize';
echo $this->element('hidden', array('fields' => $this->data));
?>

<?php echo $this->Form->end(__('Create games', true)); ?>

</div>