<?php

interface Tx_ExtensibleSitemap_Generator_Interface {
	/**
	 * initializes the generator
	 * 
	 * @param array $conf
	 * @param Tx_ExtensibleSitemap_Controller_Eid $parent
	 * @return null
	 */
	public function init($conf, $parent, $cObj = null);
	
	/**
	 * closes down the generator
	 * 
	 * @return null
	 */
	public function finish();
	
	/**
	 * get the next configuration for a page
	 * 
	 * @return array
	 */
	public function getNext();
	
	/**
	 * get an array of required namespaces if any
	 * 
	 * * return some empty value if no additional XML-Namespaces are required
	 * * or return a mixed array, where key is namespace name and value is uris for the dtd
	 * * or return an array with numeric keys and the namespace as value -> the dtd is used from a database then 
	 * 
	 * @return array
	 */
	public function getRequiredNamespaces();
}