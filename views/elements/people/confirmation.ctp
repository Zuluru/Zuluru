<?php
if (!$is_player) {
	return;
}
$person = $this->UserCache->read('Person');
if (AppController::_isChild($person['birthdate'])) {
	$check = 6 * MONTH;
} else {
	$check = YEAR;
}
if (strtotime($person['updated']) + $check < time()):
?>
	<div id="ProfileConfirmation" title="<?php __('Confirmation'); ?>" style="display:none;">
		<?php
		// Making this a dialog pulls it out of the main #zuluru div, breaking formatting.
		// TODO: Switch zuluru id to class, to avoid creating an invalid DOM.
		?>
		<div id="zuluru">
			<?php
			if (empty($person['user_id'])) {
				$for = sprintf(__('profile information for %s', true), $person['first_name']);
			} else {
				$for = __('your profile information', true);
			}
			echo $this->Html->para(null, sprintf(__('It\'s been a while since you\'ve confirmed %s. It is important that we have accurate information for team-building and/or evaluation.', true), $for));
			echo $this->Html->para(null, __('Please either update or confirm the information below.', true));
			?>
			<dl>
			<?php foreach ($fields as $field): ?>
				<dt><?php __(Inflector::humanize($field)); ?>:</dt>
				<dd><?php
				switch ($field) {
					case 'height':
						echo $person[$field] . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm', true) : __('inches', true));
						break;

					default:
						echo $person[$field];
				}
				?></dd>
			<?php endforeach; ?>
			</dl>
		</div>
	</div>
<?php
	$edit = sprintf(__('Update %s', true), __('profile', true));
	$edit_link = Router::url(array('controller' => 'people', 'action' => 'edit', 'return' => true));
	$confirm = __('Confirm', true);
	$confirm_link = Router::url(array('controller' => 'people', 'action' => 'confirm'));
	$spinner = __('Confirming...', true) . ' ' . $this->ZuluruHtml->icon('spinner.gif');
	echo $this->Html->scriptBlock("
jQuery('#ProfileConfirmation').dialog({
	buttons: {
		'$edit': function() {
			window.location.href = '$edit_link';
		},
		'$confirm': function() {
			jQuery('#ProfileConfirmation').html('$spinner');
			jQuery.ajax({
				dataType: 'html',
				type: 'GET',
				success: function(data, textStatus) {
					jQuery('#ProfileConfirmation').dialog('close');
					jQuery('#temp_update').html(data);
				},
				error: function(message) {
					jQuery('#ProfileConfirmation').dialog('close');
					alert(message);
				},
				url: '$confirm_link'
			});
		}
	},
	width: 400,
	modal: true
});
	");
endif;
?>