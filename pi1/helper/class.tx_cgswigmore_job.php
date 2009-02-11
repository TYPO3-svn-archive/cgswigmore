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
class tx_cgswigmore_job extends tx_cgswigmore_helper_base {

	/**
	 * Constructor.
	 * This constructor set's the automatic generated template marker keys for the a job.
	 * Additional markers are defined in tx_cgswigmore_job->getMarker(...)
	 *
	 * @return void
	 * @author Christoph Gostner 
	 * @see pi1/helper/tx_cgswigmore_job#getMarker()
	 */
	public function __construct() {
		parent::__construct();

		$this->tableKeys = array(
			'uid',
			'pid',
			'text',
			'title',
			'file',
		);
	}

	/**
	 * Start filling job categories and their jobs in the template.
	 * The default setting for the job categories/jobs is to view all in a list. 
	 * But if the user set the view option to CJS per typoscript the default behaviour changes
	 * and only a list of the publication's titles are displayed. The publication's title are 
	 * linked containig the selected job category and the selected job. The link leads to the same
	 * page, displaying only the selected job category and the job.
	 *
	 * @param array $select Optional parameter to modify the SQL query
	 * @return string The content of the job database filled in the template 
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillTemplate()
	 */
	public function fillTemplate($select = array()) {
		$select = array();
		$selectJob = intval($this->getGvalue('job::select'));
		$selectJobCategory = intval($this->getGvalue('jobcategory::select'));
		if (strncmp($this->conf['view'], 'CJS', 3) == 0 && $selectJobCategory != 0 && $selectJobCategory > 0) {
			$select['where'][] = 'tx_cgswigmore_jobcategory.uid = ' . $selectJobCategory;
		}
		if (strncmp($this->conf['view'], 'CJS', 3) == 0 && $selectJob == 0) {
			$this->setMasterTemplateMarker('###TEMPLATE_CJS###');
		}
		$res = $this->getJobCategoryDbResult($this->conf['category.']['sort'], $select);
		$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_JOBCATEGORY_ROW###'));
		
		$markerArray = $this->getTemplateMarkers();
		$subpartArray['###TEMPLATE_JOBCATEGORY_ROW###'] = $this->fillTemplateWithResource($res, $template['item0']);
		
		return $this->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}
	
	/**
	 * The method fills a job category in the template.
	 * The method fills the template with the job categorie's title and the jobs
	 * that are set for the job category.
	 * 
	 * @param array $row The array contains the data of the job category to fill in the template
	 * @param mixed $template The template to fill with the job category and it's jobs
	 * @return string The filled template with the job category and it's jobs
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillRow()
	 */
	public function fillRow($row, $template) {
		$markerArray = $this->getJobCategoryMarker($row);
		$subpartArray['###TEMPLATE_JOB_ROW###'] = $this->fillJobTempate($row['uid'], $this->getSubTemplate($template, '###TEMPLATE_JOB_ROW###'));
		
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * Get the avialabe jobs.
	 * The select parameter contains a limit to only return jobs for a 
	 * certain job category.
	 * 
	 * @param $sort How the jobs should be sorted
	 * @param array $select To predefine a part of the SQL query 
	 * @return resource The resourcce holding all avaiable jobs
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getDbResult()
	 */
	public function getDbResult($sort, $select = array()) {
		$select['select'][] = 'tx_cgswigmore_jobs.*';
		$select['table'][] = 'tx_cgswigmore_jobs';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_jobs');
		$select['where'][] = 'tx_cgswigmore_jobs.sys_language_uid = ' . $this->getLanguageId();
		$select['sort'] = $sort;
		
		return self::getSelectDbRes($select);
	}

	/**
	 * Generate the markers for a job category.
	 * This method extend the markers created by the getMarkerFromArr method. 
	 * 
	 * @param array $row The data to create the markers
	 * @param mixed $object An optional parameter here used to create the job link title marker
	 * @return array The marker for a job
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getMarker()
	 */
	public function getMarker($row, $object = NULL) {
		$markerArray = $this->getMarkerFromArr($row);
		
		$parameter = $this->getLinkParameter(
			array(
				'jobcategory::select' => $object,
				'job::select' => $row['uid'],
			));
		$markerArray['###JOB_LINK_TITLE###'] = $this->createLink($parameter, $row['title']);
		$markerArray['###JOB_FILE###'] = $this->getFileIconLink($row);

		return $markerArray;
	}
	
	/**
	 * The method selects the jobs for a job category and fills them in the template.
	 * This method fills selects the jobs for a job category. If the user set's the view 
	 * option to CJS it also selects only the selected job. 
	 * The method first gets all the job UIDs in the category, then it iterates thru the 
	 * result and fills the template.
	 * 
	 * @param int $uid
	 * @param mixed $template 
	 * @return string
	 * @author Christoph Gostner
	 */
	private function fillJobTempate($uid, $template) {
		$select = array();
		$selectJob = intval($this->getGvalue('job::select'));
		if (strncmp($this->conf['view'], 'CJS', 3) == 0 && $selectJob != 0 && $selectJob > 0) {
			$select['where'][] = 'tx_cgswigmore_jobs.uid = ' . $selectJob;
		}
		$jobUidArr = self::getJobCategoryJobUid($uid);
		$select['where'][] = 'tx_cgswigmore_jobs.uid IN ('.implode(',', $jobUidArr).')';
		$res = $this->getDbResult($this->conf['job.']['sort'], $select);
		
		$content = '';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))  {
			$content .= $this->fillJobTemplateRow($row, $template, $uid);
		}
		return $content;
	}
	
	/**
	 * The method fills a job in the template and returns the result.
	 * The method also fills the job's contact subpart.
	 * 
	 * @param array $row The job's data to fill in the template 
	 * @param mixed $template The template to fill
	 * @param int $uid The UID of the job category we need to produce the correct markers
	 * @return string The filled template
	 * @author Christoph Gostner
	 */
	private function fillJobTemplateRow($row, $template, $uid) {
		$markerArray = $this->getMarker($row, $uid);
		
		$subpartArray['###TEMPLATE_JOB_ROW_CONTACT###'] = $this->fillJobContactTemplate($row['uid'], $this->getSubTemplate($template, '###TEMPLATE_JOB_ROW_CONTACT###'));
		
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}
	
	/**
	 * The method fills the job conatcts in the template.
	 * This method uses the tx_cgswigmore_staff class to fill the template.
	 * Thru the tx_cgswigmore_factory class we get a reference to the the
	 * tx_cgswigmore_staff class. For this reason the template used for the 
	 * contact list is defined in the staff's template, and not in the job's 
	 * template. The marker to identify this template part is called
	 * '###TEMPLATE_JOB_ROW_CONTACT_ROW###'.
	 * To get only those staff contact's that are referenced to the selected 
	 * job, the staff's query is modify to only contain this employee.   
	 * 
	 * @param int $uid The UID of the job we want the contacts from
	 * @param mixed $template The template to fill in the contacts
	 * @return string The filled template
	 * @author Christoph Gostner
	 * @see pi1/helper/tx_cgswigmore_staff
	 */
	private function fillJobContactTemplate($uid, $template) {
		$markerArray = $this->getTemplateMarkers();
		
		$staffUidArr = self::getJobContactStaffUid($uid);
		$staff = tx_cgswigmore_factory::getInstance('tx_cgswigmore_staff');
		$staff->setMasterTemplateMarker('###TEMPLATE_JOB_CONTACT###');
		$select['where'][] = 'tx_cgswigmore_staff.uid IN (' . implode(',', $staffUidArr) . ')';
		
		$subpartArray['###TEMPLATE_JOB_ROW_CONTACT_ROW###'] = $staff->init($select);
		
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}

	/**
	 * Get the avaiable job categories.
	 * 
	 * @param string $sort How the job categories should be sorted
	 * @param array $select To predefine a part of the SQL query
	 * @return resource The resource holding all aviable job categories
	 * @author Christoph Gostner
	 */
	private function getJobCategoryDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_jobcategory.*';
		$select['table'][] = 'tx_cgswigmore_jobcategory';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_jobcategory');
		$select['where'][] = $this->getLangQueryPart('tx_cgswigmore_jobcategory');
		$select['where'][] = 'tx_cgswigmore_jobcategory.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;

		return self::getSelectDbRes($select);
	}
	
	/**
	 * Generate the markers for a job category.
	 * 
	 * @param array $row The data to create the markers
	 * @return array The marker for a job category
	 * @author Christoph Gostner
	 */
	private function getJobCategoryMarker($row) {
		return array('###JOBCATEGORY_NAME###' => $row['name']);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_job.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_job.php']);
}

?>
