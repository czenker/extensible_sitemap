<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/auto', 'Auto Configuration');

t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns(
	'pages',
	array (
		'tx_extensiblesitemap_frequency' =>  array(
			'label' => 'LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy',
			'exclude' => 1,
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.auto', ''),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.always', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_ALWAYS),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.hourly', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_HOURLY),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.daily', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_DAILY),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.weekly', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_WEEKLY),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.monthly', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_MONTHLY),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.yearly', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_YEARLY),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_frequenzy.never', Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_NEVER),		
				),
				'default' => ''
			),
		),
		'tx_extensiblesitemap_priority' => array(
			'label' => 'LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority',
			'exclude' => 1,
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.disabled', ''),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.10', '1.0'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.9', '0.9'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.8', '0.8'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.7', '0.7'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.6', '0.6'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.5', '0.5'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.4', '0.4'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.3', '0.3'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.2', '0.2'),
					array('LLL:EXT:extensible_sitemap/Resources/Private/Language/locallang_tca.xml:tx_extensiblesitemap_priority.1', '0.1'),			
				),
				'default' => '',
			)
		),
	),
	0
);
$TCA['pages']['palettes']['3']['showitem'] .= ', tx_extensiblesitemap_frequency, tx_extensiblesitemap_priority';

?>