<?php
function user_extensiblesitemap_isExtensionEnabled($name) {
	return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($name, false);
}