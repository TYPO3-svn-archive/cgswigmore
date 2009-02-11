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
class tx_cgswigmore_section extends tx_cgswigmore_helper_base {

	/**
	 * If the two markers ###SECTION_LEADER[1|2]### are created or not.
	 * 
	 * @var boolean
	 */
	private $createStaffLink;
	
	/**
	 * Constructor.
	 * This constructor set's the automatic generated template marker keys for the a section.
	 * Additional markers are defined in tx_cgswigmore_section->getMarker(...)
	 *
	 * @return void
	 * @author Christoph Gostner 
	 * @see pi1/helper/tx_cgswigmore_section#getMarker()
	 */
	public function __construct() {
		parent::__construct();

		$this->createStaffLink = true;
		$this->tableKeys = array(
			'uid',
			'pid',
			'section',
			'title',
			'photo',
			'description',
			'photo',
			'leader1',
			'leader2',
		);
	}
	
	/**
	 * Start filling sections in the template.
	 * The default setting for the sections is to view them all in a list. 
	 * If a section is selected the section's details are displayed, including 
	 * the staff list and the publications, depending on the selected scope.
	 *
	 * @param array $select Optional parameter to modify the SQL query 
	 * @return string The content of the sections filled in the template 
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillTemplate()
	 */
	public function fillTemplate($select = array()) {
		$uid = intval($this->getGvalue('section::select'));
		$doNotListArr = split(',', $this->conf['doNotList']);
		$doListArr = split(',', $this->conf['doList']);
		
		if (strlen($this->conf['doNotList']) > 0 && count($doNotListArr) > 0) {
			$select['where'][] = 'tx_cgswigmore_section.uid NOT IN ('.implode(',', $doNotListArr) .')';
		} 
		if (strlen($this->conf['doList']) > 0 && count($doListArr) > 0) {
			$select['where'][] = 'tx_cgswigmore_section.uid IN ('.implode(',', $doListArr) .')';
		}
		
		if (!is_null($uid) && $uid > 0) {
			$this->masterTemplateMarker = '###TEMPLATE_DETAIL###';
			
			$select['where'][] = 'tx_cgswigmore_section.uid = ' . $uid;
			$res = $this->getDbResult($this->conf['sort'], $select);
		} else {
			$res = $this->getDbResult($this->conf['sort'], $select);
		}
		$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_SECTION_ROW###'));
		
		$markerArray = array();
		$subpartArray['###TEMPLATE_SECTION_ROW###'] = $this->fillTemplateWithResource($res, $template['item0']);
	
		return $this->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This method fills the section in the template.
	 * Depending on the selected scope this method displays the section's staff list, 
	 * the section's publications or the section's details.
	 * To view the staff and publication list, the method uses the two classes
	 * tx_cgswigmore_staff and tx_cgswigmore_publication.
	 * 
	 * @param array $row The section's data
	 * @param mixed $template The template to fill
	 * @return string The template containing section's data
	 * @author Christoph Gostner
	 * @see pi1/helper/tx_cgswigmore_staff
	 * @see pi1/helper/tx_cgswigmore_publication
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillRow()
	 */
	public function fillRow($row, $template) {
		$markerArray = $this->getMarker($row);
		$subpartArray['###TEMPLATE_SECTION_MENU_ITEM###'] = $this->fillRowSubpartMenuTemplate($this->getSubTemplate($template, '###TEMPLATE_SECTION_MENU_ITEM###'), $row['uid']);
		
		$subpartArray['###TEMPLATE_SECTION_HOME###'] = '';
		$subpartArray['###TEMPLATE_SECTION_STAFF###'] = '';
		$subpartArray['###TEMPLATE_SECTION_PUBLICATION###'] = '';
		
		$sectionSelect = intval($this->getGvalue('section::scope'));
		switch ($sectionSelect) {
			case self::SECTION_SELECT_STAFF:
				$staffUidArr = self::getSectionStaffUids($row['uid']);
				$staff = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_staff');
				$staff->createSectionLinks(false);
				$staff->setMasterTemplateMarker('###TEMPLATE_SECTION###');
				$selectStaff['where'][] = 'tx_cgswigmore_staff.uid IN ('.implode(',', $staffUidArr).')';
				$subpartArray['###TEMPLATE_SECTION_STAFF###'] = $staff->init($selectStaff);
				break;
			case self::SECTION_SELECT_PUBLICATION:
				$publicationUidArr = self::getSectionPublicationUid($row['uid']);
				$publication = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_publication');
				$selectPublication['where'][] = 'tx_cgswigmore_publication.uid IN ('.implode(',', $publicationUidArr).')';
				$subpartArray['###TEMPLATE_SECTION_PUBLICATION###'] = $publication->init($selectPublication);
				break;
			case self::SECTION_SELECT_HOME: 
			default:
				$subpartArray['###TEMPLATE_SECTION_HOME###'] = $this->fillSectionDetail($row['uid'], $markerArray, $this->getSubTemplate($template, '###TEMPLATE_SECTION_HOME###')); 
		}
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * The method returns a resource with the selected sections.
	 * 
	 * @param string $sort The sort order for the sections
	 * @param array $select Optional parameter to modify the SQL query
	 * @return resource The resource holing the selected sections
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getDbResult()
	 */
	public function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_section.*';
		$select['table'][] = 'tx_cgswigmore_section';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_section');
		$select['where'][] = $this->getLangQueryPart('tx_cgswigmore_section');
		$select['where'][] = 'tx_cgswigmore_section.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;

		return self::getSelectDbRes($select);
	}

	/**
	 * This method creates the template's marker to use in the section's template.
	 * 
	 * @param array $row The data to create the section's marker 
	 * @param mixed $object Optional data to create section's template marker
	 * @return array The marker that can be used in the section's template
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getMarker()
	 */
	public function getMarker($row, $object = NULL) {
		$markerArray = $this->getMarkerFromArr($row);
		
		$parameter = $this->getLinkParameter(array('section::select' => $row['uid']), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_SECTION###'] = $this->createLink($parameter, $row['section']);
		$markerArray['###SECTION_LINK_TITLE###'] = $this->createLink($parameter, $row['title']);
		
		$markerArray['###SECTION_PHOTO###'] = '';
		if (is_file($this->getUploadDir() . $row['photo']))
			$markerArray['###SECTION_PHOTO###'] = $this->createImage($row['photo'], $this->conf['image.']);
		
		if ($this->createStaffLink) {
			$staff = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_staff');
			$staff->setMasterTemplateMarker('###TEMPLATE_SECTION_LEADER###');

			$markerArray['###SECTION_LEADER1###'] = '';
			$markerArray['###SECTION_LEADER2###'] = '';

			if (intval($row['leader1']) > 0) {
				$select1['where'][] = 'tx_cgswigmore_staff.uid = ' . $row['leader1'];
				$markerArray['###SECTION_LEADER1###'] = $staff->init($select1);
			}
			
			if (intval($row['leader2']) > 0) {
				$select2['where'][] = 'tx_cgswigmore_staff.uid = ' . $row['leader2'];
				$markerArray['###SECTION_LEADER2###'] = $staff->init($select2);
			}
		}
		return $markerArray;
	}
	
	/**
	 * This method creates the menu to select the section's detail, the publication and the staff list.
	 * 
	 * @param mixed $template The template to fill with the submenu
	 * @param int $uid The sections UID
	 * @return string The menu for the different section's scope
	 * @author Christoph Gostner
	 */
	private function fillRowSubpartMenuTemplate($template, $uid) {
		$content = '';

		$parameter = $this->getLinkParameter(array('section::scope' => self::SECTION_SELECT_HOME, 'section::select' => $uid), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_MENU_ITEM###'] = $this->createLink($parameter, $this->getLL('tx_cgswigmore_pi1_section_home'));
		$content .= $this->substituteMarkerArrayCached($template, $markerArray);
		
		$parameter = $this->getLinkParameter(array('section::scope' => self::SECTION_SELECT_STAFF, 'section::select' => $uid), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_MENU_ITEM###'] = $this->createLink($parameter, $this->getLL('tx_cgswigmore_pi1_section_staff'));
		$content .= $this->substituteMarkerArrayCached($template, $markerArray);
		
		$parameter = $this->getLinkParameter(array('section::scope' => self::SECTION_SELECT_PUBLICATION, 'section::select' => $uid), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_MENU_ITEM###'] = $this->createLink($parameter, $this->getLL('tx_cgswigmore_pi1_section_publications'));
		$content .= $this->substituteMarkerArrayCached($template, $markerArray);
		
		return $content;
	}
	
	/**
	 * This method fills the detail's section template.
	 * 
	 * @param int $uid The UID of the section
	 * @param array $markerArray The markers to fill the template with
	 * @param mixed $template The template to fill
	 * @return string The section's detail
	 * @author Christoph Gostner
	 */
	private function fillSectionDetail($uid, $markerArray, $template) {
		$fileUidArr = self::getSectionFileUid($uid);
		$res = $this->getFileDbResult($fileUidArr);

		$subpartArray['###TEMPLATE_SECTION_HOME_FILES###'] = $this->fillFile($res, $this->getSubTemplate($template, '###TEMPLATE_SECTION_HOME_FILES###')); 
		
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * This method fills the template with section's files.
	 * 
	 * @param resource $res The resource holding the files
	 * @param mixed $template The template to fill
	 * @return string The template filled with the section's file
	 * @author Christoph Gostner
	 */
	private function fillFile($res, $template) {
		$subTemplate = $this->getSubTemplate($template, '###TEMPLATE_FILE_ROW###');
		
		$content = '';
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= $this->fillFileRow($row, $subTemplate);
		}
		$subpartArray['###TEMPLATE_FILE_ROW###'] = $content;
		return $this->substituteMarkerArrayCached($template, array(), $subpartArray);
	}
	
	/**
	 * The method fills the subtemplate with a single file.
	 * 
	 * @param array $row The data to use when filling the template
	 * @param mixed $template The template to fill
	 * @return string The filled template with the file's row
	 * @author Christoph Gostner
	 */
	private function fillFileRow($row, $template) {
		$markerArray = $this->getFileMarker($row);
		
		return $this->substituteMarkerArrayCached($template, $markerArray);
	}
	
	/**
	 * This method returns the markers for the file.
	 * 
	 * @param array $row The data for the file
	 * @param mixed $object Optional parameter to create markers
	 * @return array The markers for the file item
	 * @author Christoph Gostner
	 */
	private function getFileMarker($row, $object = NULL) {
		$markerArray['###FILE_DESCRIPTION###'] = $row['description'];
		$markerArray['###FILE_FILE###'] = '';
		
		if (file_exists($this->getUploadDir() . $row['file'])) {
			$markerArray['###FILE_FILE###'] = $this->getFileIconLink($row);
		}
		return $markerArray;
	}
	
	/**
	 * Get the resource holding the selected location(s).
	 * 
	 * @param $sort How the result should be sorted
	 * @param $select Optional parameter used to modify the SQL query
	 * @return resource The resource holding the selected location(s)
	 * @author Christoph Gostner
	 */
	public function getFileDbResult($idArr) {
		$select['select'][] = 'tx_cgswigmore_files.*';
		$select['table'][] = 'tx_cgswigmore_files';
		$select['where'][] = 'tx_cgswigmore_files.uid IN ('.implode(',', $idArr).')';
		$select['sort'] = $this->conf['file.']['sort'];
		
		return self::getSelectDbRes($select);
	}
	
	/**
	 * With this method you can modify, if the two markers ###SECTION_LEADER[1|2]### are created or not.
	 * 
	 * @param boolean $staffLink If the two markers are created or not
	 * @return void
	 * @author Christoph Gostner
	 */
	public function createStaffLinks($staffLink = true) {
		$this->createStaffLink = $staffLink;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_section.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_section.php']);
}

?>
