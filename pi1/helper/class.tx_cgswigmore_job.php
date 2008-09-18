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

require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/class.tx_cgswigmore_helper_base.php');

/**
 * Plugin 'Company managment tool' for the 'cgswigmore' extension.
 *
 * @author	Christoph Gostner <christoph.gostner@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_cgswigmore
 */
class tx_cgswigmore_job extends tx_cgswigmore_helper_base {
	
	public function __construct() {
		parent::__construct();
		
		$this->tableKeys = array(
			'uid',
			'pid',
			'text',
			'title',
		);
	}

	protected function fillTemplate($select = array()) {
		$res = $this->getDbResult($this->conf['category.']['sort']);
	}
	
	private function fillJobTemplate($res, $template) {
		
	}
	
	private function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_jobcategory.*';
		$select['table'][] = 'tx_cgswigmore_jobcategory';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_jobcategory');
		$select['where'][] = $this->tx_reference->getLangQueryPart('tx_cgswigmore_jobcategory');
		$select['where'][] = 'tx_cgswigmore_jobcategory.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;
		
		$res = $this->getSelectDbRes($select);
	}
	
	protected function getMarker($row) {
		$markerArray = $this->getMarkerFromArr($row);
		
		return $markerArray;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_job.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_job.php']);
}

?>
