<?php
namespace Aijko\CropImages\Service;

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
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CropParameterService {

	/**
	 * @var \Aijko\CropImages\Service\DeviceService
	 * @inject
	 */
	protected $deviceService;

	/**
	 * @var \Aijko\CropImages\Service\CropValuesService
	 * @inject
	 */
	protected $cropValuesService;

	/**
	 * @var \TYPO3\CMS\Core\Imaging\GraphicalFunctions
	 * @inject
	 */
	protected $gifBuilder;


	public function getCropParameter($sysReferenceUid, $processingConfiguration) {
		$parameter = '';

		$fileObject = $this->getResourceFactory()->getFileReferenceObject($sysReferenceUid);
		$device = $this->deviceService->getDevice();

		$cropValues = $this->cropValuesService->getCropValuesFromFileReference($fileObject, $device);
		if (empty($cropValues)) {
			return $parameter;
		}

		// Croparea and Offset
		$cropWidth = intval($cropValues['x2'] - $cropValues['x1']);
		$cropHeight = intval($cropValues['y2'] - $cropValues['y1']);
		$x1 = $cropValues['x1'];
		$y1 = $cropValues['y1'];

		// Get original image dimensions and adjust them to the target crop dimensions
		$info = $this->gifBuilder->getImageDimensions($fileObject->getOriginalFile()->getPublicUrl());
		$info[0] = $cropWidth;
		$info[1] = $cropHeight;

		// Get the scaling dimensions. If the width of the cropping area is smaller than
		// width of scaling area, take the cropping area width as target width to prevent scaling up
		$scaleWidth = $this->getDimension($processingConfiguration['width']);
		if ($scaleWidth > $cropWidth) {
			$scaleWidth = $cropWidth;
		}
		$scaleHeight = $this->getDimension($processingConfiguration['height']);
		if ($scaleHeight > $cropHeight) {
			$scaleHeight = $cropHeight;
		}

		// Convert the processingConfiguration into options
		$options = array(
			'maxW' => (int)$processingConfiguration['maxWidth'],
			'minW' => (int)$processingConfiguration['maxHeight'],
			'maxH' => (int)$processingConfiguration['maxWidth'],
			'minH' => (int)$processingConfiguration['minHeight'],

		);

		// Get scale
		$data = $this->gifBuilder->getImageScale($info, $scaleWidth, $scaleHeight, $options);

		// Build crop command
		$targetWidth = $data[0];
		$targetHeight = $data[1];

		$parameter = ' -crop ' . $cropWidth . 'x' . $cropHeight . '+' . $x1 . '+' . $y1 . ' -geometry ' . $targetWidth . 'x' . $targetHeight;

//		\t3lib_utility_Debug::debug($parameter, __LINE__);
//		die;
		return $parameter;
	}

	/**
	 * Removes processing information (like "c" or "m" from the dimensions)
	 *
	 * @param string $dimension
	 * @return int
	 */
	protected function getDimension($dimension) {
		$newDimension = (int)preg_replace('/(c\-?[0-9]*)|(m\-?[0-9]*)/i', '', $dimension);
		return $newDimension;
	}


	/**
	 * Get instance of FAL resource factory
	 *
	 * @return \TYPO3\CMS\Core\Resource\ResourceFactory
	 */
	protected function getResourceFactory() {
		return \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
	}
}