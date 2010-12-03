<?php

/**
 * This class is a wrapper for an extbase repository query for a large list of objects.
 * It will only fetch entries as needed and discards entries that are not needed anymore.
 * 
 * It will help you save memory that might be needed for a large resultset.
 * 
 * @author Christian Zenker <christian.zenker@599media.de>
 */
class Tx_ExtensibleSitemap_Utility_ExtbaseDomainIterator implements Iterator {
	
	protected $repository = null;
	protected $query = null;
	protected $limit = 20;
	protected $currentOffset = 0;
	
	/**
	 * if the iterator was already initialized
	 * @var boolean
	 */
	protected $isInitialized = false;
	
	
	public function __construct($repository) {
		$this->setRepository($repository);
	}
	
	/**
	 * set a repository
	 * 
	 * @param string|Tx_Extbase_Persistence_Repository $repository
	 * @throws InvalidArgumentException
	 */
	public function setRepository($repository) {
		if($this->isInitialized) {
			throw new LogicException("You can't modify the repository after the Iterator was initialized.", $code);
		}
		if(is_string($repository)) {
			$repository = t3lib_div::makeInstance($repository);
		} elseif(!is_object($repository)) {
			throw new InvalidArgumentException('The repository must be an object or an object name.');
		}
		if(!$repository instanceof Tx_Extbase_Persistence_Repository) {
			throw new InvalidArgumentException('The repository must be an instance of Tx_Extbase_Persistence_Repository');
		}
		$this->repository = $repository;
	}
	
	/**
	 * set a limit of how many records should be fetched at max
	 * 
	 * @param integer $limit
	 */
	public function setLimit($limit) {
		$limit = intval($limit);
		if($limit < 1) {
			throw new InvalidArgumentException('The limit must be an integer larger than 0.', $code);
		}
		$this->limit = $limit;
	}
	
	/**
	 * set a base query
	 * 
	 * @param Tx_Extbase_Persistence_QueryInterface $query
	 */
	public function setQuery($query) {
		if($this->isInitialized) {
			throw new LogicException("You can't modify the query after the Iterator was initialized.", $code);
		}
		if(!$query instanceof Tx_Extbase_Persistence_QueryInterface) {
			throw new InvalidArgumentException('The repository has to implement the Tx_Extbase_Persistence_QueryInterface');
		}
		$this->query = $query;
	}
	
	
	
	public function init() {
		if(is_null($query)) {
			// if no query is set -> we will try to fetch all
			if(is_null($this->repository)) {
				throw new LogicException("You'll have to set a repository if you don't submit a query.", $code);
			}
			$this->query = $this->repository->createQuery();
		}
		
		$this->isInitialized = true;
	}
	
	protected $currentDataArray = array();
	
	public function current() {
		if(empty($this->currentDataArray)) {
			$this->getMoreData();
		}
		$element = reset($this->currentDataArray);
		if($element === false) {
			$this->currentOffset = false;
		}
		return $element;
	}
	public function key() {
		return $this->currentOffset;
	}
	public function next() {
		array_shift($this->currentDataArray);
		$this->currentOffset++;
		return $this->current();
	}
	public function rewind() {
		$this->currentDataArray = array();
		$this->currentOffset = 0;
	}
	public function valid() {
		return $this->currentOffset !== false;
	}
	
	protected function getMoreData() {
		if(!$this->valid()) {
			return null;
		}
		$this->currentDataArray = $this->query->
			setLimit($this->limit)->
			setOffset($this->currentOffset)->
		execute();
	}
	
}
?>