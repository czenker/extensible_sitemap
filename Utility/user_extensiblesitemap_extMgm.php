<?php
function user_extensiblesitemap_isExtensionEnabled($name) {
	return t3lib_extMgm::isLoaded($name, false);
}