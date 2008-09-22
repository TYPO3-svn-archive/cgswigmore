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

require_once (t3lib_extMgm::extPath('cgswigmore').'pi1/helper/util/interface.tx_cgswigmore_helper_base_interface.php');

/**
 * Plugin 'Company managment tool' for the 'cgswigmore' extension.
 *
 * @author	Christoph Gostner <christoph.gostner@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_cgswigmore
 */
abstract class tx_cgswigmore_helper_base implements tx_cgswigmore_helper_base_interface {

	/**
	 * Constants for the different scope in the staff's list view
	 */
	const STAFF_SELECT_CURRENT = 0;
	const STAFF_SELECT_ARCHIVED = 1;

	/**
	 * Constants used to differate the view when displaying the
	 * section's details
	 */
	const SECTION_SELECT_HOME = 2;
	const SECTION_SELECT_STAFF = 3;
	const SECTION_SELECT_PUBLICATION = 4;

	/**
	 * The master template marker is used when a template file initially is read.
	 *
	 * @var string
	 */
	protected $masterTemplateMarker;
	/**
	 * The table keys are used to automatically generate template markers.
	 *
	 * @var array
	 */
	protected $tableKeys;

	/**
	 * The typoconf for the selected view.
	 *
	 * @var array
	 */
	protected $conf;
	/**
	 * A reference to the tx_cgswigmore_pi1 instance
	 *
	 * @var tx_cgswigmore_pi1
	 */
	protected $tx_reference; /* TODO */
	/**
	 * The cObj object of the tx_cgswigmore_pi1 class
	 *
	 * @var object
	 */
	private $cObj;

	/**
	 * Constructor.
	 * The constructor sets the default value for the master template marker
	 * and starts the tableKeys array.
	 *
	 * @return void
	 * @author Christoph Gostner
	 */
	public function __construct() {
		$this->masterTemplateMarker = '###TEMPLATE###';
		$this->tableKeys = array();
	}

	/**
	 * This method sets the master template marker that is used when we read in the template file.
	 * The default template marker is set in the constructor, but we can use this method if we
	 * want to change the default behaviour, for example, when we call an object in another scope
	 * than his own where we want to use another template.
	 *
	 * @param string $markerName The master template marker to set.
	 * @return void
	 * @author Christoph Gostner
	 */
	public function setMasterTemplateMarker($markerName) {
		$this->masterTemplateMarker = $markerName;
	}

	/**
	 * Set the global configuration and the configuration
	 * for the selected view.
	 *
	 * @param array $conf
	 * @return void
	 * @author Christoph Gostner
	 */
	public function setConf($conf) {
		$this->conf = $conf;
	}

	/**
	 * This method sets the reference to the tx_cgswigmore_pi1 class and the cObj
	 *
	 * @param tx_cgswigmore_pi1 $reference The reference to tx_cgswigmore_pi1
	 * @return void
	 * @author Christoph Gostner
	 */
	public function setTxReference($reference) {
		$this->tx_reference = &$reference;
		$this->cObj = &$reference->cObj;
	}

	/**
	 * Initialise the template and call the subclass's fillTemplate(...) method which returns
	 * the template filled with the selected methods.
	 *
	 * @param array $select An optional part of the SQL query to modify it
	 * @return string The filled template with the information you set in the typoscript's configuration
	 * @author Christoph Gostner
	 */
	public function init($select = array()) {
		if (is_null($this->setTemplate())) {
			return $this->getLL('tx_cgswigmore_pi1_template_not_found');
		}

		return $this->fillTemplate($select);
	}

	/**
	 * This functions sets the templateCode attribute. If the templateCode is set
	 * correct and it's a file the function returns true, else false.
	 *
	 * @param array $conf The configuration for the extension.
	 * @return boolean true if the templateCode is set correcty.
	 */
	protected function setTemplate() {
		$this->templateCode = $this->tx_reference->cObj->fileResource($this->conf['templateFile']);

		return !is_null($this->templateCode);
	}
	
	/***********************************************************************
	 * Implementation of interface method(s)
	 ***********************************************************************/
	/**
	 * This method iterates thru the rows, the resource holds and calls fillRow.
	 *
	 * @param mixed $res The result set containing all the data to display
	 * @param mixed $template The subtemplate to fill with the data
	 * @return string The subtemplate, filled with all the data in the resource
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillTemplateWithResource()
	 */
	public function fillTemplateWithResource($res, $template) {
		$content = '';
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= $this->fillRow($row, $template);
		}
		
		return $content;
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

	/**
	 * Get the storage page UID's set for per typoscript of in the page's properties.
	 * If the UID's are set in per typoscript, ignore those in the page properties.
	 *
	 * @return array The UIDs of the storage pages
	 * @author Christoph Gostner
	 */
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

	/**
	 * This method returns the root of the sysfolder pages if it isn't set per typoscript.
	 *
	 * @return int Get the UID/PID of the storage page/sysfolder
	 * @author Christoph Gostner
	 */
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

	/**
	 * This method searchs all the children of a page and adds them and itselfe to the sysfolders array.
	 *
	 * @param int $uid The page's UID where we want all it's children
	 * @param array $sysfolders The array to fill with page UIDs
	 * @return int The passed UID
	 * @author Christoph Gostner
	 */
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

	/**
	 * This method returns if the root storage page should be searched recursivly.
	 *
	 * @return boolean If storage pages should be searched recursivly
	 * @author Christoph Gostner
	 */
	private function getStorageIdsRecursivly() {
		$recursive = FALSE;
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
	 * @return array The template and subparts in one array.
	 * @author Christoph Gostner
	 */
	protected function getTemplateParts($total, $items = array()) {
		$template['total'] = $this->cObj->getSubpart($this->templateCode, $total);

		for ($i = 0; $i < count($items); $i++) {
			$template['item' . $i] = $this->cObj->getSubpart($template['total'], $items[$i]);
		}
		return $template;
	}

	/**
	 * Get the subpart template part out of the main template.
	 *
	 * @param mixed $template template
	 * @param string $subpart Key for the subpart
	 * @return mixed Subpart template
	 * @author Christoph Gostner
	 */
	protected function getSubTemplate($template, $subpart) {
		return $this->cObj->getSubpart($template, $subpart);
	}
	
	/**
	 * Get markers for the template
	 * 
	 * @return array The template markers for the template
	 * @author Christoph Gostner
	 */
	protected function getTemplateMarkers() {
		$markerArray = array();
		/* publication */
		$markerArray['###TEMPLATE_PUBLICATION_PAGE_TITLE###'] = $this->getLL('tx_cgswigmore_pi1_publication_title');
		$markerArray['###TEMPLATE_VIEW_LPY_SUBMIT###'] = $this->getLL('tx_cgswigmore_pi1_publication_update');
		$markerArray['###TEMPLATE_VIEW_LPY_ACTION_URL###'] = $this->tx_reference->pi_linkTP_keepPIvars_url();
		/* staff */
		$markerArray['###TEMPLATE_STAFF_PAGE_TITLE###'] = $this->getLL('tx_cgswigmore_pi1_staff_title');
		/* location */
		$markerArray['###TEMPALTE_LOCATION_PAGE_TITLE###'] = $this->getLL('tx_cgswigmore_pi1_location_page_title');
		/* job */
		$markerArray['###TEMPLATE_JOB_PAGE_TITLE###'] = $this->getLL('tx_cgswigmore_pi1_jobs_page_title');
		$markerArray['###TEMPLATE_JOB_PAGE_TEXT###'] = $this->getLL('tx_cgswigmore_pi1_jobs_page_text');
		$markerArray['###TEMPLATE_JOB_PAGE_CONTACT###'] = $this->getLL('tx_cgswigmore_pi1_job_contact');
	
		return $markerArray;
	}

	/**
	 * This method calls the substituteMarkerArrayCached method from the plugin's cObj.
	 *
	 * @param mixed $template The template
	 * @param array $marks The template markers
	 * @param array $subpartArray The subparts of the template
	 * @return string The filled template
	 * @author Christoph Gostner
	 */
	protected function substituteMarkerArrayCached($template, $marks = array(), $subpartArray = array()) {
		return $this->cObj->substituteMarkerArrayCached($template, $marks, $subpartArray);
	}
	
	/**
	 * This method gets the text in the currently used language.
	 * 
	 * @param string $langKey The key to get the correct language text
	 * @return string The text in the corrected language
	 * @author Christoph Gostner
	 */
	protected function getLL($langKey) {
		return $this->tx_reference->pi_getLL($langKey);
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
	 * @author Christoph Gostner
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
			t3lib_div::debug(array($query));
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
	 * @author Christoph Gostner
	 */
	protected function getWhereAdd($table, $hidden = 0) {
		return $table . '.deleted = 0 AND ' . $table . '.hidden = ' . $hidden;
	}

	/***********************************************************************
	 * Common protected methods start here:
	 ***********************************************************************/
	/**
	 * Get the link for the file icon to display.
	 *
	 * @param array $row The data.
	 * @return The link to display with image.
	 * @author Christoph Gostner
	 */
	protected function getFileLink($row) {
		$fullPath = $this->getUploadDir() . $row['file'];
		if (is_file($fullPath)) {
			$image = $this->checkMimeTypeImage($fullPath);

			$userFunc = $this->conf['icon.']['userFunc'];
			$typolink_conf['parameter'] = t3lib_div::callUserFunction($userFunc, $row, &$this->tx_reference);

			return $this->cObj->typolink($image, $typolink_conf);
		}
		return '';
	}

	/**
	 * The method returns a value set in the url.
	 * If the url for the current page contains a parameter for the
	 * tx_cgswigmore_pi1 extension, look if the $key is avaiable in
	 * the array, if so, return the value, else the method returns
	 * NULL. This method checks only values avaiable thru _GET.
	 *
	 * @param string $key The key to identify the value
	 * @return string The value identified by the key
	 * @author Christoph Gostner
	 */
	protected function getGvalue($key) {
		$params = t3lib_div::_GET('tx_cgswigmore_pi1');

		if (isset($params[$key])) {
			return $params[$key];
		}
		return NULL;
	}

	/**
	 * The method returns a value passed to the page by _POST.
	 *
	 * @param string $key The key to identify the value
	 * @return string The value identified by the key
	 * @author Christoph Gostner
	 */
	protected function getPvalue($key) {
		$params = t3lib_div::_POST('tx_cgswigmore_pi1');

		if (isset($params[$key])) {
			return $params[$key];
		}
		return NULL;
	}

	/**
	 * With this method you can generate images located in the extension's upload
	 * directory.
	 *
	 * @param string $file A file that was uploaded to the extension's upload directory
	 * @param array $conf The configuration for the image
	 * @return string The image tag generated by cObj
	 * @author Christoph Gostner
	 */
	protected function createImage($file, $conf) {
		$img['file'] = $this->getUploadDir() . $file;
		$img['file.'] = $conf;

		return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}

	/**
	 * The method generates an e-mail link.
	 * If the second parameter text isn't set, the method automatically uses
	 * the param as text.
	 *
	 * @param string $param The parameter part of the typolink configuration
	 * @param string $text The text that can be used as text for the link
	 * @return string The e-mail address generated by typolink
	 * @author Christoph Gostner
	 */
	protected function createLink($param, $text = NULL) {
		if (is_null($text))
		$text = $param;
		$typolink_conf['parameter'] = $param;
		$mail = $this->cObj->typolink($text, $typolink_conf);

		return $mail;
	}


	/***********************************************************************
	 * Common methods start here:
	 ***********************************************************************/
	/**
	 * This method returns the typoscript configuration key for the class
	 * used to get the classe's configuration.
	 *
	 * @return string The typoscript configuration key for the class
	 * @author Christoph Gostner
	 */
	public function getClassConfKey() {
		return $this->getDbTableName().'.';
	}

	/**
	 * Return a part of the class name to use it for the template markers.
	 *
	 * @return string The name of the view that the class represents
	 * @author Christoph Gostner
	 */
	protected function getDbTableName() {
		$className = get_class($this);
		return substr($className, 14, strlen($className));
	}

	/**
	 * Get the language query part to support multilanguage tables.
	 * 
	 * @param string $table A table with language support
	 * @return string A part of the SQL query
	 * @author Christoph Gostner
	 */
	protected function getLangQueryPart($table) {
		if ($this->getLanguageMode() == 'strict' && $GLOBALS['TSFE']->sys_language_content) {
			// TODO: multilanguage support when not content_fallback, but how?!?
		} else { // language
			return '((' . $table . '.sys_language_uid IN (0,-1)) OR (' . $table . '.sys_language_uid='.$GLOBALS['TSFE']->sys_language_content.' AND '. $table . '.l18n_parent=0))';
		}
	}
	
	/**
	 * Get the language id for the page.
	 * 
	 * @return int The language id
	 * @author Christoph Gostner
	 */
	protected function getLanguageId() {
		return $this->tx_reference->getLanguageId();
	}
	
	/**
	 * The method returns the language mode.
	 * 
	 * @return string The language mode
	 * @author Christoph Gostner
	 */
	protected function getLanguageMode() {
		return $this->tx_reference->getLanguageMode();
	}
	
	/**
	 * Get the upload directory for the extension.
	 * 
	 * @return string The upload directory for the extension
	 * @author Christoph Gostner
	 */
	protected function getUploadDir() {
		return $this->tx_reference->getUploadDir();
	}
	
	/**
	 * Get the mime type directory.
	 * 
	 * @return string The mime directory
	 * @author Christoph Gostner
	 */
	protected function getMimeDir() {
		return $this->tx_reference->getMimeDir();
	}
	
	/**
	 * The method calls a user function and returns the result.
	 * 
	 * @param string $userFunc The user function to call
	 * @param array $row The data to pass to the user function
	 * @return mixed The result of the user function call
	 * @author Christoph Gostner
	 */
	protected function callUserFunc($userFunc, $row) {
		return t3lib_div::callUserFunction($userFunc, $row, &$this->tx_reference);
	}

	/**
	 * Generate predefined template markers using the $tableKeys class attribute.
	 *
	 * @param array $row The data to use to generate the template marker
	 * @return array The generated template markers
	 * @author Christoph Gostner
	 */
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
	
	/**
	 * Create the parameter part for typolink.
	 * 
	 * @param array $array The parameter to use the link
	 * @return string The parameter part for typolink
	 * @author Christoph Gostner
	 */
	protected function getLinkParameter($overrulePIvars, $cache=0, $clearAnyway=0, $altPageId=0) {
		return $this->tx_reference->pi_linkTP_keepPIvars_url($overrulePIvars, $cache, $clearAnyway, $altPageId);
	}

	/**
	 * This method generates a template marker for the current object.
	 * It uses the subpart staff, job, location, publication ... as base
	 * in uppercase and appends the key name with an underscore.
	 *
	 * @param $key The key to generate the template marker
	 * @return string The generated template marker
	 * @author Christoph Gostner
	 */
	private function getKeyName($key) {
		$className = strtoupper($this->getDbTableName());
		return '###'.$className . '_' . strtoupper($key) . '###';
	}

	/**
	 * Check the mime type of the file and return the image object.
	 *
	 * @param string $file The file to check the mimetype.
	 * @return mime type to image: pdf, document,  image, generic
	 * @author Christoph Gostner
	 */
	private function checkMimeTypeImage($file) {
		$mime = array(
			'image' => array( 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'tiff' ),
			'pdf' => array( 'application/pdf' ),
			'document' => array( 'application/msword', 'application/zip' ),
			'generic' => array( 'text/plain' ),
		);
		$type = mime_content_type($file);
		$fullPath = $this->getMimeDir() . 'generic.png';

		if (in_array($type, $mime['image'])) {
			$fullPath = $this->getMimeDir() . 'image.png';
		} else if (in_array($type, $mime['pdf'])) {
			$fullPath = $this->getMimeDir() . 'pdf.png';
		} else if (in_array($type, $mime['document'])) {
			$fullPath = $this->getMimeDir() . 'document.png';
		}
		$img['file'] = $fullPath;
		return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}


	/***********************************************************************
	 * Static methods to get cross references in the database
	 ***********************************************************************/
	/**
	 * This method returns all avaiable publications for an employee.
	 * If there are no publications for the employee's UID the method returns an
	 * array containig 0, which is needed to get a valid SQL query.
	 *
	 * @param int $uid The section's uid for which we cant the employee UIDs
	 * @return array An array with all the employee UIDs
	 * @author Christoph Gostner
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

	/**
	 * This method returns all avaiable staff UIDs for a section.
	 * If there are no employee for the section's UID the method returns an
	 * array containig 0, which is needed to get a valid SQL query.
	 *
	 * @param int $uid The section's uid for which we cant the employee UIDs
	 * @return array An array with all the employee UIDs
	 * @author Christoph Gostner
	 */
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

	/**
	 * This method returns all avaiable publications for a section.
	 * If there are no publications for the section's UID the method returns an
	 * array containig 0, which is needed to get a valid SQL query.
	 *
	 * @param int $uid The section's uid for which we cant the publication UIDs
	 * @return array An array with all the publication UIDs
	 * @author Christoph Gostner
	 */
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

	/**
	 * This method returns all avaiable jobs for a job category.
	 * If there are no job for the category's UID the method returns an
	 * array containig 0, which is needed to get a valid SQL query.
	 *
	 * @param int $uid The job category for which we want all the job UIDs
	 * @return array An array with all job UIDs
	 * @author Christoph Gostner
	 */
	protected static function getJobCategoryJobUid($uid) {
		$select['select'][] = 'tx_cgswigmore_jobs_categories_mm.*';
		$select['table'][] = 'tx_cgswigmore_jobs_categories_mm';
		$select['where'][] = 'tx_cgswigmore_jobs_categories_mm.uid_foreign = ' . $uid;
		$res = self::getSelectDbRes($select);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);

		$jobUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$jobUidArr[] = $row['uid_local'];
		}
		return $jobUidArr;
	}

	/**
	 * This method returns all avaiable contacts for a job.
	 * If there are no employee for the job's UID the method returns an
	 * array containig 0, which is needed to get a valid SQL query.
	 *
	 * @param int $uid The UID of the job
	 * @return array The staff's UIDs which are contact for the job
	 * @author Christoph Gostner
	 */
	protected static function getJobContactStaffUid($uid) {
		$select['select'][] = 'tx_cgswigmore_jobs_contact_mm.*';
		$select['table'][] = 'tx_cgswigmore_jobs_contact_mm';
		$select['where'][] = 'tx_cgswigmore_jobs_contact_mm.uid_local = ' . $uid;
		$res = self::getSelectDbRes($select);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);

		$staffUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$staffUidArr[] = $row['uid_foreign'];
		}
		return $staffUidArr;
	}

	/**
	 * This method returns all avaiable sections for a staff member.
	 *
	 * @param int $uid The UID of the employee
	 * @return array The sections' UIDs which are connected to the staff member
	 * @author Christoph Gostner
	 */
	protected static function getStaffSectionUid($uid) {
		$select['select'][] = 'tx_cgswigmore_staff_section_mm.uid_foreign';
		$select['table'][] = 'tx_cgswigmore_staff_section_mm';
		$select['where'][] = 'tx_cgswigmore_staff_section_mm.uid_local = ' . $uid;
		$res = self::getSelectDbRes($select);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);

		$sectionUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$sectionUidArr[] = $row['uid_foreign'];
		}
		return $sectionUidArr;
	}
	
	/**
	 * This method returns all avaiable files for a section.
	 *
	 * @param int $uid The UID of the section
	 * @return array The file's UIDs which are connected to the section
	 * @author Christoph Gostner
	 */
	protected static function getSectionFileUid($uid) {
		$select['select'][] = 'tx_cgswigmore_section_files_mm.uid_foreign';
		$select['table'][] = 'tx_cgswigmore_section_files_mm';
		$select['where'][] = 'tx_cgswigmore_section_files_mm.uid_local = ' . $uid;
		$res = self::getSelectDbRes($select);
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0)
			return array(0);

		$fileUidArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$fileUidArr[] = $row['uid_foreign'];
		}
		return $fileUidArr;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_helper_base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_helper_base.php']);
}

?>
