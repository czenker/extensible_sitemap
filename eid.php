<?php
include_once(t3lib_extMgm::extPath($_EXTKEY) . 'Utility/user_extensiblesitemap_extMgm.php');
try {
	ob_start();
	$generator = t3lib_div::makeInstance('Tx_ExtensibleSitemap_Controller_Eid');
	$generator->main();
	ob_end_flush();
} catch(Tx_ExtensibleSitemap_Exception $e) {
	@header('','',$e->getCode());
	echo sprintf('<!-- an error occured %d: %s-->', $e->getCode(), $e->getMessage());
	die();
}
?>