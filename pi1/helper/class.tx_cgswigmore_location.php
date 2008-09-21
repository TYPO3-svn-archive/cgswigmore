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

require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/util/class.tx_cgswigmore_helper_base.php');

/**
 * Plugin 'Company managment tool' for the 'cgswigmore' extension.
 *
 * @author	Christoph Gostner <christoph.gostner@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_cgswigmore
 */
class tx_cgswigmore_location extends tx_cgswigmore_helper_base {

	/**
	 * Constructor.
	 * This constructor set's the automatic generated template marker keys for the a location.
	 * Additional markers are defined in tx_cgswigmore_location->getMarker(...)
	 *
	 * @return void
	 * @author Christoph Gostner 
	 * @see pi1/helper/tx_cgswigmore_location#getMarker()
	 */
	public function __construct() {
		parent::__construct();

		$this->tableKeys = array(
			'uid',
			'pid',
			'title',
			'description',
			'address',
			'zip',
			'city',
			'mail',
			'phone',
			'fax',
			'country',
		);
	}

	/**
	 * Fill the template of for the location.
	 * The location's UID has to be set per typoscript, if not, the method returns an empty string.
	 * 
	 * @param array $select An optional string to modify the SQL query
	 * @return string The filled location's template
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillTemplate()
	 */
	public function fillTemplate($select = array()) {
		$res = $this->getDbResult(NULL, array('where' => array('tx_cgswigmore_location.uid = ' . intval($this->conf['uid']))));
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 1) {
			$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_LOCATION_ROW###'));
			
			$subpartArray['###TEMPLATE_LOCATION_ROW###'] = $this->fillTemplateWithResource($res, $template['item0']);
			$markerArray = $this->getTemplateMarkers();
			
			return $this->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
		}
		return NULL;
	}
	
	/**
	 * Fill the template with the information in the passed array. 
	 * 
	 * @param array $row The array holding the information to display
	 * @param mixed $template The template to fill
	 * @return string The filled template
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillRow()
	 */
	public function fillRow($row, $template) {
		$markerArray = $this->getMarker($row);
		
		$subpartArray['###TEMPLATE_LOCATION_ROW_PHONE###'] = '';
		$subpartArray['###TEMPLATE_LOCATION_ROW_FAX###'] = '';
		$subpartArray['###TEMPLATE_LOCATION_ROW_MAIL###'] = '';
		
		if (strlen($row['phone']) > 0) {
			$subpartArray['###TEMPLATE_LOCATION_ROW_PHONE###'] = $this->substituteMarkerArrayCached($this->getSubTemplate($template, '###TEMPLATE_LOCATION_ROW_PHONE###'), $markerArray);
		}
		if (strlen($row['fax']) > 0) {
			$subpartArray['###TEMPLATE_LOCATION_ROW_FAX###'] = $this->substituteMarkerArrayCached($this->getSubTemplate($template, '###TEMPLATE_LOCATION_ROW_FAX###'), $markerArray);
		}
		if (strlen($row['mail']) > 0) {
			$subpartArray['###TEMPLATE_LOCATION_ROW_MAIL###'] = $this->substituteMarkerArrayCached($this->getSubTemplate($template, '###TEMPLATE_LOCATION_ROW_MAIL###'), $markerArray);
		}
		
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * Get the resource holding the selected location(s).
	 * 
	 * @param $sort How the result should be sorted
	 * @param $select Optional parameter used to modify the SQL query
	 * @return resource The resource holding the selected location(s)
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getDbResult()
	 */
	public function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_location.*';
		$select['table'][] = 'tx_cgswigmore_location';
		$select['where'][] = 'tx_cgswigmore_location.pid IN ('.implode(',', $idArr).')';
		
		return self::getSelectDbRes($select);
	}

	/**
	 * Generate the markers for a location.
	 * This method extend the markers created by the getMarkerFromArr method. 
	 * 
	 * @param array $row The data to create the markers
	 * @param mixed $object An optional parameter to pass additional parameters
	 * @return array The marker for a location
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getMarker()
	 */
	public function getMarker($row, $object = NULL) {
		$markerArray = $this->getMarkerFromArr($row);

		$markerArray['###LOCATION_FILE###'] = '';
		if (is_file($this->getUploadDir() . $row['file']))
			$markerArray['###LOCATION_FILE###'] = $this->createImage($row['file'], $this->conf['image.']);
		
		$markerArray['###LOCATION_COUNTRY###'] = $this->getCountryText($row['country']);
		
		$markerArray['###LOCATION_MAIL###'] = $this->createLink($row['mail']);
		
		return $markerArray;
	}
	
	/**
	 * Get the name of the location's UID to generate the markers.
	 * 
	 * @param $uid The UID of the country
	 * @return string The name of the selected location's country
	 * @author Christoph Gostener
	 */
	private function getCountryText($uid) {
		$select['select'][] = 'static_countries.*';
		$select['table'][] = 'static_countries';
		$select['where'][] = 'static_countries.uid = ' . $uid;
		$res = $this->getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) != 1) {
			return '';
		}
		
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['cn_official_name_en'];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_location.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_location.php']);
}

?>
