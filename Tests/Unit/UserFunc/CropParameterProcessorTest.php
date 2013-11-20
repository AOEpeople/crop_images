<?php
namespace Aijko\CropImages\Tests\Unit\UserFunc;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 aijko GmbH <info@aijko.de>
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
class CropParameterProcessorTest extends AbstractProcessorTest {

	/**
	 * @var \Aijko\CropImages\UserFunc\CropParameterProcessor
	 */
	protected $fixture;

	/**
	 *
	 */
	public function setUp() {
		// Singleton; don't use object manager to get this
		$this->fixture = new \Aijko\CropImages\UserFunc\CropParameterProcessor();
	}

	/**
	 * @test
	 */
	public function verifyNoChangeInContentOnEmptyFileReference() {

		// Stub
		$stubFixture = $this->getAccessibleMock('Aijko\\CropImages\\UserFunc\\ImageSourceProcessor', array('getImageObserver', 'getCurrentReferenceFile'));
		$stubObserver = $this->getAccessibleMock('Aijko\\CropImages\\Observer\\ImageProcessing');

		$stubFixture
			->expects($this->any())
			->method('getImageObserver')
			->will($this->returnValue($stubObserver));
		$stubFixture
			->expects($this->any())
			->method('getCurrentReferenceFile')
			->will($this->returnValue(NULL));
		$this->fixture = $stubFixture;

		// Setup valid context
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->tmpl = new \stdClass();
		$GLOBALS['TSFE']->tmpl->setup = array('tt_content.' => '');

		$stubCObj = $this->getAccessibleMock('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer', array('getCurrentTable', 'getCurrentVal'));
		$stubCObj
			->expects($this->any())
			->method('getCurrentTable')
			->will($this->returnValue('tt_content'));
		$stubCObj
			->expects($this->any())
			->method('getCurrentVal')
			->will($this->returnValue(3));

		$this->fixture->cObj = $stubCObj;
		$content = $this->fixture->process('My current content', array());

		$this->assertEquals($content, 'My current content');
	}

	/**
	 * @test
	 */
	public function verifyIt() {
		$this->markTestSkipped('Skipped until we have a correct solution for cropping in respect to width and height');
	}
}
?>