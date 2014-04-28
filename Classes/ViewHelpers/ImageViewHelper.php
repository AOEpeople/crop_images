<?php
namespace Aijko\CropImages\ViewHelpers;

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

use TYPO3\CMS\Core\Resource\FileReference;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View Helper that renders a cropped image based on the FileReference Object
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	const DEFAULT_TAGNAME = 'img';

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @param FileReference $image a FAL object
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 * @param string $class
	 * @param string $tagName Sets the tag name. <img> is default
	 *
	 * @throws \Aijko\CropImages\Exception\ProcessingException
	 * @return string Rendered tag
	 */
	public function render(FileReference $image = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $class = '', $tagName = 'img') {
		if (is_null($image)) {
			throw new \Aijko\CropImages\Exception\ProcessingException('You must specify a File object.', 1398672713);
		}
		$fileId = $image->getUid();
		/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject */
		$contentObject = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
		$contentObject->start(array(), 'tt_content');
		$contentObject->setCurrentVal($fileId);
		$fileArray = $GLOBALS['TSFE']->tmpl->setup['tt_content.']['image.']['20.']['1.'];
		// Set class
		if (!empty($class)) {
			$fileArray['params'] = 'class="' . $class . '"';
		}
		// Set dimensions
		if (NULL != $width) {
			$fileArray['file.']['width'] = $width;
		}
		if (NULL != $height) {
			$fileArray['file.']['height'] = $height;
		}
		if (NULL != $minWidth) {
			$fileArray['file.']['minWidth'] = $minWidth;
		}
		if (NULL != $minHeight) {
			$fileArray['file.']['minHeight'] = $minHeight;
		}
		if (NULL != $maxWidth) {
			$fileArray['file.']['maxWidth'] = $maxWidth;
		}
		if (NULL != $maxHeight) {
			$fileArray['file.']['maxHeight'] = $maxHeight;
		}
		// Render
		$image = $contentObject->cImage($fileId, $fileArray);
		// Replace tag name
		if (self::DEFAULT_TAGNAME != $tagName) {
			$image = str_replace('<' . self::DEFAULT_TAGNAME, '<' . $tagName, $image);
			$image = str_replace('/>', '></' . $tagName . '>', $image);
		}
		return $image;
	}
}