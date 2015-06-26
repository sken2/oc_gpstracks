<?php
script('gpstracks', 'script');
style('gpstracks', 'style');
?>
<!--script type="text/javascript" src="http://www.openlayers.org/api/OpenLayers.js"></script-->
<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
