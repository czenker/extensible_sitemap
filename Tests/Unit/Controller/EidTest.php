<?php

require_once(dirname(__FILE__).'/../../Mocks/EidMock.php');
require_once(dirname(__FILE__).'/../../Mocks/GeneratorBase.php');
class Tx_CzEwlRankingTests_Controller_StateControllerTest extends tx_phpunit_testcase {

	public function setUp() {
		
	}
	
	public function tearDown() {
		
	}
	
	/**
	 * @expectedException Tx_ExtensibleSitemap_Exception
	 */
	public function testIfExceptionThrownOnMissingConfig() {
		$_GET['sitemap'] = null;
		// don't unset() in case this was not set anyways
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = NULL;
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
	}
	
	/**
	 * @expectedException Tx_ExtensibleSitemap_Exception
	 */
	public function testIfExceptionThrownOnMissingConfigForSitemap() {
		$_GET['sitemap'] = null;
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array('foobar.' => array());
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
	}
	
	/**
	 * @expectedException Tx_ExtensibleSitemap_Exception
	 */
	public function testIfExceptionThrownIfConfigIsNoArray() {
		$_GET['sitemap'] = null;
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array('foobar.' => 'baz');
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
	}
	
	public function testIfNoExceptionThrownIfConfigIsEmpty() {
		$_GET['sitemap'] = null;
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array('default.' => array());
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
		
		// no exception was thrown
		self::assertTrue(true);
	}
	
	public function testIfSitemapIsSelectableViaGetParameter() {
		// don't unset() in case this was not set anyways
		$_GET['sitemap'] = 'foobar';
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array('foobar.' => array());
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
		
		// no exception was thrown
		self::assertTrue(true);
	}
	
	public function testIfAllGeneratorsAreRecognized() {
		$_GET['sitemap'] = null;
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array('default.' => array(
			'foo' => 'Tx_ExtensibleSitemapTests_GeneratorBase',
			'foo.' => array(),
			'bar' => 'Tx_ExtensibleSitemapTests_GeneratorBase',
			'bar.' => array(),
		));
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
		$this->getContent($generator);
		
		self::assertEquals(2, Tx_ExtensibleSitemapTests_GeneratorBase::$instanceCount);
	}
	
	/**
	 * @expectedException Tx_ExtensibleSitemap_Exception
	 */
	public function testIfExceptionThrownIfGeneratorDoesNotImplementInterface() {
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array(
			'default.' => array(
				'foo' => 'stdClass',
				'foo.' => array()
			)
		);
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
		$this->getContent($generator);
	}
	
	public function testIfConfigurationIsGivenToGenerator() {
		$conf = array(
			'foo' => 'bar',
			'baz' => '42'
		);
		
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_extensiblesitemap.'] = array(
			'default.' => array(
				'foo' => 'Tx_ExtensibleSitemapTests_GeneratorBase',
				'foo.' => $conf
			)
		);
		
		$generator = new Tx_ExtensibleSitemapTests_Controller_EidMock();
		$this->getContent($generator);
		
		self::assertEquals($conf, Tx_ExtensibleSitemapTests_GeneratorBase::$lastConfig);
	}
	
	
	
	protected function getContent($generator) {
		ob_start();
		try {
			$generator->main();
			return ob_get_clean();
		} catch(Exception $e) {
			ob_end_clean();
			throw $e;
		}
	}
	
	
}
?>