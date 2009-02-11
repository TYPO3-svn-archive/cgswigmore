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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_factory.php');

/**
 * Plugin 'Company managment tool' for the 'cgswigmore' extension.
 *
 * @author	Christoph Gostner <christoph.gostner@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_cgswigmore
 */
class tx_cgswigmore_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_cgswigmore_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cgswigmore_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'cgswigmore';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The upload directory
	 * 
	 * @var string
	 */
	private $tx_upload_dir;
	/**
	 * The mime type directory where the file icons are locate
	 * 
	 * @var string
	 */
	private $tx_mime_dir;
	/**
	 * The language mode
	 * 
	 * @var string
	 */
	private $sys_language_mode;
	/**
	 * The language id
	 * 
	 * @var int
	 */
	private $sys_language_id;
	
	/**
	 * Constructor.
	 * 
	 * @return void
	 * @author Christoph Gostner
	 */
	public function __construct() {
		$this->tx_upload_dir = 'uploads/tx_cgswigmore/';
		$this->tx_mime_dir = 'EXT:cgswigmore/res/img/mime/';
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content The PlugIn content
	 * @param array $conf The PlugIn configuration
	 * @return string The content that is displayed on the website
	 * @author Christoph Gostner
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// disable cache
		$GLOBALS['TSFE']->set_no_cache();

		// language mode
		$this->sys_language_mode = $this->conf['sys_language_mode'] ? $this->conf['sys_language_mode'] : $GLOBALS['TSFE']->sys_language_mode;
		$this->sys_language_id = $GLOBALS['TSFE']->sys_language_content;

		tx_cgswigmore_factory::setExtBaseClassRef(&$this);

		switch ($conf['display']) {
			case 'PUBLICATION':
			case 'STAFF':
			case 'JOB':
			case 'SECTION':
			case 'LOCATION':
				$clazz = 'tx_cgswigmore_' . strtolower($conf['display']);
				$cgswigmoreObj = &tx_cgswigmore_factory::getInstance($clazz);
				$content .= $cgswigmoreObj->init();
				break;
			default:
				$content .= $this->pi_getLL('tx_cgswigmore_pi1_nothing_to_do');
		}
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Get the upload directory of the extension.
	 * 
	 * @return string The upload directory of the extension
	 * @author Christoph Gostner
	 */
	public function getUploadDir() {
		return $this->tx_upload_dir;
	}
	
	/**
	 * Get the mime directory where the icons for the files are located.
	 * 
	 * @return string The mime directory of the icons 
	 * @author Christoph Gostner
	 */
	public function getMimeDir() {
		return $this->tx_mime_dir;
	}
	
	/**
	 * Get the language ID currently set.
	 * 
	 * @return int The language ID
	 * @author Christoph Gostner
	 */
	public function getLanguageId() {
		return $this->sys_language_id;
	}
	
	/**
	 * Get the language mode of the page.
	 * 
	 * @return string The language mode
	 * @author Christoph Gostner
	 */
	public function getLanguageMode() {
		return $this->sys_language_mode;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/class.tx_cgswigmore_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/class.tx_cgswigmore_pi1.php']);
}

?>
