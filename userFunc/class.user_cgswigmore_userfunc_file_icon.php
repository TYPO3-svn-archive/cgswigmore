<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Christoph Gostner <christoph.gostner@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * Plugin 'Company managment tool' for the 'cgswigmore' extension.
 *
 * @author	Christoph Gostner <christoph.gostner@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_cgswigmore
 */
class user_cgswigmore_userfunc_file_icon {

	function linkFileIcon(&$row, $data) {
		$ref = $data['ref'];
		$uploadDir = $ref->getUploadDir();
		$mimedir = $ref->getMimeDir();

		$filePath = $uploadDir . $row['file'];

		if (is_file($filePath)) {
			$icon = tx_cgswigmore_helper_base::getMimeTypeIcon($filePath, $mimedir);
			$typolink_conf['parameter'] = $filePath;
			return $ref->cObj->typolink($icon, $typolink_conf);
		}
		return '';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/userFunc/class.user_cgswigmore_userfunc_file_icon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/userFunc/class.user_cgswigmore_userfunc_file_icon.php']);
}
?>