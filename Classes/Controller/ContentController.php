<?php
namespace Aijko\CropImages\Controller;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
class ContentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \Aijko\CropImages\Service\CropValuesService
	 * @inject
	 */
	protected $cropValuesService;

	/**
	 * @var \Aijko\CropImages\Domain\Service\ReferenceFileService
	 * @inject
	 */
	protected $referenceFileService;

	/**
	 * List action
	 *
	 * @return void
	 */
	public function listAction() {
		$fileReference = (int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('fileReference');
		$referer = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_REFERER');
		$devices = \Aijko\CropImages\Utility\ResponsiveType::getAllResponsiveTypes();
		$fileReferenceObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($fileReference);
		$aspectRatio = $fileReferenceObject->getProperty('tx_cropimages_aspectratio');

		// Get the devices file objects
		$modifiedDeviceArray = array();
		foreach ($devices as $key => $label) {

			$deviceFile = $this->referenceFileService->getReferenceFileByDevice($fileReferenceObject, $key);
			$cropValues = $this->cropValuesService->getCropValuesFromFileReference($deviceFile, $key);
			if (empty($cropValues)) {
				$cropValues = $this->cropValuesService->getDefaultCropValuesFromFileReference($deviceFile);
			}
			$width = $deviceFile->getOriginalFile()->getProperty('width');
			$height = $deviceFile->getOriginalFile()->getProperty('height');

			$modifiedDeviceArray[$key] = array(
				'label' => $label ? $label : LocalizationUtility::translate('layout.responsivetype.default', $this->getExtensionName()),
				'file' => $deviceFile,
				'cropValues' => $cropValues,
				'width' => $width,
				'height' => $height
			);
		}
		$this->view->assign('aspectRatio', $aspectRatio);
		$this->view->assign('responsiveObjects', $modifiedDeviceArray);
		$this->view->assign('fileReference', $fileReference);
		$this->view->assign('referer', $referer);
	}

	/**
	 * Saves the aspect ratio
	 *
	 * @param integer $fileReference
	 * @param string $referer
	 * @return void
	 */
	public function saveAction($fileReference, $referer) {
		$devices = \Aijko\CropImages\Utility\ResponsiveType::getAllResponsiveTypes();
		$request = $this->request;
		$fileReferenceObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($fileReference);

		foreach ($devices as $key => $label) {
			$deviceFile = $this->referenceFileService->getReferenceFileByDevice($fileReferenceObject, $key);

			$x1 = $request->getArgument('x1_' . $key);
			$x2 = $request->getArgument('x2_' . $key);
			$y1 = $request->getArgument('y1_' . $key);
			$y2 = $request->getArgument('y2_' . $key);

			$this->cropValuesService->storeCropValuesForFileReference($deviceFile, $x1, $x2, $y1, $y2, $key);
		}

		// Clear the page cache
		if ($GLOBALS['BE_USER']->workspace === 0) {
			$id = (int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id');
			$dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
			$dataHandler->start(array(), array(), $GLOBALS['BE_USER']);
			$dataHandler->clear_cache('pages', $id);
		}

		// Add success message
		/** @var $flashMessage \TYPO3\CMS\Core\Messaging\FlashMessage */
		$flashMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
			LocalizationUtility::translate('message.success.description', $this->getExtensionName()),
			LocalizationUtility::translate('message.success.title', $this->getExtensionName()),
			\TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_INFO,
			TRUE
		);
		$this->controllerContext->getFlashMessageQueue()->addMessage($flashMessage);

		$this->redirectToUri($referer);
	}

	/**
	 * Closes the module
	 *
	 * @param string $referer
	 * @return void
	 */
	public function closeAction($referer) {
		$this->redirectToUri($referer);
	}

	/**
	 * Gets the current extension name
	 *
	 * @return string
	 */
	protected function getExtensionName() {
		return 'crop_images';
	}

}