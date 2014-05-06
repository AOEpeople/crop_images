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
class ResponsiveTypeTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	protected $originalExtConf;

	/**
	 * @var array
	 */
	protected $originalTca;

	/**
	 * @var int
	 */
	protected $originalCount;

	/**
	 * @var NULL
	 */
	protected $fixture;

	/**
	 *
	 */
	public function setUp() {
		$this->originalTca = $GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsivetype']['config']['items'];
		$GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsivetype']['config']['items'] = array();

		$this->originalCount = $GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems'];
		$GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems'] = 0;

		$this->originalExtConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'];
		unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']);

		$this->fixture = NULL;
	}

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
		$GLOBALS['TCA']['sys_file_reference']['columns'] = $this->originalTca;
		$GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems'] = $this->originalCount;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'] = $this->originalExtConf;
	}

	///////////////////////////
	// Tests concerning getAllResponsiveTypes
	///////////////////////////

	/**
	 * @test
	 */
	public function getAllResponsiveTypesReturnsAllExistingResponsiveTypes() {

		$GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsivetype']['config']['items'] = array(
			0 => array(
				0 => 'My label',
				1 => 0,
			),
			1 => array(
				0 => 'My mobile label',
				1 => 1,
			),
			2 => array(
				0 => 'My tablet label',
				1 => 2,
			)
		);

		$types = \Aijko\CropImages\Utility\ResponsiveType::getAllResponsiveTypes();
		$expectedResult = array(
			0 => 'My label',
			1 => 'My mobile label',
			2 => 'My tablet label'
		);
		$this->assertEquals($expectedResult,$types);
	}

	///////////////////////////
	// Tests concerning addNewResponsiveType
	///////////////////////////

	/**
	 * @test
	 */
	public function addNewResponsiveTypeModifiesTca() {
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToTca('my-custom-type', 9);
		$types = \Aijko\CropImages\Utility\ResponsiveType::getAllResponsiveTypes();
		$expectedResult = array(
			9 => 'my-custom-type',
		);
		$this->assertEquals($expectedResult,$types);
	}

	/**
	 * @test
	 */
	public function addNewResponsiveTypeModifiesMaxResponsiveImageCount() {
		$this->assertEquals(0, $GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems']);
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToTca('Default', 0);
		// After adding initial element (default), maxitems should still be 0
		$this->assertEquals(0, $GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems']);
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToTca('Additional', 1);
		// Now it should be one
		$this->assertEquals(1, $GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems']);
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToTca('Additional-2', 2);
		// Now it should be two
		$this->assertEquals(2, $GLOBALS['TCA']['sys_file_reference']['columns']['tx_cropimages_responsiveimages']['config']['maxitems']);
	}

	/**
	 * @test
	 */
	public function addNewResponsiveTypeAddsResponsiveTypeToSourceCollection() {
		$this->assertFalse(isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']['source_collection'][9]));
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToExtconf(9, 'desktop-source-collection');
		$this->assertTrue(isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']['source_collection'][9][0]));
		$this->assertSame($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']['source_collection'][9][0], 'desktop-source-collection');

	}

}
