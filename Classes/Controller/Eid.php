<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Christian Zenker <christian.zenker@599media.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib . 'class.tslib_pagegen.php');
require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_t3lib . 'class.t3lib_userauth.php' );
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
require_once(PATH_t3lib . 'class.t3lib_cs.php');

/**
 * This class implements a Google sitemap.
 *
 * @author Christian Zenker <christian.zenker@599media.de>
 */
class Tx_ExtensibleSitemap_Controller_Eid {
	
	/**
	 * the configuration of the currently to use sitemap
	 * 
	 * @var array
	 */
	protected $config = null;
	
	/**
	 * cObject to generate links
	 *
	 * @var	tslib_cObj
	 */
	protected $cObj;
	
	/**
	 * an array of generators which want to create sitemap entries
	 * 
	 * @var array
	 */
	protected $genrators = null;

	/**
	 * a list of predefined namespaces
	 * 
	 * @var array
	 */
	protected $namespaces = array(
		'image' => 'http://www.google.com/schemas/sitemap-image/1.1',
		'video' => 'http://www.google.com/schemas/sitemap-video/1.1',
		'mobile' => 'http://www.google.com/schemas/sitemap-mobile/1.0',
		'codesearch' => 'http://www.google.com/codesearch/schemas/sitemap/1.0',
		'geo' => 'http://www.google.com/geo/schemas/sitemap/1.0',
		'news' => 'http://www.google.com/schemas/sitemap-news/0.9',
	);
	
	/**
	 * __constructor
	 * 
	 * sets up the configuration
	 * 
	 * @return	void
	 */
	public function __construct() {
		
		@set_time_limit(300);
		$this->initTSFE();
		
		@header('Content-type: text/xml');
		
		if(!is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'])) {
			$this->throwError(500, 'plugin.tx_extensiblesitemap is no array in the setup.');
		}
		
		// fill the config member variable
		$sitemap = t3lib_div::_GP('sitemap') ? t3lib_div::_GP('sitemap').'.' : 'default.';
		if(array_key_exists($sitemap, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'])) {
			$this->config = &$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'][$sitemap];
		} else {
			$this->throwError(404, sprintf('The sitemap "%s" was not configured.', rtrim($sitemap, '.')));
		}
		
		// check if the configuration existed and is valid
		if(!is_array($this->config)) {
			$this->throwError(500, 'The used configuration is not an array.');
		}
		
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->cObj->start(array());
	}
	
	/**
	 * throw an error if some error occurs
	 * 
	 * @param integer $code
	 * @param string $message
	 * @throws Tx_ExtensibleSitemap_Exception
	 */
	public function throwError($code, $message = null) {
		throw new Tx_ExtensibleSitemap_Exception($message, $code);
	}

	/**
	 * Outputs sitemap for pages.
	 *
	 * @return	void
	 */
	public function main() {
		$this->setupGenerators();
		
		$this->startSitemap();
		
		foreach($this->generators as $name => $generator) {
			
			while($page = $generator->getNext()) {
				$this->renderPage($page);
			}
			
			// unset this generator to free resources (the generator might have a whole bunch of data stored)
			$this->generators[$name]->finish();
		}
		
		$this->endSitemap();
	}
	
	/**
	 * the main method that renders an entry 
	 * 
	 * @param array $page
	 */
	protected function renderPage($page) {
        if($page['_OVERRIDE_HREF']) {
            $location = $page['_OVERRIDE_HREF'];
        } else {
            $location = $this->cObj->typoLink('|', array( // else: generate url
                'parameter' => $page['uid'],
                'returnLast' => 'url',
            ));
            if($location == '|') {
                // if: no link to page was generated
                // (can happen when page is hidden in a language)
                return;
            }
        }
        $location = t3lib_div::locationHeaderUrl($location);
        echo '<url>';
        echo '<loc>'.htmlspecialchars($location).'</loc>';

		if($page['SYS_LASTCHANGED'] && $page['SYS_LASTCHANGED'] > 86400) {
			echo '<lastmod>'.date('c', $page['SYS_LASTCHANGED']).'</lastmod>';
		}
		if($page['tx_extensiblesitemap_frequency']) {
			echo '<changefreq>'.htmlspecialchars($page['tx_extensiblesitemap_frequency']).'</changefreq>';
		}
		if($page['tx_extensiblesitemap_priority']) {
			echo '<priority>'.htmlspecialchars($page['tx_extensiblesitemap_priority']).'</priority>';
		}
		if($page['TX_EXTENSIBLESITEMAP_ADDITIONAL_FIELDS']) {
			echo $page['TX_EXTENSIBLESITEMAP_ADDITIONAL_FIELDS'];
		}
		
		echo '</url>';
	}
	
	/**
	 * output some stuff for the beginning of the sitemap
	 * 
	 * @return null
	 */
	protected function startSitemap() {
		$namespaces = $this->getNeededNamespaces();
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.$this->implodeNamespaces($namespaces).'>';
	}
	
	/**
	 * output some stuff for the end of the sitemap
	 * 
	 * @return null
	 */
	protected function endSitemap() {
		echo '</urlset>';
	}
	
	/**
	 * setup all generators
	 * but does not initialize them
	 * 
	 * @return null
	 */
	protected function setupGenerators() {
		$this->generators = array();
		foreach($this->config as $name => $class) {
			if(substr($name, -1) === '.') {
				continue;
			}
			$generator = t3lib_div::makeInstance((string)$class);
			$generator->init($this->config[$name.'.'] ? $this->config[$name.'.'] : array(), $this, $this->cObj);
			
			if(!$generator instanceof Tx_ExtensibleSitemap_Generator_Interface) {
				$this->throwError(500, sprintf('"%s" is no instance of Tx_ExtensibleSitemap_Generator_Interface', get_class($generator)));
			}
			
			$this->generators[$name] = $generator;
		}
	}
	
	/**
	 * get the needed namespaces from all generators 
	 * and check for consistancy
	 * 
	 * @return array(array('name' => 'uri'))
	 */
	protected function getNeededNamespaces() {
		$namespaces = array();
		foreach($this->generators as $generator) {
			$nss = $generator->getRequiredNamespaces();
			if(empty($nss) || !is_array($nss)) {
				continue;
			}
			foreach($nss as $name => $uri) {
				if(is_numeric($name)) {
					// if only the namespace but not the uri is set
					$name = $uri;
					$uri = null;
				}
				if(empty($uri)) {
					if(!array_key_exists($name, $this->namespaces)) {
						$this->throwError(500, sprintf('namespace "%s" could not be resolved.', htmlspecialchars($name)));
					}
					$uri = $this->namespaces[$name];
				}
				
				if(isset($namespaces[$name]) && $namespaces[$name] !== $uri) {
					$this->throwError(500, sprintf(
						'The namespace "%s" was already defined by the uri "%s" and could not be overridden by "%s"',
						htmlspecialchars($name),
						htmlspecialchars($namespaces[$name]),
						htmlspecialchars($uri)
					));
				}
				$namespaces[$name] = $uri;
			}
		}
		
		return $namespaces;
	}
	
	/**
	 * implode a list of namespaces and return a string for use as XML-namespace definitions
	 * 
	 * @param $namespaces
	 */
	protected function implodeNamespaces($namespaces) {
		$return = '';
		foreach($namespaces as $name => $uri) {
			$return .= sprintf(
				' xmlns:%s="%s"',
				htmlspecialchars($name),
				htmlspecialchars($uri)
			);
		}
		return $return;
	}
	

	/**
	 * Initializes TSFE and sets $GLOBALS['TSFE']
	 *
	 * @author	Dmitry Dulepov <dmitry@typo3.org>
	 * @see http://typo3.org/extensions/repository/view/dd_googlesitemap/current/
	 * @return	void
	 */
	protected function initTSFE() {
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$tsfeClassName = t3lib_div::makeInstanceClassName('tslib_fe');
			$GLOBALS['TSFE'] = new $tsfeClassName($GLOBALS['TYPO3_CONF_VARS'], t3lib_div::_GP('id'), '');
		}
		else {
			$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], t3lib_div::_GP('id'), '');
		}
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->checkAlternativeIdMethods();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->getCompressedTCarray();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getConfigArray();

		// Get linkVars, absRefPrefix, etc
		TSpagegen::pagegenInit();
	}
}
?>
