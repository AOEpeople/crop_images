<?php
namespace Aijko\CropImages\Tests\Unit\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 AIJKO GmbH <info@aijko.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * Test case for class Tx_CropImages_Controller_ContentController.
 */
class ExtConfigurationTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	protected $originalExtConf;

	/**
	 * @var NULL
	 */
	protected $fixture;

	/**
	 *
	 */
	public function setUp() {
		$this->originalExtConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'];
		unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']);
		$this->fixture = NULL;
	}

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'] = $this->originalExtConf;
	}

	///////////////////////////
	// Tests concerning getResponsiveTypeBySourceCollection
	///////////////////////////

	/**
	 * @test
	 */
	public function getResponsiveTypeBySourceCollectionReturnsZeroOnUnknownSourceCollection() {

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'] = array(
			'source_collection' => array(
				1 => array(
					0 => 'tablet',
					1 => 'tablet-highres'
				),
				2 => array(
					0 => 'mobile',
					1 => 'mobile-highres',
				)
			)
		);
		$this->assertEquals(0, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('unknown-source-collection'));
	}

	/**
	 * @test
	 */
	public function getResponsiveTypeBySourceCollectionReturnsCorrectResponsiveType() {

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'] = array(
			'source_collection' => array(
				1 => array(
					0 => 'tablet',
					1 => 'tablet-highres'
				),
				2 => array(
					0 => 'mobile',
					1 => 'mobile-highres',
				)
			)
		);
		$this->assertEquals(2, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('mobile'));
		$this->assertEquals(2, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('mobile-highres'));
		$this->assertEquals(1, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('tablet'));
		$this->assertEquals(1, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('tablet-highres'));
	}

	///////////////////////////
	// Tests concerning addResponsiveTypeToSourceCollection
	///////////////////////////

	/**
	 * @test
	 */
	public function addResponsiveTypeToSourceCollectionAddsResponsiveTypeToSourceCollection() {
		\Aijko\CropImages\Utility\ExtConfiguration::addResponsiveTypeToSourceCollection(2, 'my-first-collection');
		\Aijko\CropImages\Utility\ExtConfiguration::addResponsiveTypeToSourceCollection(3, 'my-second-collection');

		$this->assertEquals(2, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('my-first-collection'));
		$this->assertEquals(3, \Aijko\CropImages\Utility\ExtConfiguration::getResponsiveTypeBySourceCollection('my-second-collection'));
	}
}
