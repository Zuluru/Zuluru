<?php
if (!empty($division['name'])):
	echo $division['name']; ?> division of the <?php
endif;
echo $league['name']; ?> league<?php
if (!empty ($division['Day'])):
?>, which operates on <?php
echo implode (' and ', Set::extract ('/Day/name', $division)); ?><?php
endif;
?>