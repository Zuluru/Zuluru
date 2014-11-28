<?php
echo $this->ZuluruHtml->script('selector.js');
echo $this->Js->buffer('
	jQuery(".profile-trigger").on("click", function() { return select_dropdown(jQuery(this).closest(".profile-trigger"), jQuery("#profile_options")); });
');
