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
class tx_cgswigmore_section extends tx_cgswigmore_helper_base {

	public function __construct() {
		parent::__construct();

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
	
	protected function fillTemplate($select = array()) {
		$uid = intval($this->getGvalue('sec_uid')); /* TODO: rename sec_uid */
		
		if (!is_null($uid) && $uid > 0) {
			$this->masterTemplateMarker = '###TEMPLATE_DETAIL###';
			$select['where'][] = 'tx_cgswigmore_section.uid = ' . $uid;
			$res = $this->getDbResult($this->conf['sort'], $select);
		} else {
			$res = $this->getDbResult($this->conf['sort'], $select);
		}

		$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_SECTION_ROW###'));
		
		$markerArray = array();
		$subpartArray['###TEMPLATE_SECTION_ROW###'] = $this->fillSectionTemplate($res, $template['item0']);
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	private function fillSectionTemplate($res, $template) {
		$content = '';

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= $this->fillRow($row, $template);
		}

		return $content;
	}
	
	private function fillRow($row, $template) {
		$markerArray = $this->getMarker($row);
		$subpartArray['###TEMPLATE_SECTION_MENU_ITEM###'] = $this->fillRowSubpartMenuTemplate($this->cObj->getSubpart($template, '###TEMPLATE_SECTION_MENU_ITEM###'));
		
		$subpartArray['###TEMPLATE_SECTION_HOME###'] = '';
		$subpartArray['###TEMPLATE_SECTION_STAFF###'] = '';
		$subpartArray['###TEMPLATE_SECTION_PUBLICATION###'] = '';
		
		$sectionSelect = intval($this->getGvalue('section::select'));
		switch ($sectionSelect) {
			case self::SECTION_SELECT_STAFF:
				$staffUidArr = self::getSectionStaffUids($row['uid']);
				$staff = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_staff');
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
				$subTemplate = $this->cObj->getSubpart($template, '###TEMPLATE_SECTION_HOME###');
				$subpartArray['###TEMPLATE_SECTION_HOME###'] = $this->cObj->substituteMarkerArrayCached($subTemplate, $markerArray);
		}
		return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	private function fillRowSubpartMenuTemplate($template) {
		$content = '';
		
		$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('section::select' => self::SECTION_SELECT_HOME), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_MENU_ITEM###'] = $this->cObj->typolink($this->tx_reference->pi_getLL('tx_cgswigmore_pi1_section_home'), $typos);
		$content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
		
		$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('section::select' => self::SECTION_SELECT_STAFF), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_MENU_ITEM###'] = $this->cObj->typolink($this->tx_reference->pi_getLL('tx_cgswigmore_pi1_section_staff'), $typos);
		$content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
		
		$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('section::select' => self::SECTION_SELECT_PUBLICATION), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_MENU_ITEM###'] = $this->cObj->typolink($this->tx_reference->pi_getLL('tx_cgswigmore_pi1_section_publications'), $typos);
		$content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
		
		return $content;
	}
	
	private function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_section.*';
		$select['table'][] = 'tx_cgswigmore_section';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_section');
		$select['where'][] = $this->tx_reference->getLangQueryPart('tx_cgswigmore_section'); /* TODO: move in abstract parent class */
		$select['where'][] = 'tx_cgswigmore_section.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;

		return self::getSelectDbRes($select);
	}

	protected function getMarker($row) {
		$markerArray = $this->getMarkerFromArr($row);
		
		$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('sec_uid' => $row['uid']), 0, 0, $this->conf['sectionUID']);
		$markerArray['###SECTION_LINK_SECTION###'] = $this->cObj->typolink($row['section'], $typos);
		$markerArray['###SECTION_LINK_TITLE###'] = $this->cObj->typolink($row['title'], $typos);
		
		$markerArray['###SECTION_PHOTO###'] = '';
		if (is_file($this->tx_reference->tx_upload_dir . $row['photo']))
			$markerArray['###SECTION_PHOTO###'] = $this->createImage($row['photo'], $this->conf['image.']);
		
		$staff = tx_cgswigmore_factory::getInstance('tx_cgswigmore_staff');
		$markerArray['###SECTION_LEADER1###'] = $staff->getNameLink($row['leader1']);
		$markerArray['###SECTION_LEADER2###'] = $staff->getNameLink($row['leader2']);
		
		return $markerArray;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_section.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_section.php']);
}

?>
