<h2>Administrator Guide: Field Management</h2>
<p>Field management is fairly straight-forward.
Fields are handled in a hierarchical structure.</p>

<h3>Regions</h3>
<p>Your organization will have one or more regions.
Regions are defined in the database when Zuluru is installed; there is currently no interface for managing the list of regions.
Examples might be North, South, East, Central, or named after neighbourhoods or political areas.</p>
<p>Distinct regions are primarily useful if you want to support teams with regional preferences.</p>

<h3>Facilities</h3>
<p>Each region will have one or more facilities.
A facility is generally any park, school, stadium, etc. where you have procured field space.</p>
<?php echo $this->element('help/facilities/edit/name'); ?>
<p>Each facility is also given a name three-letter code.
For example, "Maple Grove Park" might be abbreviated as MGP or MAP.</p>

<h3>Fields</h3>
<p>Each facility will have one or more fields.
A field is the space where a single game can take place.</p>
<?php echo $this->element('help/fields/edit/num'); ?>
<p>If you use a single large "field" to host more than one game at a time, then that must be configured as multiple fields.</p>

<h3>Layouts</h3>
<p>Zuluru includes a field layout viewer and editor integrated with Google Maps.
When you are viewing or editing a field, other fields at that facility will also be shown.
Clicking the marker for a field will "activate" that field;
in the viewer it will show details about that field, and in the editor it will change which field you are editing.
In the editor, you can drag fields around by their marker, and adjust their size and angle with buttons to the right.</p>

<h4>Parking</h4>
The editor also includes an "add parking" button.
Click this, then click on the map to add a parking marker.
Parking markers can be dragged around like fields, and can be deleted by simply clicking on them and confirming the deletion.
Parking markers locations are facility-wide; you do not need to set up parking separately for each field.</p>

<h3>Open and Closed Fields and Facilities</h3>
<p>For historical purposes, once a game has been scheduled at a field, that field cannot be deleted from the system.
Similarly, once a field has been added to a facility, that facility cannot be deleted.
However, there are times when you no longer wish a field or facility to show up in the <?php echo $this->Html->link('field list', array('controller' => 'fields')); ?>.
When this happens, you can close fields or facilities.
<p>By closing a field, you are temporarily removing it from circulation.
It will no longer show up on the "other fields" list or the layout map for fields at that facility,
and you will not be able to add game slots for it.
Facilities with no open fields will not show up on the field list or the <?php echo $this->Html->link('map of all fields', array('controller' => 'maps'), array('target' => 'new')); ?>.</p>
<p>By closing a facility, you are essentially permanently removing it, and all of its fields, from circulation.
It will no longer show up on the map of all fields or the field list.
This should be done when a facility is permanently closed or permits are lost or dropped.
Closing a facility also closes all associated fields.
Closed facilities can be re-opened, but its fields will need to be re-opened individually.</p>
