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
class tx_cgswigmore_staff extends tx_cgswigmore_helper_base {
	
	private $createSectionLink;

	/**
	 * Constructor.
	 * This constructor set's the automatic generated template marker keys for the a staff.
	 * Additional markers are defined in tx_cgswigmore_staff->getMarker(...)
	 *
	 * @return void
	 * @author Christoph Gostner 
	 * @see pi1/helper/tx_cgswigmore_staff#getMarker()
	 */
	public function __construct() {
		parent::__construct();
		
		$this->createSectionLink = true;
		$this->tableKeys = array(
			'uid',
			'pid',
			'title',
			'firstname',
			'name',
			'description',
			'phone',
			'mobile',
			'image',
			'fax',
			'mail'
		);
	}

	/**
	 * Fill the template for the employee.
	 * If a staff id is selected the masterTemplateMarker is set to 
	 * ###TEMPLATE_DETAIL###.
	 *
	 * @return string The filled template with the employee
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillTemplate()
	 */
	public function fillTemplate($select = array()) {
		/*
		 * Get the staff UID. If the UID is set (x != NULL && x > 0), we have selected
		 * a staff and display him in detail view.
		 *
		 * @var int
		 */
		$uid = intval($this->getGvalue('staff::select'));

		if (!is_null($uid) && $uid > 0) {
			$this->masterTemplateMarker = '###TEMPLATE_DETAIL###';
			$select['where'][] = 'tx_cgswigmore_staff.uid = ' . $uid;
			$res = $this->getDbResult($this->conf['sort'], $select);
		} else {
			$res = $this->getDbResult($this->conf['sort'], $select);
		}

		$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_STAFF_ROW###'));
		$markerArray = $this->getPageMarker();
		$subpartArray['###TEMPLATE_STAFF_ROW###'] = $this->fillTemplateWithResource($res, $template['item0']);
		
		return $this->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This method fills a single staff member in the subtemplate.
	 *
	 * @param array	$row The employee's data
	 * @param mixed	$template The subtemplate to fill
	 * @return string The content of the staff member filled in the subtemplate
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillRow()
	 */
	public function fillRow($row, $template) {
		$markerArray = $this->getMarker($row);
		$subpartArray['###STAFF_PHONE_ITEM###'] = $this->fillRowSubpartTemplate($row, 'phone', $template, '###STAFF_PHONE_ITEM###', $markerArray);
		$subpartArray['###STAFF_MOBILE_ITEM###'] = $this->fillRowSubpartTemplate($row, 'mobile', $template, '###STAFF_MOBILE_ITEM###', $markerArray);
		$subpartArray['###STAFF_FAX_ITEM###'] = $this->fillRowSubpartTemplate($row, 'fax', $template, '###STAFF_FAX_ITEM###', $markerArray);
		$subpartArray['###STAFF_MAIL_ITEM###'] = $this->fillRowSubpartTemplate($row, 'mail', $template, '###STAFF_MAIL_ITEM###', $markerArray);
		$subpartArray['###TEMPLATE_STAFF_PUBLICATION###'] = $this->fillRowSubpartPublicationTemplate($template, $row['uid']);

		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * The method returns the selected employee. 
	 * If the staff's scope is set, the SQL query is modified to only get those 
	 * members, that are archived/no more active members and the masterTemplateMarker
	 * is set to ###TEMPLATE_ARCHIVED###.
	 * 
	 * @param string $sort Sort order for the result
	 * @param array $select Optional predefined SQL arguments
	 * @return resource The resource holding the selected employee
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getDbResult()
	 */
	public function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_staff.*';
		$select['table'][] = 'tx_cgswigmore_staff';
		$select['where'][] = 'tx_cgswigmore_staff.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;

		// find out if the current or the archived members should be displayed.
		$staffSelect = intval($this->getGvalue('staff::scope'));
		
		if ($staffSelect === self::STAFF_SELECT_ARCHIVED) {
			$this->masterTemplateMarker = '###TEMPLATE_ARCHIVED###';
			$staffSelect = self::STAFF_SELECT_CURRENT;
			$select['where'][] = $this->getWhereAdd('tx_cgswigmore_staff', 1);
		} else {
			$staffSelect = self::STAFF_SELECT_ARCHIVED;
			$select['where'][] = $this->getWhereAdd('tx_cgswigmore_staff');
		}
		return self::getSelectDbRes($select);
	}
	
	/**
	 * This function sets all markers for the staff.
	 *
	 * @param array $row The staff's information.
	 * @return An array with the staff's markers.
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getMarker()
	 */
	public function getMarker($row, $object = NULL) {
		$markerArray = $this->getMarkerFromArr($row);
		
		if (isset($this->conf['link.']['sprintf'])) {
			$f = $row['firstname'];
			$l = $row['name'];
			$fl = sprintf($this->conf['link.']['sprintf'], $f, $l);
			$lf = sprintf($this->conf['link.']['sprintf'], $l, $f);

			$markerArray['###STAFF_SPRINTF_FIRSTNAME_NAME###'] = $fl;
			$markerArray['###STAFF_SPRINTF_NAME_FIRSTNAME###'] = $lf;
			
			$typos['parameter'] = '';
			$parameter = $this->getLinkParameter(array('staff::select' => $row['uid']), 0, 0, $this->conf['staffUID']);
			$markerArray['###STAFF_LINK_SPRINTF_FIRSTNAME###'] = $this->createLink($parameter, $f);
			$markerArray['###STAFF_LINK_SPRINTF_NAME###'] = $this->createLink($parameter, $l);
			
			$markerArray['###STAFF_LINK_SPRINTF_FIRSTNAME_NAME###'] = $this->createLink($parameter, $fl);
			$markerArray['###STAFF_LINK_SPRINTF_NAME_FIRSTNAME###'] = $this->createLink($parameter, $lf); 
		}
		
		$markerArray['###STAFF_MAIL###'] = $this->createLink($row['mail']);
		$markerArray['###STAFF_IMAGE###'] = '';
		if (is_file($this->getUploadDir() . $row['image']))
			$markerArray['###STAFF_IMAGE###'] = $this->createImage($row['image'], $this->conf['image.']);
		
		if ($this->createSectionLink) {
			$markerArray['###STAFF_LINK_SECTION###'] = $this->getSectionMarker($row['uid']);
			$markerArray['###STAFF_SECTION###'] = $this->getSectionMarker($row['uid'], false);
		}
		
		return $markerArray;
	}

	/**
	 * The method fills the subtemplate for the phone number, the mobile number, fax number and email.
	 * 
	 * @param array $row The employee's data
	 * @param string $columnName The name of the column in the data array
	 * @param mixed $template The template to fill
	 * @param string $subpartName The name of the sub template to fill
	 * @param array $markerArray The markers that can be used in the template
	 * @return string The filled subtemplate
	 * @author Christoph Gostner
	 */
	private function fillRowSubpartTemplate($row, $columnName, $template, $subpartName, $markerArray) {
		if (strlen($row[$columnName]) > 0) {
			$sTemplate = $this->getSubTemplate($template, $subpartName);
			return $this->substituteMarkerArrayCached($sTemplate, $markerArray);
		}
		return '';
	}
	
	/**
	 * Fill the subpart template with all the publication's of the staff member.
	 * 
	 * @param mixed $template
	 * @param int $uid
	 * @return string
	 * @author Christoph Gostner
	 * @see pi1/helper/tx_cgswigmore_publication
	 */
	private function fillRowSubpartPublicationTemplate($template, $uid) {
		$publicationUidArr = self::getUserPublicationUids($uid);
		
		$publ = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_publication');
		$select['where'][] = 'tx_cgswigmore_publication.uid IN ('.implode(',', $publicationUidArr).')';

		return $publ->init($select);
	}
	
	/**
	 * The method fills the sections in the template.
	 * This method uses the tx_cgswigmore_section class to fill the template.
	 * Thru the tx_cgswigmore_factory class we get a reference to the the
	 * tx_cgswigmore_section class. For this reason the template used for the 
	 * section list is defined in the section's template, and not in the staff's 
	 * template. 
	 * To get only those sections that are referenced to the selected 
	 * employee, the section's query is modify to only contain this sections.
	 * 
	 * @param int $uid The staff's UID
	 * @param boolean $link Which master template marker should be used, linked or not?
	 * @return string
	 * @author Christoph Gostner
	 * @see pi1/helper/tx_cgswigmore_section
	 */
	private function getSectionMarker($uid, $link = true) {
		$content = '';
		if ($this->createSectionLink) {
			$sectionUidArr = self::getStaffSectionUid($uid);
			$section = tx_cgswigmore_factory::getInstance('tx_cgswigmore_section');
			$section->createStaffLinks(false);
			$idArr = split(',', $this->conf['section.']['doNotLink']);
			
			if ($link) {
				$diff = array_diff($sectionUidArr, $idArr);
				if (count($diff) == 0) $diff[] = 0;
				
				$section->setMasterTemplateMarker('###TEMPLATE_STAFF_LINK_SECTION###');
				$select['where'][] = 'tx_cgswigmore_section.uid IN (' . implode(',', $diff) . ')';
				$content = $section->init($select); // first those which should be linked
				
				$intersect = array_intersect($sectionUidArr, $idArr);
				if (count($intersect) > 0) {
					$section->setMasterTemplateMarker('###TEMPLATE_STAFF_SECTION###');
					$select = array();
					$select['where'][] = 'tx_cgswigmore_section.uid IN (' . implode(',', $intersect) . ')';
					$content .= $section->init($select); // then those without links
				}
			} else {
				$select['where'][] = 'tx_cgswigmore_section.uid IN (' . implode(',', $sectionUidArr) . ')'; 
				$section->setMasterTemplateMarker('###TEMPLATE_STAFF_SECTION###');
				$content = $section->init($select);
			}
		}
		return $content;
	}
	
	/**
	 * This method creates the markers that are used to select the current and the archived members.
	 * 
	 * @return array The markers for the template 
	 * @author Christoph Gostner
	 */
	private function getPageMarker() {
		$linkClass = $this->conf['switch.']['linkClass'];
		$markerArray = $this->getTemplateMarkers();
		$parameter = $this->getLinkParameter(array('staff::scope' => self::STAFF_SELECT_ARCHIVED)) . ' _SELF ' . $linkClass;
		$markerArray['###STAFF_SWITCH_CA_VIEW_ARCHIVED_LINK###'] = $this->createLink($parameter, $this->getLL('tx_cgswigmore_pi1_section_staff_link_previous'));
		$parameter = $this->getLinkParameter(array('staff::scope' => self::STAFF_SELECT_CURRENT)) . ' _SELF ' . $linkClass;
		$markerArray['###STAFF_SWITCH_CA_VIEW_CURRENT_LINK###'] = $this->createLink($parameter, $this->getLL('tx_cgswigmore_pi1_section_staff_link_current'));
		
		return $markerArray;
	}
	
	/**
	 * With this method you can modify, if the staff markers contain also the employee's sections.
	 * 
	 * @param boolean $sectionLink If the two markers are created or not
	 * @return void
	 * @author Christoph Gostner
	 */
	public function createSectionLinks($sectionLink = true) {
		$this->createSectionLink = $sectionLink;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_staff.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_staff.php']);
}

?>
