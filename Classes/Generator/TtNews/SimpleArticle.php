<?php
/**
 * A generator for "extensible_sitemap" that indexes tt_news articles
 * this is a basic version that does NOT extend the XML-Scheme by additional tags - 
 * so these are just the bare webpages created and do not generate a sitemap for google news 
 * 
 * Please notice that this is for tt_news from version 3.0.0 up - the parameters have changed
 * with this version
 * 
 * configuration:
 * ==============
 *  * singlePid: the page id of the single view
 *  * pid_list: the pids the records reside in (set to "0" to take all records)
 *  * recursive: how many levels under the above given pid_list should be looked for records too
 *  * defaultPriority: the default priority assigned to each item.
 *  * showInternalPages: if a news with type "internal pages" should be listed, defaults to false
 */
class Tx_ExtensibleSitemap_Generator_TtNews_SimpleArticle implements Tx_ExtensibleSitemap_Generator_Interface {
	
	protected $conf = null;
	protected $parent = null;
	protected $cObj = null;
	
	/**
	 * the fields to fetch from the record
	 * 
	 * @var string
	 */
	protected $fieldList = 'uid,title,datetime,tstamp,archivedate,type,page';
	
	/**
	 * timestamp of the current time will be cached here
	 * @var integer
	 */
	protected $now = null;
	/**
	 * the default priority to set will be cached here
	 * @var string
	 */
	protected $priority = null; 
	
	/**
	 * a handle to the database result to fetch the news records from
	 * 
	 * @var unknown_type
	 */
	protected $newsHandle = null;
	
	/**
	 * the singlePid will be cached here
	 * @var integer
	 */
	protected $singlePid = null;
	
	/**
	 * initializes the generator
	 * 
	 * @param array $conf
	 * @param Tx_ExtensibleSitemap_Utility_Config $parent
	 * @return null
	 */
	public function init($conf, $parent, $cObj = null) {
		$this->conf = $conf;
		$this->parent = $parent;
		$this->cObj = $cObj;
		
		$this->now = time();
		$this->priority = $this->conf['defaultPriority'] ? $this->conf['defaultPriority'] : null;
		
		if(isset($this->conf['singlePid'])) {
			$this->singlePid = intval($this->conf['singlePid']);
		}
		if(empty($this->singlePid)) {
			$this->parent->throwError(500, 'There was no singlePid given for '.__CLASS__);
		}
		$this->createNewsHandle();
	}
	
	/**
	 * create the database handle for all the desired news records
	 * 
	 * @return null
	 */
	protected function createNewsHandle() {
		$pids = isset($this->conf['pid_list']) ? $this->conf['pid_list'] : 0;
		$recursive = isset($this->conf['recursive']) ? $this->conf['recursive'] : 0;
		if($pids !== 0) {
			$pids = $this->getPidList($pids, $recursive);
		}
		
		/**
		 * a limitation to certain types of the tt_news record
		 * "0" is the normal news record
		 * "1" is the internal page
		 * 
		 * note: linking to external pages seems useless as sitemaps can't be 
		 * created for a different site 
		 *  
		 * @var string
		 */
		$types = $this->conf['showInternalPages'] ? '0,1' : '0';
		
		/**
		 * the WHERE part of the select query
		 * @var string
		 */
		$selectWhere = 
			($pids === 0 ? '1=1' : 'pid IN (' . $pids . ')').
			$this->cObj->enableFields('tt_news'). 
			' AND type IN('.$types.')' // only search for "news" and maybe "internal page" type
		;
		
		$this->newsHandle = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$this->fieldList,
			'tt_news',
			$selectWhere,
			'',
			'datetime DESC'
		);
	}
	
	/**
	 * closes down the generator
	 * 
	 * @return null
	 */
	public function finish(){
		$GLOBALS['TYPO3_DB']->sql_free_result($this->newsHandle);
	}
	
	/**
	 * get the next configuration for a page
	 * 
	 * @return array
	 */
	public function getNext() {
		while($news = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($this->newsHandle)) {
			
			if(!$this->shouldPageBeIndexed($news)) {
				continue;
			}
			
			return $this->processNews($news);
		}
		return null;
	}
	
	/**
	 * process a single news and return an array with all the generated data
	 * 
	 * @param $news
	 */
	protected function processNews($news) {
		
		if(empty($news['type'])) {
			$href = $this->cObj->typoLink_URL(array(
				'parameter' => $this->singlePid,
				'additionalParams' => sprintf(
					'&tx_ttnews[tt_news]=%d',
					$news['uid']
				),
				'useCacheHash' => true
			));
		} elseif($news['type'] == 1) {
			// if: link internal page
			$href = $this->cObj->typoLink_URL(array(
				'parameter' => $news['page'],
			));
		}
		
		return array(
			'title' => $news['title'],
			'_OVERRIDE_HREF' => $href,
			'SYS_LASTCHANGED' => $news['tstamp'],
			'tx_extensiblesitemap_priority' => $this->getPriority($news),
			'tx_extensiblesitemap_frequency' => $this->getFrequency($news)
		);
	}
	
	/**
	 * decides wether a given page should be indexed or not
	 * 
	 * @param $pageInfo
	 * @return boolean
	 */
	protected function shouldPageBeIndexed($pageInfo) {
		return true;
	}
	
	/**
	 * get the priority for a record
	 * 
	 * @param array $news
	 * @return string
	 */
	protected function getPriority($news) {
		return $this->priority;
	}
	
	/**
	 * get the frequency to check for changes for a record
	 * 
	 * @param array $news
	 * @return string
	 */
	protected function getFrequency($news) {
		if($news['archivedate'] > 86400 && $this->now > $news['archivedate']) {
			// if: the article was moved to the archive
			return Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_NEVER;
		} elseif ($news['datetime'] + 14 * 86400 > $this->now) {
			// if: the article is younger than one day -> there might be a typo to fix or a discussion going on
			return Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_DAILY;
		} else {
			// else: the article is not very likely to be changed
			return Tx_ExtensibleSitemap_Utility_Config::FREQUENCY_MONTHLY;
		}
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
	public function getRequiredNamespaces() {}
	
	/**
	 * Returns a commalist of page ids for a query (eg. 'WHERE pid IN (...)')
	 *
	 * @param	string		$pid_list is a comma list of page ids (if empty current page is used)
	 * @param	integer		$recursive is an integer >=0 telling how deep to dig for pids under each entry in $pid_list
	 * @return	string		List of PID values (comma separated)
	 * @see tslib_pibase::pi_getPidList
	 */
	public function getPidList($pid_list, $recursive) {
		if (!strcmp($pid_list, '')) {
			$pid_list = $GLOBALS['TSFE']->id;
		}

		$recursive = t3lib_div::intInRange($recursive, 0);

		$pid_list_arr = array_unique(t3lib_div::trimExplode(',', $pid_list, 1));
		$pid_list     = array();

		foreach($pid_list_arr as $val) {
			$val = t3lib_div::intInRange($val, 0);
			if ($val) {
				$_list = $this->cObj->getTreeList(-1 * $val, $recursive);
				if ($_list) {
					$pid_list[] = $_list;
				}
			}
		}

		return implode(',', $pid_list);
	}
}
