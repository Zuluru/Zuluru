<?php
// This should only ever happen for admins or coordinators, and there is currently nothing
// that differentiates between the two. If that ever changes, the call below will need to change.
echo $this->element('divisions/scheduling_fields', array('fields' => $league_obj->schedulingFields(true, true)));
?>
