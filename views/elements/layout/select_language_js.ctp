<?php
if (Configure::read('feature.uls') && empty($language)) {
	echo $this->ZuluruHtml->script(array(
			'jquery.uls/src/jquery.uls.data.js',
			'jquery.uls/src/jquery.uls.data.utils.js',
			'jquery.uls/src/jquery.uls.lcd.js',
			'jquery.uls/src/jquery.uls.languagefilter.js',
			'jquery.uls/src/jquery.uls.regionfilter.js',
			'jquery.uls/src/jquery.uls.core.js',
	));
	echo $this->Js->buffer('
		jQuery(".uls-trigger").uls({
			onSelect : function(language) {
				window.location = "' . $this->Html->url(array('controller' => 'all', 'action' => 'language'), true) . '/lang:" + language + "/return:1";
			},
			languages: {' . Configure::read('available_translation_strings') . '}
		});
	');
}
?>