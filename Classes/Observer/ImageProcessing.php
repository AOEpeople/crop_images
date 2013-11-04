<?php
namespace Aijko\CropImages\Observer;

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
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ImageProcessing implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var int
	 */
	protected $lastModifiedImageElement = 0;

	/**
	 * Index of the image currently being processed
	 * 0 = Default image
	 * 1-x = Responsive images, defined in the sourceCollection
	 *
	 * @var int
	 */
	protected $currentIndex = 0;

	/**
	 * Key: __CLASS__
	 * Value: Increment
	 *
	 * @var array
	 */
	protected $notificationMap = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->reset();
	}

	/**
	 * Notify the observer that something is happening in one of the processors
	 *
	 * @param string $className Name of the processor currently doing some changes
	 * @param int $imageElement uid of the image element currently being processed
	 * @return void
	 */
	public function notify($className, $imageElement) {
		// Reset if we modify a different image element
		if ($imageElement !== $this->lastModifiedImageElement) {
			$this->validateNotificationMap();
			$this->reset();
			$this->lastModifiedImageElement = $imageElement;
		}
		// Increment notification map
		if (!isset($this->notificationMap[$className])) {
			$this->notificationMap[$className] = 0;
		} else {
			$this->notificationMap[$className] ++;
		}
		$this->updateIndex();
	}

	/**
	 * Gets the current index
	 *
	 * @return int
	 */
	public function getCurrentIndex() {
		return $this->currentIndex;
	}

	/**
	 * Updates the internal index
	 *
	 * @return void
	 */
	protected function updateIndex() {
		foreach ($this->notificationMap as $className => $index) {
			if ($index > $this->currentIndex) {
				$this->currentIndex = $index;
			}
		}
	}

	/**
	 * Checks if each processor that is registered with this observer has been called an equal amount of times
	 * Exception if not, because that is a prerequisite
	 *
	 * @return void
	 * @throws \Aijko\CropImages\Exception\Processing
	 */
	protected function validateNotificationMap() {
		$globalCounter = FALSE;
		foreach ($this->notificationMap as $className => $counter) {
			// Set initial value for global counter
			if (FALSE === $globalCounter) {
				$globalCounter = $counter;
				break;
			}
			if ($counter !== $globalCounter) {
				throw new \Aijko\CropImages\Exception\Processing('Not all image processors have been called an equal amount of times.', 1383115246);
			}
		}
	}

	/**
	 * Resets the internal counters
	 *
	 * @return void
	 */
	protected function reset() {
		$this->lastModifiedImageElement = 0;
		$this->currentIndex = 0;
		$this->notificationMap = array();
	}
}
?>