<?php
/**
 * A generator for "extensible_sitemap" that indexes tt_news articles for a news sitemap
 * 
 * Please notice that this is for tt_news from version 3.0.0 up - the parameters have changed
 * with this version
 * 
 * configuration:
 * ==============
 *  * singlePid: the page id of the single view
 *  * pid_list: the pids the records reside in (set to "0" to take all records)
 *  * recursive: how many levels under the above given pid_list should be looked for records
 *  * defaultPriority: the default priority assigned to each item.
 *  * publicationName: the name of the publication
 *  * publicationLanguage: the 2- or 3-signed ISO 639 Language Code of the language this news is in. Leave blank for autodetection.
 *  * access: if access to the article is not public, set one of "Subscription,Registration", also see http://www.google.com/support/webmasters/bin/answer.py?answer=93992
 *  * genres: might be a comma-seperated list of "PressRelease,Satire,Blog,OpEd,Opinion,UserGenerated", also see http://www.google.com/support/webmasters/bin/answer.py?answer=93992
 *  * maxAge: the maximum age in days of the news in order to be displayed. Default is "7" but Google states it won't add news if they were published more than 2 days ago: http://www.google.com/support/news_pub/bin/answer.py?answer=74496
 *  * showInternalPages: if a news with type "internal pages" should be listed, defaults to true 
 *  
 *  
 *  @see http://www.google.com/support/webmasters/bin/answer.py?answer=74288
 *  @see http://www.google.com/support/webmasters/bin/answer.py?answer=93992
 *  @see http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd
 */
class Tx_ExtensibleSitemap_Generator_TtNews_NewsArticle extends Tx_ExtensibleSitemap_Generator_TtNews_SimpleArticle {
	
	/**
	 * the fields to fetch from the record
	 * 
	 * @var string
	 */
	protected $fieldList = 'uid,title,datetime,tstamp,archivedate,image,imagecaption,imagetitletext,type,page';
	
	/**
	 * an array used to store preprocessed values
	 * 
	 * @var array
	 */
	protected $cache = array();
	
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
		$types = isset($this->conf['showInternalPages']) && $this->conf['showInternalPages'] == 0 ? '0' : '0,1';
		
		/**
		 * calculate the minimum timestamp for a news to be released
		 * @var integer
		 */
		$minAge = time() - ($this->conf['maxAge'] ? intval($this->conf['maxAge']) : 7) * 86400;
		
		/**
		 * the WHERE part of the select query
		 * @var string
		 */
		$selectWhere = 
			($pids === 0 ? '1=1' : 'pid IN (' . $pids . ')').
			$this->cObj->enableFields('tt_news').
			' AND datetime > ' . $minAge.
			' AND type IN('.$types.')'
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
	 * initializes the generator
	 * 
	 * @param array $conf
	 * @param Tx_ExtensibleSitemap_Utility_Config $parent
	 * @return null
	 */
	public function init($conf, $parent, $cObj = null) {
		parent::init($conf, $parent, $cObj);
		
		if(!isset($this->conf['publicationName'])) {
			$this->parent->throwError(500, 'A publication name was not given.');
		}
		$this->cache['publicationName'] = htmlspecialchars($this->conf['publicationName']);
		
		if(isset($this->conf['publicationLanguage'])) {
			$this->cache['publicationLanguage'] = htmlspecialchars($this->conf['publicationLanguage']);
		} elseif($GLOBALS['TSFE']->sys_language_isocode) {
			$this->cache['publicationLanguage'] = htmlspecialchars($GLOBALS['TSFE']->sys_language_isocode);
		} elseif ($GLOBALS['TSFE']->tmpl->setup['config.']['language']) {
			$this->cache['publicationLanguage'] = htmlspecialchars($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		} else {
			$this->parent->throwError(500, 'Could not determine the language of the publication.');
		}
		
		if(isset($this->conf['access'])) {
			$this->cache['access'] = htmlspecialchars($this->conf['access']);
		}
		
		if(isset($this->conf['genres'])) {
			$this->cache['genres'] = htmlspecialchars($this->conf['genres']);
		}
		
		// load tca to fetch the correct uploadfolder for images
		t3lib_div::loadTCA('tt_news');
	}
	
	/**
	 * closes down the generator
	 * 
	 * @return null
	 */
	public function finish(){}
	
	/**
	 * process a single news and return an array with all the generated data
	 * 
	 * @param $news
	 */
	protected function processNews($news) {
		$add = '<news:news>';
		
		$add .= sprintf(
			'<news:publication><news:name>%s</news:name><news:language>%s</news:language></news:publication>',
			$this->cache['publicationName'],
			$this->cache['publicationLanguage']
		);
		
		if($this->cache['access']) {
			$add .= '<news:access>'.$this->cache['access'].'</news:access>';
		}
		if($this->cache['genres']) {
			$add .= '<news:genres>'.$this->cache['genres'].'</news:genres>';
		}
		$add .= '<news:publication_date>'.date('c', $news['datetime']).'</news:publication_date>';
		
		$add .= '<news:title>'.htmlspecialchars($news['title']).'</news:title>';
		
		if($news['keywords']) {
			// add keywords and limit it to the first 5 keywords set
			$add .= '<news:keywords>'.htmlspecialchars(implode(', ', t3lib_div::trimExplode(',', $news['keywords'], true, 5))).'</news:keywords>';
		}
		$add .= '</news:news>';
		if($news['image']) {
			$captions = t3lib_div::trimExplode("\n", $news['imagecaption'], false, 1000);
			$titles = t3lib_div::trimExplode("\n", $news['imagetitletext'], false, 1000);
			
			foreach(t3lib_div::trimExplode(',', $news['image'], false, 1000) as $key => $image) {
				if(empty($image)) {
					continue;
				}
				$path = $GLOBALS['TCA']['tt_news']['columns']['image']['config']['uploadfolder'].'/'.$image;
				$add .= '<image:image><image:loc>'. t3lib_div::locationHeaderUrl($path) . '</image:loc>';
				if($captions[$key]) {
					$add .= '<image:caption>'. htmlspecialchars($captions[$key]) . '</image:caption>';
				}
				if($titles[$key]) {
					$add .= '<image:title>'. htmlspecialchars($captions[$key]) . '</image:title>';
				}
				$add .='</image:image>';
			}
		}
		
		$array = parent::processNews($news);
		$array['TX_EXTENSIBLESITEMAP_ADDITIONAL_FIELDS'] = $add;
		return $array;
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
		return array('news', 'image');
	}
}
