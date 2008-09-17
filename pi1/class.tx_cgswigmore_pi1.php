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

define('TX_CGSWIGMORE_PI1_SECT_HOME', '1');
define('TX_CGSWIGMORE_PI1_SECT_STAFF', '2');
define('TX_CGSWIGMORE_PI1_SECT_PUBLICATIONS', '3');

define('TX_CGSWIGMORE_PI1_STAFF_CURRENT', '4');
define('TX_CGSWIGMORE_PI1_STAFF_ARCHIVED', '5');

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

	var $tx_upload_dir = 'uploads/tx_cgswigmore/';
	var $tx_mime_dir = 'EXT:cgswigmore/res/img/mime/';
	var $sys_language_mode;
	var $sys_language_id;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
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
				#$this->displayPublications($conf['publication.']);
				$cgswigmoreObj = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_publication');
				$content .= $cgswigmoreObj->init();
				break;
			case 'STAFF':
				#$content .= $this->displayStaff($conf['staff.']);
				$cgswigmoreObj = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_staff');
				$content .= $cgswigmoreObj->init();
				break;
			case 'JOB':
				$content .= $this->displayJobs($conf['job.']);
				break;
			case 'SECTION':
				$content .= $this->displaySections($conf['section.']);
				#$cgswigmoreObj = &tx_cgswigmore_factory::getInstance('tx_cgswigmore_section');
				#$content .= $cgswigmoreObj->init();
				break;
			case 'LOCATION':
				$content .= $this->displayLocation($conf['location.']);
				break;
			default: 
				$content .= $this->pi_getLL('tx_cgswigmore_pi1_nothing_to_do');
		}
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	
	
	
	
	function getTemplateMarkers() { /* TODO */
		$markerArray = array();
		/* publication */
		$markerArray['###TEMPLATE_PUBLICATION_PAGE_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_publication_title');
		$markerArray['###TEMPLATE_VIEW_LPY_SUBMIT###'] = 'Submit'; // TODO: ll key
		$markerArray['###TEMPLATE_VIEW_LPY_ACTION_URL###'] = $this->pi_linkTP_keepPIvars_url();
		/* staff */
		$markerArray['###TEMPLATE_STAFF_PAGE_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_staff_title');
		$markerArray['###TEMPLATE_STAFF_LINK_CURRENT###'] = 'Current/Archived'; // TODO: ll key
		$markerArray['###TEMPLATE_STAFF_LINK_ARCHIVED###'] = 'Current/Archived'; // TODO: ll key
		
		return $markerArray;
	}
	
	/* ###COMMON_FUNCTIONS### begin */
	/**
	 * Get a part of the where query. User can decide if he want to see hidden
	 * records or not. 
	 *
	 * @param string $table The name of the table.
	 * @param int $hidden To decide if hidden recors should be displayed.
	 * @return The generated where query part.
	 */
	function getWhereAdd($table, $hidden = 0) {
		return $table . '.deleted = 0 AND ' . $table . '.hidden = ' . $hidden;
	}
	
	/**
	 * This functions sets the templateCode attribute. If the templateCode is set
	 * correct and it's a file the function returns true, else false.
	 * 
	 * @param array $conf The configuration for the extension.
	 * @return boolean true if the templateCode is set correcty.
	 */
	function setTemplate($conf) {
		$this->templateCode = $this->cObj->fileResource($conf['templateFile']);
		
		return !is_null($this->templateCode);
	}
	
	/**
	 * This function gets the language code from the translation file and returns it 
	 * so that the user in the front end sees, that the templateCode wasn't set 
	 * correctly in the extension's configuration.
	 *
	 * @return string The error message, that the template wasn't found.
	 */
	function getTemplateNotFountError() {
		return $this->pi_getLL('tx_cgswigmore_pi1_template_not_found');
	}
	
	/**
	 * This function gets template suparts out of the main template. 
	 * The parameter $total declears the template part to select, the items
	 * declear subparts of the main template code.
	 *
	 * @param string $total The main template part.
	 * @param array $items The subparts to select.
	 * @return The template and subparts in one array.
	 */
	function getTemplateParts($total, $items) {
		$template['total'] = $this->cObj->getSubpart($this->templateCode, $total);
		
		for ($i = 0; $i < count($items); $i++) {
			$template['item' . $i] = $this->cObj->getSubpart($template['total'], $items[$i]);
		}
		return $template;
	}
	
	/**
	 * This function searchs the URL for parameters for the extension.
	 * If the parameter is found the value will be returned, if not the
	 * error code NULL is returned.
	 * 
	 * @param string $name The name of the extension's parameter.
	 * @return mixed The value of the extension's parameter.
	 */
	function getParameter($name) {
		if (!is_null(t3lib_div::_GET('tx_cgswigmore_pi1'))) {
			$arr = t3lib_div::_GET('tx_cgswigmore_pi1');
			
			if (isset($arr[$name])) {
				return $arr[$name];
			}
		}
		return NULL;
	}
	
	// TODO extend to support arrays as name or paths
	function getConfValue($conf, $name) {
		return $conf[$name];
	}
	
	/** 
	 * Execute a select query on the database and return the 
	 * result set.
	 *
	 * @param array $values The select fields, the table ... for the query.
	 * @param boolean $debug Print the query to execute (default = false).
	 * @return The resulting set of values.
	 */
	function getSelectDbRes($values, $debug = false) {
		if ($debug) {
			$query = $GLOBALS['TYPO3_DB']->SELECTquery(implode(', ', $values['select']),
					implode(', ', $values['table']),
					implode(' AND ', $values['where']),
					$values['group'],
					$values['sort'],
					$values['limit']
			);
			t3lib_div::debug($query);
		}
	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			implode(', ', $values['select']),
			implode(', ', $values['table']),
			implode(' AND ', $values['where']),
			$values['group'],
			$values['sort'],
			$values['limit']
		);
		return $res;
	}
	
	/**
	 * This function generates a picture with the given file
	 * and configuration and returns the image resource to use
	 * in the template.
	 *
	 * @param string $file The file in the extension's upload directory.
	 * @param array $conf The image's configuration.
	 * @return The image to use in the template.
	 */
	function getExtensionImage($file, $conf) {
		$img['file'] = $this->tx_upload_dir . $file;
		$img['file.'] = $conf;
		
		return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}
	
	/**
	 * ???
	 */
	function getLangQueryPart($table) {
		if ($this->sys_language_mode == 'strict' && $GLOBALS['TSFE']->sys_language_content) {
			// TODO multilanguage support when not content_fallback, but how?!?
		} else { // language
			return '((' . $table . '.sys_language_uid IN (0,-1)) OR (' . $table . '.sys_language_uid='.$GLOBALS['TSFE']->sys_language_content.' AND '. $table . '.l18n_parent=0))';
		}
	}
	
	/**
	 * Get the language overlay for the row, if the lanuage uid is different than the default one.
	 * 
	 * @param array $row The array with information about the table's content.
	 * @param string $table The name of the table.
	 * @return The original row or the translated row.
	 */
	function getLanguageOverlay($row, $table) {
		if ($GLOBALS['TSFE']->config['config']['sys_language_uid']) {
			$OLmode = ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '');
			return $GLOBALS['TSFE']->sys_page->getRecordOverlay($table, $row, $GLOBALS['TSFE']->config['config']['sys_language_uid'], $OLmode);
		}
		return $row;
	}
	
	/**
	 * This method creates the E-Mail address.
	 *
	 * @param string The E-Mail address
	 * @param string The text for the E-Mail address.
	 * @return The E-Mail address of the staff member.
	 */	
	function createEMailAdress($param, $text = NULL) {
		if (is_null($text)) 
			$text = $param;
		$typolink_conf['parameter'] = $param;
		$mail = $this->cObj->typolink($text, $typolink_conf);
		
		return $mail;
	}
	
	/**
	 * Check the mime type of the file and return the image object. 
	 *
	 * @param string $file The file to check the mimetype.
	 * @return mime type to image: pdf, document,  image, generic
	 */
	function checkMimeTypeImage($file) {
		$mime = array( 
			'image' => array( 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'tiff' ),
			'pdf' => array( 'application/pdf' ),
			'document' => array( 'application/msword', 'application/zip' ),
			'generic' => array( 'text/plain' ),
		);
		$type = mime_content_type($file); 
		$fullPath = $this->tx_mime_dir . 'generic.png';

		if (in_array($type, $mime['image'])) {
			$fullPath = $this->tx_mime_dir . 'image.png';
		} else if (in_array($type, $mime['pdf'])) {
			$fullPath = $this->tx_mime_dir . 'pdf.png';
		} else if (in_array($type, $mime['document'])) {
			$fullPath = $this->tx_mime_dir . 'document.png';
		} 
		$img['file'] = $fullPath;
		return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}
	
	/**
	 * Get the link for the file icon to display.
	 *
	 * @param array $row The data.
	 * @param array $conf The configuration.
	 * @return The link to display with image.
	 */
	function getFileLink($row, $conf) {
		$fullPath = $this->tx_upload_dir . $row['file'];
		if (is_file($fullPath)) {
			$image = $this->checkMimeTypeImage($fullPath);

			$uf = $conf['icon.']['userFunc'];
			$typolink_conf['parameter'] = $this->getPublicationLinkByUserFunc($uf, $row);
		
			return $this->cObj->typolink($image, $typolink_conf);
		}
		return '';
	}

	/** NOTE: 
	 *   - getStorageId
	 *   - getStorageRecursivly
	 *   - getStorageIds
	 *   - getSysFolders
	 * Influenced by dmmjobcontrol from Kevin Renskers 
	 **/

	/**
	 * Get the root storage id if one is set. If no SysFolder is set in the page the
	 * current page's id will be returned.
	 *
	 * @param array $conf The configuration.
	 * @return The SysFolder or page uid.
	 */
	function getStorageId($conf) {
		// Get the PID of the sysfolder where everything will be stored.
		if (!is_null($this->cObj->data['pages'])) { // First look for 'startingpoint' config in the plugin
			return $this->cObj->data['pages'];
		} elseif ($storagePid = $GLOBALS['TSFE']->getStorageSiterootPids()) { // No startingpoint given, is there a storagepid given?
			return $storagePid['_STORAGE_PID'];
		} else { // Last resort: the current page itself
			return $GLOBALS['TSFE']->id;
		}
	}

	/**
	 * Says if the SysFolder should be looked recrusivley for data.
	 * 
	 * @param array $conf The configuration.
	 * @return If the SysFolder should be looked recursivley.
	 */
	function getStorageRecursivly($conf) {
		// Recursively find all storage pages
		$recursive = 0;
		if (isset($this->cObj->data['recursive']) && $this->cObj->data['recursive']) {
			$recursive = $this->cObj->data['recursive'];
		} elseif (isset($conf['recursive']) && $conf['recursive']) {
			$recursive = $conf['recursive'];
		}
		return $recursive;
	}

	/**
	 * This method can be used to return all storage page's UID that are set.
	 * 
	 * @param array $conf The configuration.
	 * @return The storage page's UIDs.
	 */
	function getStorageIds($conf) {
		$ids = $this->getConfValue($conf, 'recordIds');

		if (is_null($ids)) {
			$startpoint = $this->getStorageId($conf);
			$sysfolders = array($startpoint);
			if ($this->getStorageRecursivly($conf)) {
				$this->getSysFolders($startpoint, &$sysfolders);
			}
			return $sysfolders;
		} else {
			$rIds = split(',', $ids);
			$sysfolders = array();
			$rec = $this->getStorageRecursivly($conf);
			if ($rec) {
				foreach ($rIds as $id ) {
					$this->getSysFolders($id, &$sysfolders);
					$sysfolders[] = $id;
				}
			} else {
				return $rIds;
			}
			return $sysfolders;
		}
	}

	/**
	 * Searchh the database for subpages where data can be stored.
	 * 
	 * @param int $uid The id of the current page.
	 * @param array $sysfolder The array where the id's should be stored.
	 */
	function getSysFolders($uid, $sysfolders) {
		$select['select'][] = 'pages.uid';
		$select['table'][] = 'pages';
		$select['where'][] = 'pid = ' . $uid;
		$select['where'][] = $this->getWhereAdd('pages');

		$res = $this->getSelectDbRes($select);
	 
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->getSysFolders($row['uid'], &$sysfolders);
			$sysfolders[] = $row['uid'];
		}
	}
	/* ###COMMON_FUNCTIONS### end */

	/* ###PUBLICATIONS### begin */
	function getPublicationDbResult($conf, $storageIdsArr) {
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_publication.*';
		$select['table'][] = 'tx_cgswigmore_publication';
		
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_publication');
		$select['where'][] = 'tx_cgswigmore_publication.pid IN ('.implode(',', $storageIdsArr).')';
		$select['sort'] = $this->getConfValue($conf, 'sort');
		
		return $this->getSelectDbRes($select);
		
	}
	
	/**
	 * Display the list of publications.
	 *
	 * @param array $conf The configuration of the publications.
	 * @return The list of publications.
	 */
	function displayPublications($conf) {
		if (!$this->setTemplate($conf)) 
			return $this->getTemplateNotFountError();

		$ids = $this->getStorageIds($conf);
		$res = $this->getPublicationDbResult($conf, $ids);
		$template = $this->getTemplateParts('###TEMPLATE###', array());
		
		$markerArray['###PUBL_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_publication_title'); 
		
		return $this->getPublicationsRows($res, $template['total'], $conf, $markerArray);
	}
	
	/**
	 * This method generates the publication items, fills them in a template and returns 
	 * the result.
	 * 
	 * @param mixed $res The database resource to get publication's information.
	 * @param mixed $template The template to fill.
	 * @param array $conf The publication's configuration.
	 * @param array $marks The total template markers to set (optional).
	 * @return The publication's row template filled with publications.
	 */
	function getPublicationsRows($res, $template, $conf, $marks = array()) {
		/* create links or not */
		$pml = $this->getConfValue($conf, 'link');
		
		$index = 0;
		if ($pml == 0)
			$index = 1;

		$templ['total'] = $template;
		$templ['item'] = $this->cObj->getSubpart($templ['total'], '###PUBL_ITEM_ROW###');
		$subpartNames = array('###PUBL_ITEM_WITH_PUBMED_LINK###', '###PUBL_ITEM_WITHOUT_PUBMED_LINK###');
		$content = '';
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray = $this->getPublicationsItemData($row, $conf);

			$sA['###PUBL_ITEM_FILE_CONTAINER###'] = '';

			if ($this->getConfValue($conf, 'link') && $this->getConfValue($conf, 'icon')) {
				$sA['###PUBL_ITEM_FILE_CONTAINER###'] = $this->getPublicationsFileSubpart($row, $templ['total'], $conf);
			}
			
			$mySub = $this->cObj->getSubpart($templ['item'], $subpartNames[$index]);
			$subArr[$subpartNames[$index]] = $this->cObj->substituteMarkerArrayCached($mySub, $markerArray, $sA);
			$subArr[$subpartNames[($index+1)%2]] = '';
			
			$content .= $this->cObj->substituteMarkerArrayCached($templ['item'], $markerArray, $subArr);
		}
		$subpartArray['###PUBL_ITEM_ROW###'] = $content;
		
		return $this->cObj->substituteMarkerArrayCached($templ['total'], $marks, $subpartArray);
	}

	/**
	 * Fill the subpart for the image link.
	 * 
	 * @param array $row The publication's data.
	 * @param mixed $template_orig The template source.
	 * @param array $conf The configuration.
	 * @return The filled subpart.
	 */
	function getPublicationsFileSubpart($row, $template_orig, $conf) {
		$template['total'] = $template_orig;
		$template['item0'] = $this->cObj->getSubpart($template_orig, '###PUBL_ITEM_FILE_CONTAINER###');

		$markerArray['###PUBL_ITEM_FILE###'] = $this->getFileLink($row, $conf);

		return $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray);
	}

	/**
	 * Call the user function set for the publications.
	 *
	 * @param string $userFunc The name of the user function.
	 * @param array $row The publication's data.
	 */
	function getPublicationLinkByUserFunc($userFunc, $row) {
		return t3lib_div::callUserFunction($userFunc, $row, $this);
	}

	/**
	 * This function sets all markers for the publication.
	 * 
	 * @param array $row The publication's information.
	 * @param array Publication's configuration.
	 * @return An array with the publication's markers.
	 */
	function getPublicationsItemData($row, $conf) {
		$date = $row['date'];
		if ($date != 0) {
			$date = date('j M Y', $date);
		} else {
			$date = '';
		}
	
		$markerArray = array(
			'###PUBL_NAME###'	 => 	$row['title'],
			'###PUBL_AUTHORS###' =>		$row['author'],
			'###PUBL_JOURNAL###' => 	$row['journal'],
			'###PUBL_VOLUME###'	 => 	$row['volume'],
			'###PUBL_NUMBER###'	 => 	$row['number'],
			'###PUBL_DATE###' 	 => 	$date,
			'###PUBL_PAGES###'	 => 	$row['pages'],
			'###PUBL_NOTE###'	 => 	$row['note'],
			'###PUBMED_LINK###'	 => 	''
		);
		

		if ($this->getConfValue($conf, 'link')) {
			$uf = $this->getConfValue($conf, 'userFunc');
			$link = $this->getPublicationLinkByUserFunc($uf, $row);
		
			$markerArray['###PUBMED_LINK###'] = $link;
		}
		
		return $markerArray;
	}
	/* ###PUBLICATIONS### end */

	/* ###STAFF### begin */
	/**
	 * This method displayes a list of staff members. Using the 'sta_state' switch defines if the
	 * current or archived members should be displayed. But if the 'sta_uid' is set, the method 
	 * displayes the details of the staff member with uid = sta_uid
 	 * 
	 * @param array $conf The configuration of the staff element.
	 * @return The content of the staff member list.
	 */
	function displayStaff($conf) {
		if (!$this->setTemplate($conf)) 
			return $this->getTemplateNotFountError();
		
		$sta_uid = $this->getParameter('sta_uid');
		$storageIds = $this->getStorageIds($conf);
		
		if (is_null($sta_uid)) {
			return $this->displayStaffList($conf, $storageIds);
		} else {
			return $this->displayStaffDetail($conf, $storageIds, $sta_uid);
		} 
	}

	/**
	 * This method generates the query to display the list of staff members of the institute.
	 * Using the 'sta_state' switch it decides if the current or archived members should be 
	 * displayed. 
	 * 
	 * @param array $conf The configuration of the staff element.
	 * @param array $ids The storage ids.
	 * @return The content of the staff member list.
	 */
	function displayStaffList($conf, $ids) {
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_staff.*';
		$select['table'][] = 'tx_cgswigmore_staff';
		$select['where'][] = 'tx_cgswigmore_staff.pid IN ('.implode(',', $ids).')';
		$select['sort'] = $this->getConfValue($conf, 'sort');
		
		// find out if the current or the archived members should be displayed.
		$sta_state = $this->getParameter('sta_state');
		if ($sta_state === NULL || $sta_state === TX_CGSWIGMORE_PI1_STAFF_CURRENT) {
			$sta_state = TX_CGSWIGMORE_PI1_STAFF_ARCHIVED;
			$select['where'][] = $this->getWhereAdd('tx_cgswigmore_staff');
		} else {
			$sta_state = TX_CGSWIGMORE_PI1_STAFF_CURRENT;
			$select['where'][] = $this->getWhereAdd('tx_cgswigmore_staff', 1);
		}
			
		$res = $this->getSelectDbRes($select);

		$template = $this->getTemplateParts('###TEMPLATE###', array());
		return $this->fillStaffListTemplate($template['total'], $res, $sta_state, $conf);
	}
	
	/**
	 * This method displays the details about a staff member and its publications. For the publications here a 
	 * method from the PUBLICATIONS section is used (see up).
	 *
	 * @param array $conf The configuration of the staff member.
	 * @param array $ids The storage folder ids.
	 * @param int $sta_uid The uid of the staff member.
	 * @return The template detail template filled with user's information.
	 */
	function displayStaffDetail($conf, $ids, $sta_uid) {
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_staff.*';
		$select['table'][] = 'tx_cgswigmore_staff';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_staff');
		$select['where'][] = 'tx_cgswigmore_staff.uid = ' . intval($sta_uid);
		$select['where'][] = 'tx_cgswigmore_staff.pid IN ('.implode(',', $ids).')';
		$select['sort'] = $this->getConfValue($conf, 'sort');
		$select['limit'] = '1';
		
		$res = $this->getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) != 1) {
			return $this->pi_getLL('tx_cgswigmore_pi1_invalid_numer_of_rows');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		$template = $this->getTemplateParts('###TEMPLATEDETAIL###', array('###STAF_PUBLICATIONS###'));
		$subpartArray = $this->displayStaffDetailSubpart($template, $row);
		$markerArray = $this->getStaffItemData($row, $GLOBALS['TSFE']->id, $conf);
		
		if (is_null($markerArray)) {
			return $this->pi_getLL('tx_cgswigmore_pi1_invalid_staff_member');
		}
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This method fills the suparts of the staff member's detail template.
	 * 
	 * @param mixed $template The template that contains the subparts.
	 * @param array $row The array with the data.
	 * @return The filled subparts. 
	 */
	function displayStaffDetailSubpart($template, $row) {
		$subpartArray['###STAF_PUBLICATIONS###'] = $this->getStaffDetailMemberPublications($row['uid'], $template['item0']); 
		$subpartArray['###STAFF_PHONE_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																	'###STAFF_PHONE_ITEM###', 
																	'###STAF_PHONE###', 
																	'phone');
		$subpartArray['###STAFF_FAX_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																	'###STAFF_FAX_ITEM###', 
																	'###STAF_FAX###', 
																	'fax'); 
		$subpartArray['###STAFF_MOBILE_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																	'###STAFF_MOBILE_ITEM###', 
																	'###STAFF_MOBILE###', 
																	'mobile');
		$subpartArray['###STAFF_MAIL_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																	'###STAFF_MAIL_ITEM###', 
																	'###STAF_MAIL###', 
																	'mail');
		return $subpartArray;
	}
	
	/**
	 * Get the subpart details of the staff for the phone, the fax, mobile phone number and E-Mail address.
	 * The method can be called with the template with the subpart name, the marker to use and the name of 
	 * the column in the database.
	 *
	 * @param mixed $template_orig The total template containig the subpart to fill.
	 * @param array $row The array with the data for the subpart.
	 * @param string $subpartName The name of the subpart to fill.
	 * @param string $markerName The name of the marker to use.
	 * @param string $columnName The name of the column.
	 * @return The filled subpart with the information requested.
	 */
	function getDisplayStaffDetailPMFE($template_orig, $row, $subpartName, $markerName, $columnName) {
		$data = $row[$columnName];
		if (is_null($data) || strlen($data) == 0)
			return '';
		
		$template = $this->cObj->getSubpart($template_orig, $subpartName);
		
		if ($columnName == 'mail') {
			$data = $this->createEMailAdress($row['mail']);
		}
		$markerArray[$markerName] = $data;
		
		return $this->cObj->substituteMarkerArrayCached($template, $markerArray);
	}
	
	
	/**
	 * This method selects all publications where the staff member is part of, fills them in a 
	 * subpart of the template and returns the content. 
	 * Specially this method only selects all publications of the staff member.
	 *
	 * NOTE: This method is an exeption for configuration usage. It doesn't use the section's 
	 * configuration because the sorting of the publications isn't defined there. So the method 
	 * uses the classes configuration to get the configuration part for the publications.
	 * 
	 * @param int $uid The uid of the staff member.
	 * @param mixed $template The template to fill.
	 * @return The list of publications of the staff member.
	 */
	function getStaffDetailMemberPublications($uid, $template) {
		$localConf = $this->conf['publication.'];

		/* get all connections to publications for this member */
		$select_mm['select'][] = 'tx_cgswigmore_publication_staff_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_publication_staff_mm';
		$select_mm['where'][] = 'tx_cgswigmore_publication_staff_mm.uid_foreign = ' . intval($uid);
		
		$res_mm = $this->getSelectDbRes($select_mm);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0)
			return '';
		
		$p_uid = array();
		/* 
			so get all ids of publications the user is part of and in the same step
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$p_uid[] = ' tx_cgswigmore_publication.uid = ' . $row_mm['uid_local'];
		}
		
		/* now get all the publications of the user */
		$select['select'][] = 'tx_cgswigmore_publication.*';
		$select['table'][] = 'tx_cgswigmore_publication';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_publication');
		$select['where'][] = implode(' OR ', $p_uid);
		$select['sort'] = $this->getConfValue($localConf, 'sort');
		
		$res = $this->getSelectDbRes($select, true);

		$marks['###PUBL_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_publication_title'); 
		
		return $this->getPublicationsRows($res, $template, $localConf, $marks);
	}
	
	/**
	 * This method fills the values for template's member list. Using the 'sta_state' flag
	 * the method can decide to link the person's name for more details or not.
	 * 
	 * @param mixed $template_total The template to use for the list.
	 * @param mixed $res The databank ressource to get the staff member's data.
	 * @param int $sta_state The flag to dicide about current or archived members.
	 * @param array $conf The configuration of the staff element.
	 * @return The rows of staff members.
	 */
	function fillStaffListTemplate($template_total, $res, $sta_state, $conf) {
		$subpartNames = array('###STAF_SUBPART_WITH_LINKS###', '###STAF_SUBPART_WITHOUT_LINKS###');
		$template['total'] = $template_total;
		$template['item0'] = $this->cObj->getSubpart($template['total'], $subpartNames[0]);
		$template['item1'] = $this->cObj->getSubpart($template['total'], $subpartNames[1]);
		
		$index = 1;
		if ($sta_state == TX_CGSWIGMORE_PI1_STAFF_ARCHIVED)
			$index = 0;

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) != 0) {
				$content = '';
				$templ['total'] = $template['item'.$index];
				$templ['item'] = $this->cObj->getSubpart($template['item'.$index],'###STAF_ROW###');
				
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$page_uid = $conf['staffUID'];
					$markerArr = $this->getStaffItemData($row, $page_uid, $conf);
					
					if (is_null($markerArr)) {
						continue;
					}
					
					$content .= $this->cObj->substituteMarkerArrayCached($templ['item'], $markerArr);
				}
				
				$subpartArr['###STAF_ROW###'] = $content;
				$subpartArray[$subpartNames[$index]] = $this->cObj->substituteMarkerArrayCached($templ['total'], array(), $subpartArr);
				$subpartArray[$subpartNames[($index + 1)%2]] = '';
		} else {
			$subpartArray[$subpartNames[$index]] = $this->pi_getLL('tx_cgswigmore_pi1_no_staff');
			$subpartArray[$subpartNames[($index + 1)%2]] = '';
		}
		
		$markerArray = $this->getDisplayStaffListMarkers($sta_state);
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This method gets the template markers for the list template of the staff
	 * members of the institute.
	 *
	 * @param int The flag for archived or current users.
	 * @return The array with markers of the list template.
	 */
	function getDisplayStaffListMarkers($state) {
		if ($state === TX_CGSWIGMORE_PI1_STAFF_ARCHIVED) {
			$cLink['sta_state'] = TX_CGSWIGMORE_PI1_STAFF_ARCHIVED;
			$text = $this->pi_getLL('tx_cgswigmore_pi1_section_staff_link_previous');
		} else {
			$cLink['sta_state'] = TX_CGSWIGMORE_PI1_STAFF_CURRENT;
			$text = $this->pi_getLL('tx_cgswigmore_pi1_section_staff_link_current');
		}
		
		$markerArray = array(
			'###STAF_TITLE###'			=> $this->pi_getLL('tx_cgswigmore_pi1_section_staff'),
			'###STAF_PREV_NEXT_LINK###'		=> $this->pi_linkTP_keepPIvars_url($cLink),
			'###STAF_PREV_NEXT_LINK_TEXT###'	=> $text
		);
		return $markerArray;
	}
	
	/**
	 * This function sets all markers for the staff member.
	 * 
	 * @param array $row The staff member's information.
	 * @param int $page_uid The page id to use for the link.
	 * @param array $conf The configuration of the staff element.
	 * @return An array with the staff member's markers.
	 */
	function getStaffItemData($row, $page_uid, $conf) {
		/* staff item image */
		if (is_file($this->tx_upload_dir . $row['image']))
			$image = $this->getExtensionImage($row['image'], $conf['image.']);
		
		/* staff item detail link */
		$lcache['sta_uid'] = $row['uid'];
		$link = $this->pi_linkTP_keepPIvars_url($lcache, 0, 0, $page_uid); 
		
		/* staff item mail */
		$mail = $this->createEMailAdress($row['mail']);

		/* staff item sections */
		$sections = $this->getStaffItemMemberSections($row['uid'], $conf, false);
		$lSections = $this->getStaffItemMemberSections($row['uid'], $conf, true);

		if (is_null($sections)) {
			return NULL;
		}
		
		/* fill the markerArray */
		$markerArray = array(
			'###STAF_TITLE###'		=> $row['title'],
			'###STAF_FIRSTNAME###' 		=> $row['firstname'],
			'###STAF_SURNAME###'		=> $row['name'],
			'###STAF_PERSON_LINK###'	=> $link,
			'###STAF_DESCRIPTION###'	=> $row['description'],
			'###STAF_PHONE###'		=> $row['phone'],
			'###STAF_MOBILE###'		=> $row['mobile'],
			'###STAF_IMAGE###'		=> $image,
			'###STAF_FAX###'		=> $row['fax'],
			'###STAF_MAIL###'		=> $mail,
			'###STAF_SECTIONS###'		=> $sections,
			'###STAF_LINKED_SECTIONS###'	=> $lSections,
		);
		return $markerArray;
	}
	
	/**
	 * This method gets a list of the section's names where the staff member is part of.
	 *
	 * NOTE: This method is an exeption for configuration usage. It doesn't use the staff's 
	 * for the section's sorting because the sorting is defined in the section's part of the
	 * configuration.
	 *
	 * @param int $uid The staff member's uid.
	 * @param 	array	$conf: The staff configuration.
	 * @param bool $link Link to the section.
	 * @return The list of staff member's sections.
	 */
	function getStaffItemMemberSections($uid, $conf, $link = true) {
		/* get all connections to sections for this member */
		$select_mm['select'][] = 'tx_cgswigmore_staff_section_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_staff_section_mm';
		$select_mm['where'][] = 'tx_cgswigmore_staff_section_mm.uid_local = ' . intval($uid);
		
		$res_mm = $this->getSelectDbRes($select_mm);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0) {
			return '';
		}
		
		$s_uid = array();
		/* 
			so get all ids of sections the user is part of and in the same step
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$s_uid[] = ' tx_cgswigmore_section.uid = ' . $row_mm['uid_foreign'];
		}
		
		/* now get all the sections of the user */
		$select['select'][] = 'tx_cgswigmore_section.*';
		$select['table'][] = 'tx_cgswigmore_section';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_section');
		$select['where'][] = implode(' OR ', $s_uid);
		$select['where'][] = $this->getLangQueryPart('tx_cgswigmore_section');
		$select['sort'] = $this->getConfValue($this->conf['section.'], 'sort');
		
		$res = $this->getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
			// if we reach this the database is in an inconsistent state
			return NULL;
		}
		
		$title = array();
		$dnL = split(',', $conf['section.']['doNotLink']);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$row = $this->getLanguageOverlay($row, 'tx_cgswigmore_section');
			$sectionUID = $row['uid'];
			if ($link && !in_array($sectionUID, $dnL)) {
				$p_uid = $this->getConfValue($this->conf['section.'], 'sectionUID');
				$link = $this->pi_linkTP_keepPIvars_url(array('sec_uid' => $row['uid']), 0, 0, $p_uid); 
				$typolink_conf['parameter'] = $link;
				$title[] = $this->cObj->typolink($row['title'], $typolink_conf);
			} else {
				$title[] = $row['title'];
			}
		}
		return implode(', ', $title);
	}
	/* ###STAFF### end */

	/* ###JOBS### begin */
	/** 
	 * This method displays the jobs in a list sorted by the categories.
	 * 
	 * @param array $conf The job's configuration.
	 * @return The job list filled in a template.
	 */
	function displayJobs($conf) {
		if (!$this->setTemplate($conf)) 
			return $this->getTemplateNotFountError();

		$ids = $this->getStorageIds($conf);
		$select['select'][] = 'tx_cgswigmore_jobcategory.*';
		$select['table'][] = 'tx_cgswigmore_jobcategory';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_jobcategory');
		$select['where'][] = $this->getLangQueryPart('tx_cgswigmore_jobcategory');
		$select['where'][] = 'tx_cgswigmore_jobcategory.pid IN ('.implode(',', $ids).')';
		$select['sort'] = $conf['category.']['sort'];
		
		$res = $this->getSelectDbRes($select);
		
		switch ($conf['view']) {
			case 'AIO':
				return $this->displayJobsAIO($res, $conf);
				break;
			case 'CJS':
				return $this->displayJobsCJS($res, $conf);
				break;
			default:
				return $this->pi_getLL('tx_cgswigmore_pi1_no_view_set');
		}
	}

	/**
	 * Display jobs and categories in different views (CJS = Categories and Jobs in Single view).
	 *
	 * @param mixed $res The database ressource for the categories.
	 * @param array $conf The configuration array.
	 */
	function displayJobsCJS($res, $conf) {
		$job_uid = $this->getParameter('job_uid');
		$template = $this->getTemplateParts('###TEMPLATE###', array('###JOBCATEGORY_CJS_LIST###', '###JOBCATEGORY_CJSINGLE###'));
	
		$markerArray['###JOB_GLOBAL_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_jobs_page_title');
		$markerArray['###JOB_GLOBAL_TEXT###'] = $this->pi_getLL('tx_cgswigmore_pi1_jobs_text');	
		$subpartArray['###JOBCATEGORY_ROW_AIO###'] = '';
		
		if (is_null($job_uid)) {
			$subpartArray['###JOBCATEGORY_CJS_LIST###'] = $this->displayJobsCategoryJSView($template['item0'], $res, $conf);
			$subpartArray['###JOBCATEGORY_CJSINGLE###'] = '';
		} else {
			$subpartArray['###JOBCATEGORY_CJS_LIST###'] = '';
			$subpartArray['###JOBCATEGORY_CJSINGLE###'] = $this->displayJobsCJobSView($template['item1'], $job_uid, $conf);
		}
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	function displayJobsCJobSView($tmpl, $uid, $conf) {
		$select['select'][] = 'tx_cgswigmore_jobs.*';
		$select['table'][] = 'tx_cgswigmore_jobs';
		$select['where'][] = 'tx_cgswigmore_jobs.uid = ' . intval($uid);
		$select['sort'] = $conf['job.']['sort'];
		$select['limit'] = 1;
		
		$res = $this->getSelectDbRes($select);

		return $this->getJobcategoriesJobListFillTemplate($tmpl, $res, $conf);
	}
	
	/**
	 * Display the list of Categories with the titles of the Jobs.
	 *
	 * @param mixed $tmpl The template to fill the content.
	 * @param mixed $res The database resource for the categories.
	 * @param array $conf The job's configuration.
	 * @return The list of categories with the job titles as sub items.
	 */
	function displayJobsCategoryJSView($tmpl, $res, $conf) {
		$template['total'] = $tmpl;
		$template['item0'] = $this->cObj->getSubpart($template['total'],'###JOBCATEGORY_CJS_LIST_ROW###');
		$innertpl = $this->cObj->getSubpart($template['item0'],'###JOBCATEGORY_LIST###');
		
		$content = '';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))  {
			$row = $this->getLanguageOverlay($row, 'tx_cgswigmore_jobcategory');
			$markerArray = $this->getJobcategoryItemData($row);

			$iSubpartArray['###JOBCATEGORY_LIST###'] = $this->getJobcategoriesJobList($row['uid'], $innertpl, $conf);
			$content .= $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray, $iSubpartArray);
		}
		$subpartArray['###JOBCATEGORY_CJS_LIST_ROW###'] = $content;
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray);
	}

	/**
	 * Display jobs and categories all in one page (AIO = All In One).
	 *
	 * @param mixed $res The database ressource for the categories.
	 * @param array $conf The configuration array.
	 */
	function displayJobsAIO($res, $conf) {
		$template = $this->getTemplateParts('###TEMPLATE###', array('###JOBCATEGORY_ROW_AIO###'));
		$innertpl = $this->cObj->getSubpart($template['item0'],'###JOBCATEGORY_LIST###');
		$content = '';
		
		$subpartArray['###JOBCATEGORY_ROW_AIO###'] = '';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))  {
			$row = $this->getLanguageOverlay($row, 'tx_cgswigmore_jobcategory');
			$markerArray = $this->getJobcategoryItemData($row);

			$innerSubpart['###JOBCATEGORY_LIST###'] = $this->getJobcategoriesJobList($row['uid'], $innertpl, $conf);
			$subpartArray['###JOBCATEGORY_ROW_AIO###'] .= $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray, $innerSubpart);
		}
		
		$markerArray['###JOB_GLOBAL_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_jobs_page_title');
		$markerArray['###JOB_GLOBAL_TEXT###'] = $this->pi_getLL('tx_cgswigmore_pi1_jobs_text');
	        $subpartArray['###JOBCATEGORY_CJS_LIST###'] = '';
		$subpartArray['###JOBCATEGORY_CJSINGLE###'] = '';
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This method selects all jobs from a certain job category. It's important here
	 * that only the jobs in the current language are selected, not such with or without
	 * translations. So this method doesn't work like the rest of the multi-language settings
	 * in this class.
	 *
	 * @param int $uid The uid of the job category.
	 * @param mixed $template_orig The template to fill.
	 * @param array $conf The configuration of the jobs.
	 */
	function getJobcategoriesJobList($uid, $template_orig, $conf) {
		/* get all connections to staff members for this section */
		$select_mm['select'][] = 'tx_cgswigmore_jobs_categories_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_jobs_categories_mm';
		$select_mm['where'][] = 'tx_cgswigmore_jobs_categories_mm.uid_foreign = ' . intval($uid);
		
		$res_mm = $this->getSelectDbRes($select_mm);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0) {
			return '';
		}
		
		$jc_uid = array();
		/* 
			so get all ids of jobs for this job category and in the same step
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$jc_uid[] = ' ( tx_cgswigmore_jobs.uid = ' . $row_mm['uid_local'] . ' AND ' .
						$this->getWhereAdd('tx_cgswigmore_jobs') . ' AND ' . 
						'tx_cgswigmore_jobs.sys_language_uid = ' . $this->sys_language_id . ' ) ';
		}
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_jobs.*';
		$select['table'][] = 'tx_cgswigmore_jobs';
		$select['where'][] = implode(' OR ', $jc_uid);
		$select['sort'] = $conf['jobs.']['sort'];
		
		$res = $this->getSelectDbRes($select);
		
		return $this->getJobcategoriesJobListFillTemplate($template_orig, $res, $conf);
	}
	
	/**
	 * This method fills the rows with job data.
	 * 
	 * @param mixed $template_orig The template to fill.
	 * @param mixed $res The db result set to get the job-data.
	 * @param array $conf The configuration.
	 * @return The list of jobs filled in the template.
	 */
	function getJobcategoriesJobListFillTemplate($template_orig, $res, $conf) {
		$template['total'] = $template_orig;
		$template['item0'] = $this->cObj->getSubpart($template['total'],'###JOB_ROW###');
		$content = '';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray = $this->getJobItemData($row, $conf);
			
			$innertpl = $this->cObj->getSubpart($template['item0'], '###JOB_CONTACT###');
			$innerSubpart['###JOB_CONTACT###'] = $this->getJobcategoriesJobListGetContactList($row['uid'], $innertpl);
			
			$content .= $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray, $innerSubpart);
		}
		$subpartArray['###JOB_ROW###'] = $content;
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray);
	}
	
	/**
	 * Select all contacts for the job, fill the template and return the list with contacts.
	 * 
	 * @param int $uid The uid of the job.
	 * @param mixed $template_orig The template to fill with contacts.
	 * @return The list of contacts for a job.
	 */
	function getJobcategoriesJobListGetContactList($uid, $template_orig) {
		$localConf = $this->conf['staff.'];
		$select_mm['select'][] = 'tx_cgswigmore_jobs_contact_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_jobs_contact_mm';
		$select_mm['where'][] = 'tx_cgswigmore_jobs_contact_mm.uid_local = ' . intval($uid);
		
		$res_mm = $this->getSelectDbRes($select_mm);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0) {
			return $this->pi_getLL('tx_cgswigmore_pi1_job_no_contact_found');
		}
		$s_uid = array();
		/* 
			so get all ids of contacts for this job and
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$s_uid[] = ' ( tx_cgswigmore_staff.uid = ' . $row_mm['uid_foreign'] . ' AND ' .
						$this->getWhereAdd('tx_cgswigmore_staff') . ' ) ';
		}
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_staff.*';
		$select['table'][] = 'tx_cgswigmore_staff';
		$select['where'][] = implode(' OR ', $s_uid);
		$select['sort'] = $localConf['sort'];
		
		$res = $this->getSelectDbRes($select);
		
		return $this->getJobcategoriesJobListGetContactListFillContact($template_orig, $res);
	}

	/**
	 * Finally fill the template with the contacts.
	 * 
	 * @param mixed $template_orig The template to fill.
	 * @param mixed $res The database resultset to get contacts.
	 * @return The filled list of contacts for a job.
	 */
	function getJobcategoriesJobListGetContactListFillContact($template_orig, $res) {
		$template['total'] = $template_orig;
		$template['item0'] = $this->cObj->getSubpart($template['total'], '###JOB_CONTACT_ROW###');
		$content = '';
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray = $this->getStaffItemData($row, $page_uid, $this->conf['staff.']);
			
			if (is_null($markerArray)) {
				continue;
			}
			
			$content .= $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray);
		}
	
		$subpartArray['###JOB_CONTACT_ROW###'] = $content;
		$markerArray['###JOB_CONTACT_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_job_contact');
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This function sets all markers for the job.
	 * 
	 * @param array $row The job's information.
	 * @param array $conf The job's configuration.
	 * @return An array with the job's markers.
	 */
	function getJobItemData($row, $conf) {
		$cLink['job_uid'] = $row['uid'];
		$link = $this->pi_linkTP_keepPIvars_url($cLink);
		
		$typolink_conf['parameter'] = $link;
		$url = $this->cObj->typolink($row['title'], $typolink_conf);
		
		$markerArray = array(
			'###JOB_TEXT###'	=> $row['text'],
			'###JOB_TITLE###'	=> $row['title'],
			'###JOB_TITLE_SINGLE_LINK###' => $url,
			'###JOB_ITEM_FILE###' => $this->getFileLink($row, $conf),
		);
		return $markerArray;
	}
	
	/**
	 * This function sets all markers for the job category.
	 * 
	 * @param array $row The jobcategorie's information.
	 * @return An array with the job categorie's markers.
	 */
	function getJobcategoryItemData($row) {
		$markerArray = array(
			'###JOBCATEGORY_TITLE###'	=> $row['name']
		);
		return $markerArray;
	}
	/* ###JOBS### end */

	/* ###SECTION### begin */
	/**
	 * This method displayes a list of sections. The 'sec_uid' switch defines if the
	 * list of sections or one section's details should be displayed. 
 	 * 
	 * @param array $conf The configuration of the sections.
	 * @return The content of the sections.
	 */
	function displaySections($conf) {
		if (!$this->setTemplate($conf)) 
			return $this->getTemplateNotFountError();
		
		$sec_uid = $this->getParameter('sec_uid');
		$storageIds = $this->getStorageIds($conf);
		
		if (is_null($sec_uid)) {
			return $this->displaySectionList($conf, $storageIds);
		} else {
			return $this->displaySectionDetail($conf, $sec_uid, $storageIds);
		}
	}
	
	/** 
	 * This method selects all the sections and
	 * fills the result in the template.
	 *
	 * @param array $conf The section's configuration.
	 * @param array $ids The record storage ids.
	 * @return The list of sections filled in the template.
	 */
	function displaySectionList($conf, $ids) {
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_section.*';
		$select['table'][] = 'tx_cgswigmore_section';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_section');
		$select['where'][] = $this->getLangQueryPart('tx_cgswigmore_section');
		$select['where'][] = 'tx_cgswigmore_section.pid IN ('.implode(',', $ids).')';

		$select['sort'] = $this->getConfValue($conf, 'sort');

		$res = $this->getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
			return $this->pi_getLL('tx_cgswigmore_pi1_section_no_row_found');
		}
		
		return $this->displaySectionListFillTemplate($res, $conf);
	}
	
	/**
	 * Display the section's details.
	 * 
	 * @param array $conf The section's configuration.
	 * @param int $sec_uid The section's uid.
	 * @param array $ids The record storage ids
	 * @return The section's details.
	 */
	function displaySectionDetail($conf, $sec_uid, $ids) {
		$sec_state = $this->getParameter('sec_state');
		if (is_null($sec_state)) 
			$sec_state = TX_CGSWIGMORE_PI1_SECT_HOME;
		
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_section.*';
		$select['table'][] = 'tx_cgswigmore_section';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_section');
		$select['where'][] = $this->getLangQueryPart('tx_cgswigmore_section');
		$select['where'][] = 'tx_cgswigmore_section.uid = ' . intval($sec_uid);
		$select['where'][] = 'tx_cgswigmore_section.pid IN ('.implode(',', $ids).')';
		$select['limit'] = '1';

		$res = $this->getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) != 1) {
			return $this->pi_getLL('tx_cgswigmore_pi1_invalid_numer_of_rows');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$row = $this->getLanguageOverlay($row, 'tx_cgswigmore_section');
		
		return $this->fillSectionDetailTemplate($row, $sec_state, $conf);
	}
	
	/**
	 * This method selects the diffrent details of the section and displays them.
	 * 
	 * @param array $row The section's row.
	 * @param int $sec_state The switcher to select the different details of the section.
	 * @param array $conf The configuration of the section.
	 */
	function fillSectionDetailTemplate($row, $sec_state, $conf) {
		$subpartNames = array('###SECT_DETAIL_DESC###', '###SECT_DETAIL_STAFF### ', '###SECT_DETAIL_PUBLICATIONS###');
		$template = $this->getTemplateParts('###TEMPLATEDETAIL###', $subpartNames);
		$menuSubpart = $this->cObj->getSubpart($template['total'],'###SECT_MENUITEM###');
		
		$index = 0;
		if ($sec_state == TX_CGSWIGMORE_PI1_SECT_STAFF) 
			$index = 1;
		else if ($sec_state == TX_CGSWIGMORE_PI1_SECT_PUBLICATIONS)
			$index = 2;
		
		switch ($index) {
			case 1:
				$subpartArray[$subpartNames[$index]] = $this->getSectionDetailTemplateStaffList($row['uid'], $template['item'.$index], $conf); 
				break;
			case 2:
				$subpartArray[$subpartNames[$index]] = $this->getSectionDetailTemplatePublicationList($row['uid'], $template['item'.$index], $conf);
				break;
			case 0:
				$index = 0;
			default:
				$subpartArray[$subpartNames[$index]] = $this->getSectionDetailTemplateDetail($row, $template['item'.$index], $conf);
		}
		
		$subpartArray[$subpartNames[($index+1)%3]] = '';
		$subpartArray[$subpartNames[($index+2)%3]] = '';
		$subpartArray['###SECT_MENUITEM###'] = $this->getSectionDetailMenu($menuSubpart, $sec_state);
		$markerArray = $this->getSectionItemData($row, $conf);
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	// TODO files???
	function getSectionDetailTemplateDetail($row, $template, $conf) {
		$subpartArray['###SECT_DETAIL_FILE###'] = $this->getSectionDetailFileList($row['uid'], $template, $conf);
		$markerArray['###SECT_DETAIL_TEXT###'] = $row['description'];

		return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * This method selects all publications witch are linked to this section fills the
	 * template and returns the list.
	 * To fill the rows of the template a method from the publications section is used!
	 * 
	 * @param int $uid The uid of the section.
	 * @param mixed $template The template to fill.
	 * @param array $conf The configuration of the section.
	 * @return The list of publications of this section.
	 */
	function getSectionDetailTemplatePublicationList($uid, $template, $conf) {
		$localConf = $this->conf['publication.'];
		
		/* get all connections to staff members for this section */
		$select_mm['select'][] = 'tx_cgswigmore_publication_section_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_publication_section_mm';
		$select_mm['where'][] = 'tx_cgswigmore_publication_section_mm.uid_foreign = ' . intval($uid);
		
		$res_mm = $this->getSelectDbRes($select_mm);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0) {
			return $this->pi_getLL('tx_cgswigmore_pi1_no_publications'); // no publications found.
			
		}
		
		$p_uid = array();
		/* 
			so get all ids of publications for this section and in the same step
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$p_uid[] = ' ( tx_cgswigmore_publication.uid = ' . $row_mm['uid_local'] . ' AND ' .
						$this->getWhereAdd('tx_cgswigmore_publication') . ' ) ';
		}
		
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_publication.*';
		$select['table'][] = 'tx_cgswigmore_publication';
		$select['where'][] = implode(' OR ', $p_uid);
		$select['sort'] = $localConf['sort'];
		
		$res = $this->getSelectDbRes($select, true);
		
		return $this->getPublicationsRows($res, $template, $localConf);
	}
	
	/**
	 * This method displayes finally the list of the staff members for the section. It selects
	 * all the staff members for this section and uses a method from the staff section to complete
	 * the template.
	 *
	 * NOTE: This method is an exeption for configuration usage. It doesn't use the section's 
	 * configuration because the sorting of the staff members isn't present there.
	 *
	 * @param int $uid The uid of the section.
	 * @param mixed $template The template to use for the list.
	 * @param array $conf The configuration for the section.
	 * @return The list of section's staff members.
	 */
	function getSectionDetailTemplateStaffList($uid, $template, $conf) {
		$localConf = $this->conf['staff.'];

		/* get all connections to staff members for this section */
		$select_mm['select'][] = 'tx_cgswigmore_staff_section_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_staff_section_mm';
		$select_mm['where'][] = 'tx_cgswigmore_staff_section_mm.uid_foreign = ' . intval($uid); 
		
		$res_mm = $this->getSelectDbRes($select_mm);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0) {
			return $this->pi_getLL('tx_cgswigmore_pi1_no_staff_members');
		}
		
		$s_uid = array();
		/* 
			so get all ids of staff members of this section and in the same step
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$s_uid[] = ' ( tx_cgswigmore_staff.uid = ' . $row_mm['uid_local'] . ' AND ' .
						$this->getWhereAdd('tx_cgswigmore_staff') . ' ) ';
		}
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_staff.*';
		$select['table'][] = 'tx_cgswigmore_staff';
		$select['where'][] = implode(' OR ', $s_uid);
		$select['sort'] = $localConf['sort'];
		
		$res = $this->getSelectDbRes($select);

		$template = $this->cObj->getSubpart($this->templateCode, '###SECT_DETAIL_STAFF###');
		return $this->fillStaffListTemplate($template, $res, TX_CGSWIGMORE_PI1_STAFF_ARCHIVED, $localConf);
	}
	
	/**
	 * Generate the menu for the section's detail view.
	 * 
	 * @param mixed $template The template to use for the menu.
	 * @param int $sec_state The state that says which item is selected.
	 * @return The menu for the detail's section view.
	 */
	function getSectionDetailMenu($template, $sec_state) {
		$content = '';
		
		$cLink = array('sec_state' => TX_CGSWIGMORE_PI1_SECT_HOME);
		$markerArray = array(
			'###SECT_MENU_LINK###' 		=> 	$this->pi_linkTP_keepPIvars_url($cLink), 
			'###SECT_MENU_LINK_CLASS###'	=> 	$sec_state == TX_CGSWIGMORE_PI1_SECT_HOME ? 'tx-cgswigmore-pi1-A-active' : 'tx-cgswigmore-pi1-A-none',
			'###SECT_MENU_TEXT###' 		=> 	$this->pi_getLL('tx_cgswigmore_pi1_section_home')
		);
		$content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
		
		$cLink = array('sec_state' => TX_CGSWIGMORE_PI1_SECT_STAFF);
		$markerArray = array(
			'###SECT_MENU_LINK###' 		=> 	$this->pi_linkTP_keepPIvars_url($cLink), 
			'###SECT_MENU_LINK_CLASS###'	=> 	$sec_state == TX_CGSWIGMORE_PI1_SECT_STAFF ? 'tx-cgswigmore-pi1-A-active' : 'tx-cgswigmore-pi1-A-none',
			'###SECT_MENU_TEXT###'		=> 	$this->pi_getLL('tx_cgswigmore_pi1_section_staff')
		);
		$content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
		
		$cLink = array('sec_state' => TX_CGSWIGMORE_PI1_SECT_PUBLICATIONS);
		$markerArray = array(
			'###SECT_MENU_LINK###'		=> 	$this->pi_linkTP_keepPIvars_url($cLink), 
			'###SECT_MENU_LINK_CLASS###'	=> 	$sec_state == TX_CGSWIGMORE_PI1_SECT_PUBLICATIONS ? 'tx-cgswigmore-pi1-A-active' : 'tx-cgswigmore-pi1-A-none',
			'###SECT_MENU_TEXT###' 		=> 	$this->pi_getLL('tx_cgswigmore_pi1_section_publications')
		);
		$content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
		
		return $content;
	}
	
	/**
	 * Fill the template with the section's data.
	 *
	 * @param mixed $res The db's ressource to get section's data.
	 * @param array $conf The section's configuration.
	 * @return The list of sections filled in the template.
	 */
	function displaySectionListFillTemplate($res, $conf) {
		$template = $this->getTemplateParts('###TEMPLATE###', array('###SEC_ITEM###'));
		$content = '';
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$row = $this->getLanguageOverlay($row, 'tx_cgswigmore_section');
			$markerArray = $this->getSectionItemData($row, $conf);
			$content .= $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray);
		}
		
		$subpartArray['###SEC_ITEM###'] = $content;

		return $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray);
	}

	/**
	 * This method creates a list o files for a section.
	 * 
	 * @param int $uid The uid of the sections we search for files.
	 * @return The list of files to return, or NULL.
	 */
	function getSectionDetailFileList($uid, $template, $conf) {
		/* get all connections to staff members for this section */
		$select_mm['select'][] = 'tx_cgswigmore_section_files_mm.*';
		$select_mm['table'][] = 'tx_cgswigmore_section_files_mm';
		$select_mm['where'][] = 'tx_cgswigmore_section_files_mm.uid_local = ' . intval($uid);

		$res_mm = $this->getSelectDbRes($select_mm);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_mm) == 0) {
			return NULL;
		}
		
		$f_uid = array();
		/* 
			so get all ids of files of this section and in the same step
		 	prepare a part of the query for the next select 
		*/
		while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)) {
			$f_uid[] = ' ( tx_cgswigmore_files.uid = ' . $row_mm['uid_foreign'] . ' AND ' .
						$this->getWhereAdd('tx_cgswigmore_files') . ' ) ';
		}
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_files.*';
		$select['table'][] = 'tx_cgswigmore_files';
		$select['where'][] = implode(' OR ', $f_uid);
		$select['sort'] = $localConf['sort'];
		
		$res = $this->getSelectDbRes($select);
		
		return $this->createSectionDetailFileList($res, $template, $conf);
	}
	
	/**
	 * Fill the template with image tags or links and return it.
	 * 
	 * @param mixed $res The databank ressource.
	 * @param mixed $template_orig The template to fill.
	 * @param array $conf The section's configuyration.
	 * @return The filled template with the files.
	 */
	function createSectionDetailFileList($res, $template_parent, $conf) {
		$template['total'] = $this->cObj->getSubpart($template_parent, '###SECT_DETAIL_FILE###');
		$template['item0'] = $this->cObj->getSubpart($template_parent, '###SEC_DETAIL_FILE_ROW###');
		$content = '';

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray = $this->getFileItemData($row, $conf);
			$content .= $this->cObj->substituteMarkerArrayCached($template['item0'], $markerArray);
		}
		
		$subpartArray['###SEC_DETAIL_FILE_ROW###'] = $content;
		$markerArray['###SECT_FILE_TITLE###'] = $this->pi_getLL('tx_cgswigmore_pi1_section_file_title'); // TODO
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This function sets all markers for the section.
	 * 
	 * @param array $row The section's information.
	 * @param array $conf The configuration of the section.
	 * @return An array with the section's markers.
	 */
	function getSectionItemData($row, $conf) {
		$lcache['sec_uid'] = $row['uid'];
		$link = $this->pi_linkTP_keepPIvars_url($lcache, 0, 0, $conf['sectionUID']);

		$image = '';
		if (is_file($this->tx_upload_dir . $row['photo'])) {
			$image = $this->getExtensionImage($row['photo'], $conf['image.']);
		}
		
		$leader1 = $this->getSectionItemLeaderLink($row['leader1']);
		$leader2 = $this->getSectionItemLeaderLink($row['leader2']);

		$markerArray = array(
			'###SECT_SECTION###'	 	=> 	$row['section'],
			'###SECT_TITLE###'		 	=>	$row['title'],
			'###SECT_PHOTO###'		 	=>	$row['photo'],
			'###SECT_DESCRIPTION###' 	=> 	$row['description'],
			'###SECT_LINK###'	 	 	=> 	$link,
			'###SECT_IMAGE###'			=>	$image,
			'###SECT_LEADER1###'		=> 	$leader1,
			'###SECT_LEADER2###'		=>	$leader2
		);

		return $markerArray;
	}
	
	/**
	 * This method get the staff links to detail pages. The text for the link is the name of 
	 * the staff member. If the member is not set, an emtpy string will be returned.
	 *
	 * NOTE: This method is an exeption for configuration usage. It doesn't use the section's 
	 * configuration because the staff's uid isn't set there.
	 *
	 * @param int $uid The uid of the staff member.
	 * @return The link to the staff member's detail page.
	 *
	 */
	function getSectionItemLeaderLink($uid) {
		if (is_numeric($uid) && $uid > 0) {
			$select['select'][] = 'tx_cgswigmore_staff.*';
			$select['table'][] = 'tx_cgswigmore_staff';
			$select['where'][] = $this->getWhereAdd('tx_cgswigmore_staff');
			$select['where'][] = 'tx_cgswigmore_staff.uid = ' . intval($uid);
			$select['limit'] = '1';
			
			$res = $this->getSelectDbRes($select);
			
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
				return '';
			}
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			
			$page_uid = $this->conf['staff.']['staffUID'];
			$name = $row['name'];

			$lcache['sta_uid'] = $row['uid'];
			return $this->pi_linkTP_keepPIvars($name, $lcache, 0, 0, $page_uid);
		}
		return '';
	}
	
	/**
	 * This method get the markers for the file item.
	 * 
	 * @param array $row The array with the data.
	 * @param array $conf The configuration of the section.
	 * @return The marker array.
	 */
	function getFileItemData($row, $conf) {
		$markerArray = array(
			// TODO finish this => create a img tag if the file is an image, a links if the file is a document, 
			'###SEC_FILE_NAME###' => $row['file'],
			'###SEC_FILE_DESC###' => $row['description']
		);
		return $markerArray;
	}
	/* ###SECTION### end*/
	
	/* ###LOCATION### begin */
	/**
	 * This method displayes the location set in the extension's configuration.
	 * The uid is checked, but if no location is found an error message will
	 * be displayed.
	 *
	 * @param $conf array The location's configuration/
	 * @return The content for the location.
	 */
	function displayLocation($conf) {
		if (!$this->setTemplate($conf)) 
			return $this->getTemplateNotFountError();
		$uid = intval($this->getConfValue($conf, 'uid'));
		$ids = $this->getStorageIds($conf);
			
		// begin creating the query
		$select['select'][] = 'tx_cgswigmore_location.*';
		$select['table'][] = 'tx_cgswigmore_location';
		$select['where'][] = 'tx_cgswigmore_location.uid = ' . $uid;
		$select['where'][] = 'tx_cgswigmore_location.pid IN ('.implode(',', $ids).')';
		
		$res = $this->getSelectDbRes($select);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) != 1) {
			return $this->pi_getLL('tx_cgswigmore_pi1_invalid_numer_of_rows');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$row = $this->getLanguageOverlay($row, 'tx_cgswigmore_location');

		return $this->displayLocationFillTemplate($row, $conf);
	}
	
	/**
	 * This method fills the location's template. To fill the phone, fax and mail item
	 * it uses a method from the staff's section (getDisplayStaffDetailPMFE(...)).
	 *
	 * @param $row array The location's information from the database.
	 * @param $conf array The location's configuration.
	 * @return The content of the location.
	 */
	function displayLocationFillTemplate($row, $conf) {
		$template = $this->getTemplateParts('###TEMPLATE###', array('###PHONE_ITEM###', '###FAX_ITEM###', '###MAIL_ITEM###'));
		$markerArray = $this->getLocationItemData($row, $conf);
		$subpartArray['###PHONE_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																				'###PHONE_ITEM###', 
																				'###LOCATION_PHONE###', 
																				'phone');
		$subpartArray['###FAX_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																				'###FAX_ITEM###', 
																				'###LOCATION_FAX###', 
																				'fax');
		$subpartArray['###MAIL_ITEM###'] = $this->getDisplayStaffDetailPMFE($template['total'], $row, 
																				'###MAIL_ITEM###', 
																				'###LOCATION_MAIL###', 
																				'mail');
		
		return $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * This function sets all markers for the location.
	 * 
	 * @param array $row The location's information.
	 * @param array $conf The location's configuration.
	 * @return An array with the location's markers.
	 */
	function getLocationItemData($row, $conf) {
		if (is_file($this->tx_upload_dir . $row['file']))
			$image = $this->getExtensionImage($row['file'], $conf['image.']);
		
		$select['select'][] = 'static_countries.*';
		$select['table'][] = 'static_countries';
		$select['where'][] = 'static_countries.uid = ' . intval($row['country']);
		

		$markerArray = array(
			'###LOCATION_PAGE###' => $this->pi_getLL('tx_cgswigmore_pi1_location_page_title'),
			'###LOCATION_TITLE###' => $row['title'],
			'###LOCATION_ADDRESS###' => $row['address'],
			'###LOCATION_ZIP###' => $row['zip'],
			'###LOCATION_CITY###' => $row['city'],
			'###LOCATION_MAIL###' => $this->createEMailAdress($row['mail']),
			'###LOCATION_PHONE###' => $row['phone'],
			'###LOCATION_FAX###' => $row['fax'],
			'###LOCATION_DESCRIPTION###' => $row['description'],
			'###LOCATION_IMAGE###' => $image
		);
		
		$res = $this->getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 1) {
			$r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$markerArray['###LOCATION_COUNTRY###'] = $r['cn_official_name_en'];
		}
		
		
		return $markerArray;
	}
	/* ###LOCATION### end */
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/class.tx_cgswigmore_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/class.tx_cgswigmore_pi1.php']);
}

?>
