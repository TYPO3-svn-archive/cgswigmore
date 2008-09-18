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
class tx_cgswigmore_staff extends tx_cgswigmore_helper_base {
	
	public function __construct() {
		parent::__construct();
		
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

	protected function fillTemplate($select = array()) {
		/*
		 * Get the staff UID. If the UID is set (x != NULL && x > 0), we have selected
		 * a staff and display him in detail view.
		 *
		 * @var int
		 */
		$uid = intval($this->getGvalue('sta_uid')); /* TODO: rename sta_uid */

		if (!is_null($uid) && $uid > 0) {
			$this->masterTemplateMarker = '###TEMPLATE_DETAIL###';
			$select['where'][] = 'tx_cgswigmore_staff.uid = ' . $uid;
			$res = $this->getDbResult($this->conf['sort'], $select);
		} else {
			$res = $this->getDbResult($this->conf['sort'], $select);
		}

		$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_STAFF_ROW###'));
		$markerArray = $this->getPageMarker();
		$subpartArray['###TEMPLATE_STAFF_ROW###'] = $this->fillStaffTemplate($res, $template['item0']);
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}

	private function fillStaffTemplate($res, $template) {
		$content = '';
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= $this->fillRow($row, $template);
		}
		
		return $content;
	}
	
	private function fillRow($row, $template) {
		$markerArray = $this->getMarker($row);
		$subpartArray['###STAFF_PHONE_ITEM###'] = $this->fillRowSubpartTemplate($row, 'phone', $template, '###STAFF_PHONE_ITEM###', $markerArray);
		$subpartArray['###STAFF_MOBILE_ITEM###'] = $this->fillRowSubpartTemplate($row, 'mobile', $template, '###STAFF_MOBILE_ITEM###', $markerArray);
		$subpartArray['###STAFF_FAX_ITEM###'] = $this->fillRowSubpartTemplate($row, 'fax', $template, '###STAFF_FAX_ITEM###', $markerArray);
		$subpartArray['###STAFF_MAIL_ITEM###'] = $this->fillRowSubpartTemplate($row, 'mail', $template, '###STAFF_MAIL_ITEM###', $markerArray);
		$subpartArray['###TEMPLATE_STAFF_PUBLICATION###'] = $this->fillRowSubpartPublicationTemplate($template, $row['uid']);

		return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}

	private function fillRowSubpartTemplate($row, $columnName, $template, $subpartName, $markerArray) {
		if (strlen($row[$columnName]) > 0) {
			$sTemplate = $this->cObj->getSubpart($template, $subpartName);
			return $this->cObj->substituteMarkerArrayCached($sTemplate, $markerArray);
		}
		return '';
	}
	
	private function fillRowSubpartPublicationTemplate($template, $uid) {
		$publicationUidArr = self::getUserPublicationUids($uid);
		
		$publ = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_publication');
		$select['where'][] = 'tx_cgswigmore_publication.uid IN ('.implode(',', $publicationUidArr).')';

		return $publ->init($select);
	}
	
	private function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_staff.*';
		$select['table'][] = 'tx_cgswigmore_staff';
		$select['where'][] = 'tx_cgswigmore_staff.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;

		// find out if the current or the archived members should be displayed.
		$staffSelect = intval($this->getGvalue('sta_state')); /* TODO: rename sta_state */
		
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
	
	protected function getMarker($row) {
		$markerArray = $this->getMarkerFromArr($row);
		
		if (isset($this->conf['link.']['sprintf'])) {
			$f = $row['firstname'];
			$l = $row['name'];
			$fl = sprintf($this->conf['link.']['sprintf'], $f, $l);
			$lf = sprintf($this->conf['link.']['sprintf'], $l, $f);

			$markerArray['###STAFF_SPRINTF_FIRSTNAME_NAME###'] = $fl;
			$markerArray['###STAFF_SPRINTF_NAME_FIRSTNAME###'] = $lf;
			
			$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('sta_uid' => $row['uid']), 0, 0, $this->conf['staffUID']); /* TODO: rename sta_uid */
			
			$markerArray['###STAFF_LINK_SPRINTF_FIRSTNAME###'] = $this->cObj->typolink($f, $typos);
			$markerArray['###STAFF_LINK_SPRINTF_NAME###'] = $this->cObj->typolink($l, $typos);
			
			$markerArray['###STAFF_LINK_SPRINTF_FIRSTNAME_NAME###'] = $this->cObj->typolink($fl, $typos);
			$markerArray['###STAFF_LINK_SPRINTF_NAME_FIRSTNAME###'] = $this->cObj->typolink($lf, $typos); 
		}
		
		$markerArray['###STAFF_IMAGE###'] = '';
		if (is_file($this->tx_reference->tx_upload_dir . $row['image']))
			$markerArray['###STAFF_IMAGE###'] = $this->createImage($row['image'], $this->conf['image.']);
		
		$markerArray['###STAFF_LINK_SECTION###'] = $this->getSectionMarker($row['uid']);
		$markerArray['###STAFF_SECTION###'] = $this->getSectionMarker($row['uid'], false);
		
		return $markerArray;
	}
	
	function getSectionMarker($uid, $link = true) {
		$sectionUidArr = $this->getStaffSectionMarker($uid);
		
		if (is_null($sectionUidArr) || count($sectionUidArr) == 0)
			return '';
		
		/* TODO: move the rest of the function to the tx_cgswigmore_section class */
		/* now get all the sections of the user */
		$select['select'][] = 'tx_cgswigmore_section.*';
		$select['table'][] = 'tx_cgswigmore_section';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_section');
		$select['where'][] = implode(' OR ', $sectionUidArr);
		$select['where'][] = $this->tx_reference->getLangQueryPart('tx_cgswigmore_section'); /* TODO: language overlay */
		$select['sort'] = $this->txConf['section.']['sort'];
		$res = self::getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return NULL;
		
		$title = array();
		$dnL = split(',', $this->conf['section.']['doNotLink']);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$row = $this->tx_reference->getLanguageOverlay($row, 'tx_cgswigmore_section'); /* TODO: language overlay */
			$sectionUID = $row['uid'];
			
			if ($link && !in_array($sectionUID, $dnL)) {
				$p_uid = $this->txConf['section.']['sectionUID'];
				$link = $this->tx_reference->pi_linkTP_keepPIvars_url(array('sec_uid' => $row['uid']), 0, 0, $p_uid); /* TODO: rename sec_uid */
				$typolink_conf['parameter'] = $link;
				$title[] = $this->cObj->typolink($row['title'], $typolink_conf);
			} else {
				$title[] = $row['title'];
			}
		}
		return implode(', ', $title);
	}
	
	private function getStaffSectionMarker($uid) {
		/* get all connections to sections for this member */
		$select['select'][] = 'tx_cgswigmore_staff_section_mm.uid_foreign'; /* TODO: move to abstract parent class */
		$select['table'][] = 'tx_cgswigmore_staff_section_mm';
		$select['where'][] = 'tx_cgswigmore_staff_section_mm.uid_local = ' . $uid;
		$res = self::getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return NULL;
		
		$sectiunUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
			$sectiunUidArr[] = ' tx_cgswigmore_section.uid = ' . $row['uid_foreign'];
		
		return $sectiunUidArr;
	}
	
	private function getPageMarker() {
		$linkClass = $this->conf['switch.']['linkClass'];
		$markerArray = $this->tx_reference->getTemplateMarkers();
		$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('sta_state' => self::STAFF_SELECT_ARCHIVED)) . ' _SELF ' . $linkClass;
		$markerArray['###STAFF_SWITCH_CA_VIEW_ARCHIVED_LINK###'] = $this->cObj->typolink($this->tx_reference->pi_getLL('tx_cgswigmore_pi1_section_staff_link_previous'), $typos);
		$typos['parameter'] = $this->tx_reference->pi_linkTP_keepPIvars_url(array('sta_state' => self::STAFF_SELECT_CURRENT)) . ' _SELF ' . $linkClass;
		$markerArray['###STAFF_SWITCH_CA_VIEW_CURRENT_LINK###'] = $this->cObj->typolink($this->tx_reference->pi_getLL('tx_cgswigmore_pi1_section_staff_link_current'), $typos);
		
		return $markerArray;
	}
	
	public function getNameLink($uid) {
		$res = $this->getDbResult(NULL, array('where' => array('tx_cgswigmore_staff.uid = ' . $uid)));
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 1) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$markerArray = $this->getMarker($row);
			
			return $markerArray['###STAFF_LINK_SPRINTF_NAME###'];
		}
		return NULL;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_staff.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_staff.php']);
}

?>
