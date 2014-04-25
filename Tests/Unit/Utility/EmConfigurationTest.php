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
class EmConfigurationTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	protected $originalExtConf;

	/**
	 * @var NULL
	 */
	protected $fixture;

	/**
	 *
	 */
	public function setUp() {
		$this->originalExtConf = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['crop_images'];
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['crop_images']);
		$this->fixture = NULL;
	}

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['crop_images'] = $this->originalExtConf;
	}

	/**
	 * @test
	 */
	public function validateParseSettingsParsesSettings() {
		$extConf = array(
			'randomValue' => 1,
			'anotherRandomValue' => 2,
		);
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['crop_images'] = serialize($extConf);
		$parsedSettings = \Aijko\CropImages\Utility\EmConfiguration::parseSettings();
		$this->assertSame($extConf, $parsedSettings);
	}

	/**
	 * @test
	 */
	public function validateGetSettingReturnsNullOnUnknownSetting() {
		$extConf = array(
			'randomValue' => 1,
			'anotherRandomValue' => 2,
		);
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['crop_images'] = serialize($extConf);
		$setting = \Aijko\CropImages\Utility\EmConfiguration::getSetting('UnknownKey');
		$this->assertSame(NULL, $setting);
	}

	/**
	 * @test
	 */
	public function validateGetSettingReturnsSingleSettingWhenItIsSet() {
		$extConf = array(
			'randomValue' => 1,
			'anotherRandomValue' => 2,
		);
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['crop_images'] = serialize($extConf);
		$setting = \Aijko\CropImages\Utility\EmConfiguration::getSetting('anotherRandomValue');
		$this->assertSame(2, $setting);
	}

}
