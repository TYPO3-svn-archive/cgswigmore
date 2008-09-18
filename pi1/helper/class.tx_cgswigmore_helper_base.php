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
abstract class tx_cgswigmore_helper_base {

	const STAFF_SELECT_CURRENT = 0;
	const STAFF_SELECT_ARCHIVED = 1;
	
	const SECTION_SELECT_HOME = 2;
	const SECTION_SELECT_STAFF = 3;
	const SECTION_SELECT_PUBLICATION = 4;

	protected $masterTemplateMarker;
	
	protected $tableKeys;


	protected $txConf;
	protected $conf;

	protected $tx_reference;

	public function __construct() {
		$this->masterTemplateMarker = '###TEMPLATE###';
		$this->tableKeys = array();
	}
	
	public function setMasterTemplateMarker($markerName) {
		$this->masterTemplateMarker = $markerName;
	}

	public function setConf($txConf) {
		$this->txConf = $txConf;
		$this->conf = $txConf[$this->getClassConfKey()];
	}



	protected $cObj; // TODO: will be removed when migration finished
	public function setTxReference($reference) {
		$this->tx_reference = &$reference;
		$this->cObj = &$reference->cObj;
	}

	public function init($select = array()) {
		if (is_null($this->setTemplate())) {
			t3lib_div::debug("template error");
		}

		return $this->fillTemplate($select);
	}

	protected abstract function fillTemplate($select = array());
	protected abstract function getMarker($row);

	/**
	 * This functions sets the templateCode attribute. If the templateCode is set
	 * correct and it's a file the function returns true, else false.
	 *
	 * TODO: how to make error handling on fail?
	 * @param array $conf The configuration for the extension.
	 * @return boolean true if the templateCode is set correcty.
	 */
	protected function setTemplate() {
		$this->templateCode = $this->tx_reference->cObj->fileResource($this->conf['templateFile']);

		return !is_null($this->templateCode);
	}





	/***********************************************************************
	 * This methods are used to get the different storage ids, where the
	 * extension's data can be found for selection/display.
	 *
	 * NOTE:
	 *   - getStorageId
	 *   - getStorageRecursivly
	 *   - getStorageIds
	 *   - getSysFolders
	 * Influenced by dmmjobcontrol from Kevin Renskers
	 ***********************************************************************/

	protected function getStorageIds() {
		if (isset($this->conf['recordIds']))
			$idArr = split(',', $this->conf['recordIds']);
		else
			$idArr = array($this->getSysFolderId());

		if ($this->getStorageIdsRecursivly()) {
			$sysFolders = array();
				
			foreach ($idArr as $id) {
				$sysFolders[] = $this->setSysFolders($id, &$sysFolder);
			}
			return $sysFolders;
		}
		return $idArr;
	}

	private function getSysFolderId() {
		// Get the PID of the sysfolder where everything will be stored.
		if (!is_null($this->cObj->data['pages'])) { // First look for 'startingpoint' config in the plugin
			return $this->cObj->data['pages'];
		} elseif ($storagePid = $GLOBALS['TSFE']->getStorageSiterootPids()) { // No startingpoint given, is there a storagepid given?
			return $storagePid['_STORAGE_PID'];
		} else { // Last resort: the current page itself
			return $GLOBALS['TSFE']->id;
		}
	}

	private function setSysFolders($uid, $sysfolders) {
		$select['select'][] = 'pages.uid';
		$select['table'][] = 'pages';
		$select['where'][] = 'pid = ' . $uid;
		$select['where'][] = $this->getWhereAdd('pages');

		$res = self::getSelectDbRes($select);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$sysfolders[] = $this->setSysFolders($row['uid'], &$sysfolders);
		}
		return $uid;
	}

	private function getStorageIdsRecursivly() {
		$recursive = 0;
		if (isset($this->cObj->data['recursive']) && $this->cObj->data['recursive']) {
			$recursive = $this->cObj->data['recursive'];
		} elseif (isset($this->conf['recursive']) && $this->conf['recursive']) {
			$recursive = $this->conf['recursive'];
		}
		return $recursive;
	}

	/***********************************************************************
	 * Here now some methods usefull to work with templates.
	 ***********************************************************************/
	/**
	 * This function gets template suparts out of the main template.
	 * The parameter $total declears the template part to select, the items
	 * declear subparts of the main template code.
	 *
	 * @param string $total The main template part.
	 * @param array $items The subparts to select.
	 * @return The template and subparts in one array.
	 */
	function getTemplateParts($total, $items = array()) {
		$template['total'] = $this->cObj->getSubpart($this->templateCode, $total);

		for ($i = 0; $i < count($items); $i++) {
			$template['item' . $i] = $this->cObj->getSubpart($template['total'], $items[$i]);
		}
		return $template;
	}

	/***********************************************************************
	 * Here now starts the implementation of a common request method to use
	 * to get a result from the database.
	 ***********************************************************************/
	/**
	 * Execute a select query on the database and return the
	 * result set.
	 *
	 * @param array $values The select fields, the table ... for the query.
	 * @param boolean $debug Print the query to execute (default = false).
	 * @return The resulting set of values.
	 */
	protected static function getSelectDbRes($values, $debug = false) {
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
	 * Get a part of the where query. User can decide if he want to see hidden
	 * records or not.
	 *
	 * @param string $table The name of the table.
	 * @param int $hidden To decide if hidden recors should be displayed.
	 * @return The generated where query part.
	 */
	protected function getWhereAdd($table, $hidden = 0) {
		return $table . '.deleted = 0 AND ' . $table . '.hidden = ' . $hidden;
	}
	
	/*public function addToSelect($key, $queryPart) {
		$this->select[$key][] = $queryPart;
	}*/

	/***********************************************************************
	 * Common protected methods start here:
	 ***********************************************************************/
	/**
	 * Get the link for the file icon to display.
	 *
	 * @param array $row The data.
	 * @return The link to display with image.
	 */
	protected function getFileLink($row) {
		$fullPath = $this->tx_reference->tx_upload_dir . $row['file'];
		if (is_file($fullPath)) {
			$image = $this->checkMimeTypeImage($fullPath);

			$userFunc = $this->conf['icon.']['userFunc'];
			$typolink_conf['parameter'] = t3lib_div::callUserFunction($userFunc, $row, &$this->tx_reference);

			return $this->cObj->typolink($image, $typolink_conf);
		}
		return '';
	}

	protected function getGvalue($key) {
		$params = t3lib_div::_GET('tx_cgswigmore_pi1');

		if (isset($params[$key])) {
			return $params[$key];
		}
		return NULL;
	}
	
	protected function getPvalue($key) {
		$params = t3lib_div::_POST('tx_cgswigmore_pi1');

		if (isset($params[$key])) {
			return $params[$key];
		}
		return NULL;
	}

	protected function createImage($file, $conf) {
		$img['file'] = $this->tx_reference->tx_upload_dir . $file;
		$img['file.'] = $conf;

		return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}


	/***********************************************************************
	 * Common methods start here:
	 ***********************************************************************/
	private function getClassConfKey() {
		return $this->getDbTableName().'.';
	}

	protected function getDbTableName() {
		$className = get_class($this);
		return substr($className, 14, strlen($className));
	}

	protected function getMarkerFromArr($row) {
		$markerArray = array();

		foreach ($this->tableKeys as $key) {
			$keyName = $this->getKeyName($key);
				
			if (isset($row[$key])) {
				$markerArray[$keyName] = $row[$key];
			} else {
				$markerArray[$keyName] = '';
			}
		}
		return $markerArray;
	}

	private function getKeyName($key) {
		$className = strtoupper($this->getDbTableName());
		return '###'.$className . '_' . strtoupper($key) . '###';
	}



	/**
	 * Check the mime type of the file and return the image object.
	 *
	 * @param string $file The file to check the mimetype.
	 * @return mime type to image: pdf, document,  image, generic
	 */
	private function checkMimeTypeImage($file) {
		$mime = array(
			'image' => array( 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'tiff' ),
			'pdf' => array( 'application/pdf' ),
			'document' => array( 'application/msword', 'application/zip' ),
			'generic' => array( 'text/plain' ),
		);
		$type = mime_content_type($file);
		$fullPath = $this->tx_reference->tx_mime_dir . 'generic.png';

		if (in_array($type, $mime['image'])) {
			$fullPath = $this->tx_reference->tx_mime_dir . 'image.png';
		} else if (in_array($type, $mime['pdf'])) {
			$fullPath = $this->tx_reference->tx_mime_dir . 'pdf.png';
		} else if (in_array($type, $mime['document'])) {
			$fullPath = $this->tx_reference->tx_mime_dir . 'document.png';
		}
		$img['file'] = $fullPath;
		return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}
	
	
	
	/**
	 * 
	 *
	 */
	protected static function getUserPublicationUids($uid) {
		$select['select'][] = 'tx_cgswigmore_publication_staff_mm.uid_local';
		$select['table'][] = 'tx_cgswigmore_publication_staff_mm';
		$select['where'][] = 'tx_cgswigmore_publication_staff_mm.uid_foreign = ' . $uid;
		$res = self::getSelectDbRes($select);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);
		
		$publicationUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$publicationUidArr[] = $row['uid_local'];
		}
		return $publicationUidArr;
	}
	
	protected static function getSectionStaffUids($uid) {
		$select['select'][] = 'tx_cgswigmore_staff_section_mm.*';
		$select['table'][] = 'tx_cgswigmore_staff_section_mm';
		$select['where'][] = 'tx_cgswigmore_staff_section_mm.uid_foreign = ' . $uid;  
		$res = self::getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);

		$staffUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$staffUidArr[] = $row['uid_local'];
		}
		return $staffUidArr;
	}
	
	protected static function getSectionPublicationUid($uid) {
		$select['select'][] = 'tx_cgswigmore_publication_section_mm.*';
		$select['table'][] = 'tx_cgswigmore_publication_section_mm';
		$select['where'][] = 'tx_cgswigmore_publication_section_mm.uid_foreign = ' . $uid;
		$res = self::getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);
		
		$publicationUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$publicationUidArr[] = $row['uid_local'];
		}
		return $publicationUidArr;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_helper_base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_helper_base.php']);
}

?>
