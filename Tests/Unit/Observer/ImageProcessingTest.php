<?php
namespace Aijko\CropImages\Tests\Unit\Observer;

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
class ImageProcessingTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \Aijko\CropImages\Observer\ImageProcessing
	 */
	protected $fixture;

	/**
	 *
	 */
	public function setUp() {
		// Singleton; don't use object manager to get this
		$this->fixture = new \Aijko\CropImages\Observer\ImageProcessing();
	}

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function validateInitialValuesAreCorrect() {
		$this->assertEquals(0, $this->fixture->getCurrentIndex());
	}

	/**
	 * @test
	 */
	public function validateCurrentIndexIsIncrementedCorrectly() {
		// Increment once
		$this->fixture->notify('My_First_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		$this->assertEquals(0, $this->fixture->getCurrentIndex());
		// Increment twice
		$this->fixture->notify('My_First_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		$this->assertEquals(1, $this->fixture->getCurrentIndex());
		// Increment thrice
		$this->fixture->notify('My_First_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		$this->assertEquals(2, $this->fixture->getCurrentIndex());
	}

	/**
	 * @test
	 */
	public function validateResetIndexWorks() {
		// Increment once
		$this->fixture->notify('My_First_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		$this->assertEquals(0, $this->fixture->getCurrentIndex());
		// Increment twice
		$this->fixture->notify('My_First_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		$this->assertEquals(1, $this->fixture->getCurrentIndex());
		// Trigger reset
		$this->fixture->notify('My_First_Class', 3);
		$this->assertEquals(0, $this->fixture->getCurrentIndex());
	}

	/**
	 * @test
	 * @expectedException \Aijko\CropImages\Exception\Processing
	 */
	public function validationExceptionIsThrownWhenObserverIsCalledUnevenly() {
		// Register calls
		$this->fixture->notify('My_First_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		$this->fixture->notify('My_Second_Class', 1);
		// Provoke exception
		$this->fixture->notify('My_First_Class', 2);
	}

}
?>