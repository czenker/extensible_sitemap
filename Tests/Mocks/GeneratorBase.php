<?php

class Tx_ExtensibleSitemapTests_GeneratorBase implements Tx_ExtensibleSitemap_Generator_Interface {
	
	public static $instanceCount = 0;
	
	public function __construct() {
		self::$instanceCount++;
	}
	
	public static $lastConfig = null;
	
	/**
	 * initializes the generator
	 * 
	 * @param array $conf
	 * @param Tx_ExtensibleSitemap_Controller_Eid $parent
	 * @return null
	 */
	public function init($conf, $parent, $cObj = null) {
		self::$lastConfig = $conf;
	}
	
	/**
	 * get the next configuration for a page
	 * 
	 * @return array
	 */
	public function getNext() {}
	
	/**
	 * get an array of required namespaces if any
	 * 
	 * * return some empty value if no additional XML-Namespaces are required
	 * * or return a mixed array, where key is namespace name and value is uris for the dtd
	 * * or return an array with numeric keys and the namespace as value -> the dtd is used from a database then 
	 * 
	 * @return array
	 */
	public function getRequiredNamespaces() {}
}