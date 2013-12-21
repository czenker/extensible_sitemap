<?php
/** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $TSFE */
$TSFE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

// Initialize Language
\TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();

// Initialize FE User.
$TSFE->initFEuser();

// Important: no Cache for Ajax stuff
$TSFE->set_no_cache();
$TSFE->checkAlternativeIdMethods();
$TSFE->determineId();
$TSFE->initTemplate();
$TSFE->getConfigArray();
\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
$TSFE->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
$TSFE->settingLanguage();
$TSFE->settingLocale();
try {
	ob_start();
	$generator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_ExtensibleSitemap_Controller_Eid');
	$generator->main();
	ob_end_flush();
} catch(Tx_ExtensibleSitemap_Exception $e) {
	@header('','',$e->getCode());
	echo sprintf('<!-- an error occured %d: %s-->', $e->getCode(), $e->getMessage());
	die();
}
?>