<?php
namespace Aijko\CropImages\Tests\Unit\Service;

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
class CropValuesTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \Aijko\CropImages\Service\CropValues
	 */
	protected $fixture;

	/**
	 * @var object
	 */
	protected $databaseHandle;

	/**
	 *
	 */
	public function setUp() {
		$this->fixture = new \Aijko\CropImages\Service\CropValues();
		$this->databaseHandle = $GLOBALS['TYPO3_DB'];
	}

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
		$GLOBALS['TYPO3_DB'] = $this->databaseHandle;
	}

	///////////////////////////
	// Tests concerning storeCropValuesForFileReference
	///////////////////////////

	/**
	 * @test
	 */
	public function storeCropValuesForFileReferenceStoresNewValuesCorrectly() {

		// Mock
		$stubResourceFactory = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', array('getFileObject'));
		$stubResourceFactory
			->expects($this->any())
			->method('getFileObject')
			->will($this->returnValue(new \stdClass()));

		$fileReference = $this->getAccessibleMock(	'TYPO3\\CMS\\Core\\Resource\\FileReference',
													array('getUid', 'getName'),
													array(
														array(
															'uid_local' => 1,
															'name' => 'dummyName'
														),
														$stubResourceFactory
													)
		);

		$fileReference
			->expects($this->any())
			->method('getUid')
			->will($this->returnValue(1));
		$fileReference
			->expects($this->any())
			->method('getName')
			->will($this->returnValue('MyFileName.jpg'));

		// Mock static dependency
		$staticBeMockClass = $this->getMockClass(
			'\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility',
			array('getRecord')
		);

		$recordData = array(
			'tx_cropimages_cropvalues' => '', // Premise: Existing crop values are empty
		);
		$staticBeMockClass::staticExpects($this->any())
							->method('getRecord')
							->will($this->returnValue($recordData));

		// This here matters! What we expect to be stored in the DB.
		$expectedXmlOutput = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<images><image x1="0" y1="1" x2="100" y2="200" tstamp="1234567" device="0">MyFileName.jpg</image></images>';

		$dbMock = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_UPDATEquery'));
		$dbMock
			->expects($this->exactly(1))
			->method('exec_UPDATEquery')
			->with('sys_file_reference', 'uid = 1', array('tx_cropimages_cropvalues' => $expectedXmlOutput));

		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->inject($this->fixture, 'staticBackendUtilityClass', $staticBeMockClass);
		$this->fixture->storeCropValuesForFileReference($fileReference, 0, 100, 1, 200, 0, 1234567);
	}

	/**
	 * @test
	 */
	public function storeCropValuesForFileReferenceStoresNewValuesAlongsideExistingValuesForOtherDeviceCorrectly() {

		// Mock
		$stubResourceFactory = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', array('getFileObject'));
		$stubResourceFactory
			->expects($this->any())
			->method('getFileObject')
			->will($this->returnValue(new \stdClass()));

		$fileReference = $this->getAccessibleMock(	'TYPO3\\CMS\\Core\\Resource\\FileReference',
			array('getUid', 'getName'),
			array(
				array(
					'uid_local' => 1,
					'name' => 'dummyName'
				),
				$stubResourceFactory
			)
		);

		$fileReference
			->expects($this->any())
			->method('getUid')
			->will($this->returnValue(1));
		$fileReference
			->expects($this->any())
			->method('getName')
			->will($this->returnValue('MyFileName.jpg'));

		// Mock static dependency
		$staticBeMockClass = $this->getMockClass(
			'\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility',
			array('getRecord')
		);

		$recordData = array(
			// Premise: We already have an existing crop value stored with a different device
			'tx_cropimages_cropvalues' => '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<images><image x1="0" y1="1" x2="100" y2="200" tstamp="1234567" device="1">MyOtherFileName.jpg</image></images>',
		);
		$staticBeMockClass::staticExpects($this->any())
			->method('getRecord')
			->will($this->returnValue($recordData));

		// This here matters! What we expect to be stored in the DB.
		$expectedXmlOutput = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
							 '<images><image x1="0" y1="1" x2="100" y2="200" tstamp="1234567" device="1">MyOtherFileName.jpg</image>' .
							 '<image x1="3" y1="4" x2="303" y2="404" tstamp="1234567" device="0">MyFileName.jpg</image></images>';

		$dbMock = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_UPDATEquery'));
		$dbMock
			->expects($this->exactly(1))
			->method('exec_UPDATEquery')
			->with('sys_file_reference', 'uid = 1', array('tx_cropimages_cropvalues' => $expectedXmlOutput));

		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->inject($this->fixture, 'staticBackendUtilityClass', $staticBeMockClass);
		$this->fixture->storeCropValuesForFileReference($fileReference, 3, 303, 4, 404, 0, 1234567);
	}

	/**
	 * @test
	 */
	public function storeCropValuesForFileReferenceUpdatesExistingDeviceCroppingInformationCorrectly() {

		// Mock
		$stubResourceFactory = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', array('getFileObject'));
		$stubResourceFactory
			->expects($this->any())
			->method('getFileObject')
			->will($this->returnValue(new \stdClass()));

		$fileReference = $this->getAccessibleMock(	'TYPO3\\CMS\\Core\\Resource\\FileReference',
			array('getUid', 'getName'),
			array(
				array(
					'uid_local' => 1,
					'name' => 'dummyName'
				),
				$stubResourceFactory
			)
		);

		$fileReference
			->expects($this->any())
			->method('getUid')
			->will($this->returnValue(1));
		$fileReference
			->expects($this->any())
			->method('getName')
			->will($this->returnValue('MyFileName.jpg'));

		// Mock static dependency
		$staticBeMockClass = $this->getMockClass(
			'\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility',
			array('getRecord')
		);

		$recordData = array(
			// Premise: We already have an existing crop value stored with a different device
			'tx_cropimages_cropvalues' => '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<images><image x1="0" y1="1" x2="100" y2="200" tstamp="1234567" device="0">MyFileName.jpg</image></images>',
		);
		$staticBeMockClass::staticExpects($this->any())
			->method('getRecord')
			->will($this->returnValue($recordData));

		// This here matters! What we expect to be stored in the DB.
		$expectedXmlOutput = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
			'<images><image x1="3" y1="4" x2="303" y2="404" tstamp="1234567" device="0">MyFileName.jpg</image></images>';

		$dbMock = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_UPDATEquery'));
		$dbMock
			->expects($this->exactly(1))
			->method('exec_UPDATEquery')
			->with('sys_file_reference', 'uid = 1', array('tx_cropimages_cropvalues' => $expectedXmlOutput));

		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->inject($this->fixture, 'staticBackendUtilityClass', $staticBeMockClass);
		$this->fixture->storeCropValuesForFileReference($fileReference, 3, 303, 4, 404, 0, 1234567);
	}

	///////////////////////////
	// Tests concerning getCropValuesFromFileReference
	///////////////////////////

	/**
	 * @test
	 */
	public function getCropValuesFromFileReferenceReturnsEmptyArrayOnInvalidXmlCropString() {

		// Mock
		$stubResourceFactory = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', array('getFileObject'));
		$stubResourceFactory
			->expects($this->any())
			->method('getFileObject')
			->will($this->returnValue(new \stdClass()));

		$fileReference = $this->getAccessibleMock(	'TYPO3\\CMS\\Core\\Resource\\FileReference',
			array('getProperty'),
			array(
				array(
					'uid_local' => 1,
					'name' => 'dummyName'
				),
				$stubResourceFactory
			)
		);

		$fileReference
			->expects($this->any())
			->method('getProperty')
			->will($this->returnValue('NotAnXmlString'));

		$returnValue = $this->fixture->getCropValuesFromFileReference($fileReference, $deviceKey = 0);

		$this->assertSame(array(), $returnValue);
	}

	/**
	 * @test
	 */
	public function getCropValuesFromFileReferenceReturnsCorrectValuesFromValidXmlString() {

		// Mock
		$stubResourceFactory = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', array('getFileObject'));
		$stubResourceFactory
			->expects($this->any())
			->method('getFileObject')
			->will($this->returnValue(new \stdClass()));

		$fileReference = $this->getAccessibleMock(	'TYPO3\\CMS\\Core\\Resource\\FileReference',
			array('getProperty'),
			array(
				array(
					'uid_local' => 1,
					'name' => 'dummyName'
				),
				$stubResourceFactory
			)
		);

		$validXmlString = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
			'<images><image x1="0" y1="1" x2="100" y2="200" tstamp="1234567" device="1">MyOtherFileName.jpg</image>' .
			'<image x1="3" y1="4" x2="303" y2="404" tstamp="1234567" device="0">MyFileName.jpg</image></images>';

		$fileReference
			->expects($this->any())
			->method('getProperty')
			->will($this->returnValue($validXmlString));

		$returnValue = $this->fixture->getCropValuesFromFileReference($fileReference, $deviceKey = 1);

		$expectedResult = array(
			'x1' => 0,
			'x2' => 100,
			'y1' => 1,
			'y2' => 200,
		);
		$this->assertSame($expectedResult, $returnValue);
	}

	///////////////////////////
	// Tests concerning getDefaultCropValuesFromFileReference
	///////////////////////////

	/**
	 * @test
	 */
	public function getDefaultCropValuesFromFileReferenceWorksForPortraitImages() {

		// Original File Mock
		$anonymous = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\File', array('getProperty'), array(), '', FALSE);
		$anonymous
			->expects($this->any())
			->method('getProperty')
			->will($this->returnValueMap(
				array(
					array('height', 200),
					array('width', 100)
				)
			));

		// Mock
		$stubResourceFactory = $this->getAccessibleMock('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', array('getFileObject'));
		$stubResourceFactory
			->expects($this->any())
			->method('getFileObject')
			->will($this->returnValue($anonymous));

		$fileReference = $this->getAccessibleMock(	'TYPO3\\CMS\\Core\\Resource\\FileReference',
			array('getProperty', 'getOriginalFile'),
			array(
				array(
					'uid_local' => 1,
					'name' => 'dummyName'
				),
				$stubResourceFactory
			)
		);

		$fileReference
			->expects($this->any())
			->method('getProperty') // Will be aspectRatio
			->will($this->returnValue('4:3'));
		$fileReference
			->expects($this->any())
			->method('getOriginalFile')
			->will($this->returnValue($anonymous));

		$returnValue = $this->fixture->getDefaultCropValuesFromFileReference($fileReference);
		$expectedValue = array(
			'x1' => 0,
			'x2' => 100,
			'y1' => 62,
			'y2' => 138,
		);
		$this->assertEquals($returnValue, $expectedValue);
	}


}
?>