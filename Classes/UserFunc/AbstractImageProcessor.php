<?php
namespace Aijko\CropImages\UserFunc;

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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractImageProcessor implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \Aijko\CropImages\Observer\ImageProcessing
	 */
	protected $imageObserver = NULL;

	/**
	 * @var \Aijko\CropImages\Domain\Service\ReferenceFileService
	 */
	protected $referenceFileService = NULL;

	/**
	 * @var \Aijko\CropImages\Service\DeviceService
	 */
	protected $deviceService = NULL;

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	public $cObj;

	/**
	 * @param $content
	 * @param $configuration
	 * @return mixed
	 */
	public abstract function process($content, $configuration);

	/**
	 * Checks if the current object is actually used in the context of a userFunc
	 *
	 * @return void
	 * @throws \Aijko\CropImages\Exception\ProcessingException
	 */
	protected function validateContext() {
		if (!isset($this->cObj) || !($this->cObj instanceof \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer)) {
			throw new \Aijko\CropImages\Exception\ProcessingException('Cannot crop without a valid cObj.', 1383038383);
		}
		if (!isset($GLOBALS['TSFE']) || !isset($GLOBALS['TSFE']->tmpl->setup['tt_content.'])) {
			throw new \Aijko\CropImages\Exception\ProcessingException('Cannot crop without a given TSFE configuration.', 1383038389);
		}
		if ('tt_content' != $this->getTable() || 0 == $this->getImageReferenceUid()) {
			throw new \Aijko\CropImages\Exception\ProcessingException('Cannot process data outside the context of tt_content and a numeric image reference.', 1383059920);
		}
	}

	/**
	 * Gets the uid of the current image reference
	 *
	 * @return int
	 */
	protected function getImageReferenceUid() {
		$currentImageReference = (int) $this->cObj->getCurrentVal();
		return $currentImageReference;
	}

	/**
	 * @return array
	 */
	protected function getData() {
		$data =  $this->cObj->data;
		return $data;
	}

	/**
	 * Returns the current table
	 *
	 * @return string
	 */
	protected function getTable() {
		$table = $this->cObj->getCurrentTable();
		return $table;
	}

	/**
	 * Determines the device we are currently rendering for
	 *
	 * @return int
	 */
	protected function getDevice() {
		return $this->getDeviceService()->getDevice();
	}

	/**
	 * Gets the currently processed file reference
	 *
	 * @return null|\TYPO3\CMS\Core\Resource\FileReference
	 */
	protected function getCurrentReferenceFile() {
		$currentReferenceUid = $this->getImageReferenceUid();
		if (0 >= $currentReferenceUid) {
			return NULL;
		}
		$sysReferenceFile = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()
			->getFileReferenceObject($currentReferenceUid);
		return $sysReferenceFile;
	}

	/**
	 * Notifies the observer that the current image processor has finished his processing
	 *
	 * @return void
	 */
	protected function notifyObserver() {
		$referenceUid = $this->getImageReferenceUid();
		$this->getImageObserver()->notify(get_class($this), $referenceUid);
	}

	/**
	 * Gets the image observer
	 *
	 * @return \Aijko\CropImages\Observer\ImageProcessing
	 */
	protected function getImageObserver() {
		if (NULL === $this->imageObserver) {
			$this->imageObserver = $this->getObjectManager()->get('Aijko\\CropImages\\Observer\\ImageProcessing');
		}
		return $this->imageObserver;
	}

	/**
	 * Gets the image observer
	 *
	 * @return \Aijko\CropImages\Domain\Service\ReferenceFileService
	 */
	protected function getReferenceFileService() {
		if (NULL === $this->referenceFileService) {
			$this->referenceFileService = $this->getObjectManager()->get('Aijko\\CropImages\\Domain\\Service\\ReferenceFileService');
		}
		return $this->referenceFileService;
	}

	/**
	 * Gets the image observer
	 *
	 * @return \Aijko\CropImages\Service\DeviceService
	 */
	protected function getDeviceService() {
		if (NULL === $this->deviceService) {
			$this->deviceService = $this->getObjectManager()->get('Aijko\\CropImages\\Service\\DeviceService');
		}
		return $this->deviceService;
	}

	/**
	 * Gets the image observer
	 *
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected function getObjectManager() {
		if (NULL === $this->objectManager) {
			$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		}
		return $this->objectManager;
	}

}