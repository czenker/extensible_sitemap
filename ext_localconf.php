<?php
if (!defined('TYPO3_MODE')) {
	exit;
}

include_once(t3lib_extMgm::extPath($_EXTKEY) . 'Utility/user_extensiblesitemap_extMgm.php');

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['extensible_sitemap'] = 'EXT:' . $_EXTKEY . '/eid.php';
?>