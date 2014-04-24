<?php
namespace Aijko\CropImages\Hooks\Field;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\FormProtection\FormProtectionFactory;

/**
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Cropvalues {

	/**
	 * Generates the TCA field for the cropvalues with the wizard
	 *
	 * @param array $pa
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $fObj
	 * @return string
	 */
	public function generateField($pa, $fObj) {
		$relPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('crop_images');
		$currentRow = $pa['row'];
		$elementUid = $currentRow['uid'];
		$pageId = $currentRow['pid'];
		$moduleName = 'CropImagesCropmainmodule_CropImagesCropper';

		$urlParameters = array(
			'id' => $pageId,
			'M' => $moduleName,
			'moduleToken' => FormProtectionFactory::get()->generateToken('moduleCall', $moduleName),
			'fileReference' => $elementUid,
		);
		$url = 'mod.php?' . ltrim(GeneralUtility::implodeArrayForUrl('', $urlParameters, '', TRUE, TRUE), '&');
		return '<a href="' . $url . '"><img src="' . $relPath . 'Resources/Public/Icons/crop.png" /></a>';
	}

}

?>