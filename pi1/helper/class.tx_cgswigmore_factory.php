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

require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_job.php');
require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_location.php');
require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_publication.php');
require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_section.php');
require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_staff.php');

/**
 * Plugin 'Company managment tool' for the 'cgswigmore' extension.
 *
 * @author	Christoph Gostner <christoph.gostner@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_cgswigmore
 */
class tx_cgswigmore_factory {
	
	private static $reference;

	/**
	 * Prevent instances of tx_cgswigmore_factory
	 */
	private function __construct() {
		
	}
	
	/**
	 * Set the reference to the tx_cgswigmore_pi1 class. We need this
	 * before we can call tx_cgswigmore_factory::getInstance(), because
	 * there we initialise the class with the reference and the complete
	 * configuration of extension.
	 * 
	 * @param	tx_cgswigmore_pi1	$reference: The reference to tx_cgswigmore_pi1
	 */
	public static function setExtBaseClassRef($reference) {
		self::$reference = $reference;
	}
	
	/**
	 * Make an instance of the class, set some values and
	 * return the object to the caller.
	 * 
	 * @param	string	$className: The name of the class we want an instance of
	 * @return	tx_cgswigmore_helper_base	A class that implements this abstract class
	 */
	public static function getInstance($className) {
		$obj = &t3lib_div::makeInstance($className);
		
		$obj->setConf(self::$reference->conf);
		$obj->setTxReference(self::$reference);
		
		return $obj;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_factory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_factory.php']);
}

?>
