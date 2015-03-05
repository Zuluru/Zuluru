<h2><?php printf(__('Administrator Guide: %s Management', true), __(Configure::read('ui.field_cap'), true)); ?></h2>
<p><?php
printf(__('%s management is fairly straight-forward. %s are handled in a hierarchical structure.', true),
	__(Configure::read('ui.field_cap'), true), __(Configure::read('ui.fields_cap'), true)
); ?></p>

<h3><?php __('Regions'); ?></h3>
<p><?php
printf(__('Your organization will have one or more regions, which you manage through the %s -> Regions area. Examples might be North, South, East, Central, or named after neighbourhoods or political areas.', true),
	__(Configure::read('ui.fields_cap'), true)
); ?></p>
<p><?php __('Distinct regions are primarily useful if you want to support teams with regional preferences.'); ?></p>

<h3><?php __('Facilities'); ?></h3>
<p><?php
printf(__('Each region will have one or more facilities. A facility is generally any park, school, stadium, etc. where you have procured one or more %s.', true),
	__(Configure::read('ui.fields'), true)
); ?></p>
<?php echo $this->element('help/facilities/edit/name'); ?>
<p><?php __('Each facility is also given a name three-letter code. For example, "Maple Grove Park" might be abbreviated as MGP or MAP.'); ?></p>

<h3><?php __(Configure::read('ui.fields_cap')); ?></h3>
<p><?php
printf(__('Each facility will have one or more %s. A %s is the space where a single game can take place.', true),
	__(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true)
); ?></p>
<?php echo $this->element('help/fields/edit/num'); ?>
<p><?php
printf(__('If you use a single large "%s" to host more than one game at a time, then that must be configured as multiple %s.', true),
	__(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true)
); ?></p>

<h3><?php __('Layouts'); ?></h3>
<p><?php
printf(__('%s includes a %s layout viewer and editor integrated with Google Maps. When you are viewing or editing a %s, other %s at that facility will also be shown. Clicking the marker for a %s will "activate" that %s; in the viewer it will show details about that %s, and in the editor it will change which %s you are editing. In the editor, you can drag %s around by their marker, and adjust their size and angle with buttons to the right.', true),
	ZULURU, __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true)
); ?></p>

<h4><?php __('Parking and Entrances'); ?></h4>
<p><?php
printf(__('The editor also includes "add parking" and "add entrance" buttons. Click these, then click on the map to add a parking or entrance marker. These markers can be dragged around like %s, and can be deleted by simply clicking on them and confirming the deletion. Parking and entrance markers are facility-wide; you do not need to set up parking and entrances separately for each %s.', true),
	__(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true)
); ?></p>

<h3><?php
printf(__('Open and Closed %s and Facilities', true),
	__(Configure::read('ui.fields_cap'), true)
); ?></h3>
<p><?php
printf(__('For historical purposes, once a game has been scheduled at a %s, that %s cannot be deleted from the system. Similarly, once a %s has been added to a facility, that facility cannot be deleted. However, there are times when you no longer wish a %s or facility to show up in the %s. When this happens, you can close %s or facilities.', true),
	__(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true),
	$this->Html->link(__('facility list', true), array('controller' => 'facilities')),
	__(Configure::read('ui.fields'), true)
); ?></p>
<p><?php
printf(__('By closing a %s, you are temporarily removing it from circulation. It will no longer show up on the "other %s" list or the layout map for %s at that facility, and you will not be able to add game slots for it. Facilities with no open %s will not show up on the %s list or the %s.', true),
	__(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true),
	$this->Html->link(sprintf(__('map of all %s', true), __(Configure::read('ui.fields'), true)), array('controller' => 'maps'), array('target' => 'map'))
); ?></p>
<p><?php
printf(__('By closing a facility, you are essentially permanently removing it, and all of its %s, from circulation. It will no longer show up on the map of all %s or the %s list. This should be done when a facility is permanently closed or permits are lost or dropped. Closing a facility also closes all associated %s. Closed facilities can be re-opened, but its %s will need to be re-opened individually.', true),
	__(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true)
); ?></p>
