<h2>Administrator Guide: <?php __(Configure::read('ui.field_cap')); ?> Management</h2>
<p><?php __(Configure::read('ui.field_cap')); ?> management is fairly straight-forward.
<?php __(Configure::read('ui.fields_cap')); ?> are handled in a hierarchical structure.</p>

<h3>Regions</h3>
<p>Your organization will have one or more regions, which you manage through the <?php __(Configure::read('ui.fields_cap')); ?> -> Regions area.
Examples might be North, South, East, Central, or named after neighbourhoods or political areas.</p>
<p>Distinct regions are primarily useful if you want to support teams with regional preferences.</p>

<h3>Facilities</h3>
<p>Each region will have one or more facilities.
A facility is generally any park, school, stadium, etc. where you have procured one or more <?php __(Configure::read('ui.fields')); ?>.</p>
<?php echo $this->element('help/facilities/edit/name'); ?>
<p>Each facility is also given a name three-letter code.
For example, "Maple Grove Park" might be abbreviated as MGP or MAP.</p>

<h3><?php __(Configure::read('ui.fields_cap')); ?></h3>
<p>Each facility will have one or more <?php __(Configure::read('ui.fields')); ?>.
A <?php __(Configure::read('ui.field')); ?> is the space where a single game can take place.</p>
<?php echo $this->element('help/fields/edit/num'); ?>
<p>If you use a single large "<?php __(Configure::read('ui.field')); ?>" to host more than one game at a time, then that must be configured as multiple <?php __(Configure::read('ui.fields')); ?>.</p>

<h3>Layouts</h3>
<p><?php echo ZULURU; ?> includes a <?php __(Configure::read('ui.field')); ?> layout viewer and editor integrated with Google Maps.
When you are viewing or editing a <?php __(Configure::read('ui.field')); ?>, other <?php __(Configure::read('ui.fields')); ?> at that facility will also be shown.
Clicking the marker for a <?php __(Configure::read('ui.field')); ?> will "activate" that <?php __(Configure::read('ui.field')); ?>;
in the viewer it will show details about that <?php __(Configure::read('ui.field')); ?>, and in the editor it will change which <?php __(Configure::read('ui.field')); ?> you are editing.
In the editor, you can drag <?php __(Configure::read('ui.fields')); ?> around by their marker, and adjust their size and angle with buttons to the right.</p>

<h4>Parking</h4>
The editor also includes an "add parking" button.
Click this, then click on the map to add a parking marker.
Parking markers can be dragged around like <?php __(Configure::read('ui.fields')); ?>, and can be deleted by simply clicking on them and confirming the deletion.
Parking markers locations are facility-wide; you do not need to set up parking separately for each <?php __(Configure::read('ui.field')); ?>.</p>

<h3>Open and Closed <?php __(Configure::read('ui.fields_cap')); ?> and Facilities</h3>
<p>For historical purposes, once a game has been scheduled at a <?php __(Configure::read('ui.field')); ?>, that <?php __(Configure::read('ui.field')); ?> cannot be deleted from the system.
Similarly, once a <?php __(Configure::read('ui.field')); ?> has been added to a facility, that facility cannot be deleted.
However, there are times when you no longer wish a <?php __(Configure::read('ui.field')); ?> or facility to show up in the <?php echo $this->Html->link(sprintf('%s list', Configure::read('ui.field')), array('controller' => 'fields')); ?>.
When this happens, you can close <?php __(Configure::read('ui.fields')); ?> or facilities.
<p>By closing a <?php __(Configure::read('ui.field')); ?>, you are temporarily removing it from circulation.
It will no longer show up on the "other <?php __(Configure::read('ui.fields')); ?>" list or the layout map for <?php __(Configure::read('ui.fields')); ?> at that facility,
and you will not be able to add game slots for it.
Facilities with no open <?php __(Configure::read('ui.fields')); ?> will not show up on the <?php __(Configure::read('ui.field')); ?> list or the <?php echo $this->Html->link(sprintf('map of all %s', Configure::read('ui.fields')), array('controller' => 'maps'), array('target' => 'map')); ?>.</p>
<p>By closing a facility, you are essentially permanently removing it, and all of its <?php __(Configure::read('ui.fields')); ?>, from circulation.
It will no longer show up on the map of all <?php __(Configure::read('ui.fields')); ?> or the <?php __(Configure::read('ui.field')); ?> list.
This should be done when a facility is permanently closed or permits are lost or dropped.
Closing a facility also closes all associated <?php __(Configure::read('ui.fields')); ?>.
Closed facilities can be re-opened, but its <?php __(Configure::read('ui.fields')); ?> will need to be re-opened individually.</p>
