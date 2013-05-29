<?php
namespace Aijko\CropImages\Controller;

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
class ContentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \Aijko\CropImages\Service\CropValues
	 * @inject
	 */
	protected $cropValuesService;

	/**
	 * List action
	 *
	 * @return void
	 */
	public function listAction() {
		$fileReference = (int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('fileReference');
		$referer = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_REFERER');

		$fileReferenceObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($fileReference);
		$cropValues = $this->cropValuesService->getCropValuesFromFileReference($fileReferenceObject);
		$aspectRatio = $fileReferenceObject->getProperty('tx_cropimages_aspectratio');

		if (empty($cropValues)) {
			$cropValues = $this->cropValuesService->getDefaultCropValuesFromFileReference($fileReferenceObject);
		}

		$this->view->assign('aspectRatio', $aspectRatio);
		$this->view->assign('currentCropValues', $cropValues);
		$this->view->assign('imageWidth', $fileReferenceObject->getOriginalFile()->getProperty('width'));
		$this->view->assign('imageHeight', $fileReferenceObject->getOriginalFile()->getProperty('height'));
		$this->view->assign('fileReferenceObject', $fileReferenceObject);
		$this->view->assign('fileReference', $fileReference);
		$this->view->assign('referer', $referer);
	}

	/**
	 * Saves the aspect ratio
	 *
	 * @param integer $fileReference
	 * @param integer $x1
	 * @param integer $x2
	 * @param integer $y1
	 * @param integer $y2
	 * @param string $referer
	 * @return void
	 */
	public function saveAction($fileReference, $x1, $x2, $y1, $y2, $referer) {
		$fileReferenceObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($fileReference);
		$this->cropValuesService->storeCropValuesForFileReference($fileReferenceObject, $x1, $x2, $y1, $y2);

		// TODO: make flash message work, this won't display :-(
//		$this->controllerContext->getFlashMessageQueue()->addMessage(
//			new \TYPO3\CMS\Core\Messaging\FlashMessage('Fehlernachricht', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR)
//		);

		$this->redirectToUri($referer);
	}

	/**
	 * Resets the crop values
	 *
	 * @param integer $fileReference
	 * @param string $referer
	 * @return void
	 */
	public function resetAction($fileReference, $referer) {
		$fileReferenceObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($fileReference);
		$this->cropValuesService->resetCropValuesForFileReference($fileReferenceObject);

		// TODO: make flash message work, this won't display :-(
//		$this->controllerContext->getFlashMessageQueue()->addMessage(
//			new \TYPO3\CMS\Core\Messaging\FlashMessage('Fehlernachricht', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR)
//		);

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

}

?>