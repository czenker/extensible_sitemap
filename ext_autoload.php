<?php
$extensionPath = t3lib_extMgm::extPath('extensible_sitemap');
$extensionClassesPath = $extensionPath . 'Classes/';
return array(
	'tx_extensiblesitemap_exception' => $extensionClassesPath . 'Exception.php',
	'tx_extensiblesitemap_controller_eid' => $extensionClassesPath . 'Controller/Eid.php',
	'tx_extensiblesitemap_utility_config' => $extensionClassesPath . 'Utility/Config.php',
	'tx_extensiblesitemap_generator_interface' => $extensionClassesPath . 'Generator/Interface.php',

	'tx_extensiblesitemap_generator_page_recursive' => $extensionClassesPath . 'Generator/Page/Recursive.php',
	'tx_extensiblesitemap_generator_ttnews_simplearticle' => $extensionClassesPath . 'Generator/TtNews/SimpleArticle.php',
	'tx_extensiblesitemap_generator_ttnews_newsarticle' => $extensionClassesPath . 'Generator/TtNews/NewsArticle.php',
);
?>
