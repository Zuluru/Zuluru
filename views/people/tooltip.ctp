<h2><?php
echo $person['full_name'];
echo $this->element('people/player_photo', array('person' => $person, 'photo' => $photo));
?></h2>
<?php
$view_contact = $is_me || $is_admin || $is_manager || $is_coordinator || $is_captain || $is_my_captain || $is_my_coordinator || $is_division_captain;
$has_visible_contact = false;
$lines = array();

if (!empty($person['email']) &&
	($view_contact || ($is_logged_in && $person['publish_email'])))
{
	$has_visible_contact = true;
	$lines[] = $this->Html->link ($person['email'], "mailto:{$person['email']}");
}
if (!empty($person['alternate_email']) &&
	($view_contact || ($is_logged_in && $person['publish_alternate_email'])))
{
	$has_visible_contact = true;
	$lines[] = $this->Html->link ($person['alternate_email'], "mailto:{$person['alternate_email']}");
}
if (!empty($person['home_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_home_phone'])))
{
	$has_visible_contact = true;
	$lines[] = $person['home_phone'] . ' (' . __('home', true) . ')';
}
if (!empty($person['work_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_work_phone'])))
{
	$has_visible_contact = true;
	$line = $person['work_phone'];
	if (!empty($person['work_ext'])) {
		$line .= ' x' . $person['work_ext'];
	}
	$line .= ' (' . __('work', true) . ')';
	$lines[] = $line;
}
if (!empty($person['mobile_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_mobile_phone'])))
{
	$has_visible_contact = true;
	$lines[] = $person['mobile_phone'] . ' (' . __('mobile', true) . ')';
}

echo implode($this->Html->tag('br'), $lines);

if ($is_logged_in) {
	$links = array();
	if ($has_visible_contact) {
		$links[] = $this->Html->link(__('VCF', true), array('action' => 'vcf', 'person' => $person['id']));
	}
	if (Configure::read('feature.annotations')) {
		$links[] = $this->Html->link(__('Add Note', true), array('action' => 'note', 'person' => $person['id']));
	}
	if (!empty($links)) {
		echo $this->Html->tag('br') . implode(' / ', $links);
	}
}

if ($is_logged_in && Configure::read('feature.badges') && !empty($badges['Badge'])) {
	echo $this->Html->tag('br');
	foreach ($badges['Badge'] as $badge) {
		echo $this->ZuluruHtml->iconLink("{$badge['icon']}_48.png", array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['id']),
			array('alt' => $badge['name'], 'title' => $badge['description']));
	}
}

if ($view_contact) {
	if (AppController::_isChild($person['birthdate'])) {
		$related_to = $this->UserCache->read('RelatedTo', $person['id']);
		if (!empty($related_to)) {
?>
	<h3><?php __('Contacts');?></h3>
	<?php
			$lines = array();
			foreach ($related_to as $relative){
				if ($relative['PeoplePerson']['approved']){
					$lines[] = $this->Html->tag('strong', $this->Html->link ($relative['Relative']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $relative['Relative']['id'])));
					if (!empty($relative['Relative']['email'])) {
						$lines[] = $this->Html->link ($relative['Relative']['email'], "mailto:{$relative['Relative']['email']}");
					}
					if (!empty($relative['Relative']['alternate_email'])) {
						$lines[] = $this->Html->link ($relative['Relative']['alternate_email'], "mailto:{$relative['Relative']['alternate_email']}");
					}
					if (!empty($relative['Relative']['home_phone'])) {
						$lines[] = $relative['Relative']['home_phone'] . ' (' . __('home', true) . ')';
					}
					if (!empty($relative['Relative']['work_phone'])) {
						$line = $relative['Relative']['work_phone'];
						if (!empty($relative['Relative']['work_ext'])) {
							$line .= ' x' . $relative['Relative']['work_ext'];
						}
						$line .= ' (' . __('work', true) . ')';
						$lines[] = $line;
					}
					if (!empty($relative['Relative']['mobile_phone'])) {
						$lines[] = $relative['Relative']['mobile_phone'] . ' (' . __('mobile', true) . ')';
					}
					$lines[] = '';
				}
			}
			array_pop($lines);
			echo implode($this->Html->tag('br'), $lines);
		}
	}
}
?>
