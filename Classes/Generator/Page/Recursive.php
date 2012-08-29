<?php

/**
 * A generator for "extensible_sitemap" that indexes pages of the TYPO3 page-tree recursively
 * 
 * configuration:
 * ==============
 *  * pidList: a comma-seperated list of pageIds that serve as the root for the indexer. If not set this will be the id of the currently visited page
 */
class Tx_ExtensibleSitemap_Generator_Page_Recursive implements Tx_ExtensibleSitemap_Generator_Interface {
	
	protected $conf = null;
	protected $parent = null;
	protected $cObj = null;
	
	protected $pagesList = array();
	
	protected $fieldList = 'uid,doktype,no_search,SYS_LASTCHANGED,tx_extensiblesitemap_frequency,tx_extensiblesitemap_priority';
	protected $invalidDoktypes =  array(3,4,5,6,7,199,254,255);
	
	/**
	 * initializes the generator
	 * 
	 * @param array $conf
	 * @param Tx_ExtensibleSitemap_Controller_Eid $parent
	 * @return null
	 */
	public function init($conf, $parent, $cObj = null) {
		$this->conf = $conf;
		$this->parent = $parent;
		$this->cObj = $cObj;
		
		$pids = array_key_exists('pidList', $this->conf) ? 
			t3lib_div::trimExplode(',', $this->conf['pidList'], true) :
			array(0) 
		;
		
			
		/**
		 * an array that have all fields from $this->fieldList as keys and NULL as value
		 * @var array
		 */
		$fieldList = array_flip(t3lib_div::trimExplode(',', $this->fieldList, true));
		
		//TODO: this could be optimized by using one db-query 
		foreach($pids as $pid) {
			if ($pid === 0 || $pid == $GLOBALS['TSFE']->id) {
	            $page = &$GLOBALS['TSFE']->page;
	        }
	        else {
	            $page = $GLOBALS['TSFE']->sys_page->getPage($pid);
	           
	        }
	        // this takes all the fields from $page, that were allowed in $this->fieldList, all others are discarded
            $this->pagesList[$page['uid']] = array_intersect_key($page, $fieldList);
		}
	}
	
	/**
	 * closes down the generator
	 * 
	 * @return null
	 */
	public function finish(){}
	
	/**
	 * get the next configuration for a page
	 * 
	 * @return array
	 */
	public function getNext() {
		while(!empty($this->pagesList)) {
			$pageInfo = array_shift($this->pagesList);
			// apply all subpages to the stack of indexable pages
			$this->pagesList += $GLOBALS['TSFE']->sys_page->getMenu(
				$pageInfo['uid'],
				$this->fieldList,
				'',
				'',
				false
			);
			
			if (!$this->shouldPageBeIndexed($pageInfo)) {
				continue;
			}

			if(array_key_exists('mobile', $this->conf) && $this->conf['mobile']) {
				$pageInfo['TX_EXTENSIBLESITEMAP_ADDITIONAL_FIELDS'] = '<mobile:mobile />';
			}

			return $pageInfo;
			
		}
		// if pagesList is empty return nothing
		return null;
	}
	
	/**
	 * decides wether a given page should be indexed or not
	 * 
	 * @param $pageInfo
	 * @return boolean
	 */
	protected function shouldPageBeIndexed($pageInfo) {
		return !in_array($pageInfo['doktype'], $this->invalidDoktypes) && $pageInfo['no_search'] == 0;
	}
	
	/**
	 * get an array of required namespaces if any
	 * 
	 * * return some empty value if no additional XML-Namespaces are required
	 * * or return a mixed array, where key is namespace name and value is uris for the dtd
	 * * or return an array with numeric keys and the namespace as value -> the dtd is used from a database then 
	 * 
	 * @return array
	 */
	public function getRequiredNamespaces() {
		if(is_array($this->conf) && array_key_exists('mobile', $this->conf) && $this->conf['mobile'] == true) {
			return array('mobile');
		} else {
			return array();
		}

	}
}