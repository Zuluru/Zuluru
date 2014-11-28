<?php if (Configure::read('feature.uls') && empty($language)): ?>
<span class="uls-trigger"><?php echo Configure::read('Config.language_name'); ?></span>
<?php endif; ?>
