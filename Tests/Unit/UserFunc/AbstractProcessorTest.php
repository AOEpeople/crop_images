<?php
namespace Aijko\CropImages\Tests\Unit\UserFunc;

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
 * Abstract test case for all processor test cases
 * Will verify that they all respect context and notify their observers
 */
abstract class AbstractProcessorTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
		unset($GLOBALS['TSFE']);
	}

	/**
	 * @test
	 * @expectedException \Aijko\CropImages\Exception\ProcessingException
	 */
	public function triggerExceptionOnInvalidCObjectContext() {
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->tmpl = new \stdClass();
		$GLOBALS['TSFE']->tmpl->setup = array('tt_content.' => '');

		$this->fixture->process('', array());

		unset($GLOBALS['TSFE']);
	}

	/**
	 * @test
	 * @expectedException \Aijko\CropImages\Exception\ProcessingException
	 */
	public function triggerExceptionOnInvalidTsfeContext() {
		$this->fixture->cObj = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
		$this->fixture->process('', array());
	}

	/**
	 * @test
	 * @expectedException \Aijko\CropImages\Exception\ProcessingException
	 */
	public function triggerExceptionOnInvalidTableContext() {
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->tmpl = new \stdClass();
		$GLOBALS['TSFE']->tmpl->setup = array('tt_content.' => '');

		$stubCObj = $this->getAccessibleMock('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer', array('getCurrentTable', 'getCurrentVal'));
		$stubCObj
			->expects($this->any())
			->method('getCurrentTable')
			->will($this->returnValue('Some_invalid_table_that_is_not_ttcontent')); // not tt_content
		$stubCObj
			->expects($this->any())
			->method('getCurrentVal')
			->will($this->returnValue(-1));

		$this->fixture->cObj = $stubCObj;
		$this->fixture->process('', array());

		unset($GLOBALS['TSFE']);
	}

	/**
	 * @test
	 * @expectedException \Aijko\CropImages\Exception\ProcessingException
	 */
	public function triggerExceptionOnInvalidContentValueContext() {
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
			->will($this->returnValue('non_integer')); // Not an integer

		$this->fixture->cObj = $stubCObj;
		$this->fixture->process('', array());

		unset($GLOBALS['TSFE']);
	}

	/**
	 * @test
	 */
	public function validContextWillNotGetAnException() {
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
			->will($this->returnValue(-1));

		$this->fixture->cObj = $stubCObj;
		$this->fixture->process('', array());

		$this->assertTrue(TRUE); // If we get to here, we are good and everything is working as expected

		unset($GLOBALS['TSFE']);
	}

	/**
	 * @test
	 */
	public function verifyObserverIsCalledByProcessor() {

		// Stub
		$stubFixture = $this->getAccessibleMock('Aijko\\CropImages\\UserFunc\\ImageSourceProcessor', array('getImageObserver'));
		$stubObserver = $this->getAccessibleMock('Aijko\\CropImages\\Observer\\ImageProcessing', array('notify'));

		$stubObserver
			->expects(($this->exactly(1)))
			->method('notify')
			->withAnyParameters();
		$stubFixture
			->expects($this->any())
			->method('getImageObserver')
			->will($this->returnValue($stubObserver));
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
			->will($this->returnValue(-1));

		$this->fixture->cObj = $stubCObj;
		$this->fixture->process('', array());

		$this->assertTrue(TRUE); // If we get to here, we are good and everything is working as expected
	}
}
