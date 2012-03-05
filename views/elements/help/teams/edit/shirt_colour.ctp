<p>Shirt colour can be whatever you want, but if you pick a common colour you'll get a properly-coloured shirt icon next to your team name in various displays. Examples include yellow <?php
echo $this->ZuluruHtml->icon('shirts/yellow.png');
?>, light blue <?php
echo $this->ZuluruHtml->icon('shirts/light_blue.png');
?> and dark <?php
echo $this->ZuluruHtml->icon('shirts/dark.png');
?>. If you have two options, list them both. For example, "blue or white" will show like this: <?php
echo $this->element('shirt', array('colour' => 'blue or white'));
?>.
If you get the "unknown" shirt <?php
echo $this->ZuluruHtml->icon('shirts/default.png');
?>, this means that your colour is not supported.<?php if ($is_admin): ?>
 Additional shirt colours can be added simply by placing appropriately-named icons in the &lt;webroot&gt;/img/shirts folder.<?php endif; ?></p>
