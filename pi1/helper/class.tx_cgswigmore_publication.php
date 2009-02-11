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
class tx_cgswigmore_publication extends tx_cgswigmore_helper_base {

	/**
	 * Constructor.
	 * This constructor set's the automatic generated template marker keys for the a publication.
	 * Additional markers are defined in tx_cgswigmore_publication->getMarker(...)
	 *
	 * @return void
	 * @author Christoph Gostner 
	 * @see pi1/helper/tx_cgswigmore_publication#getMarker()
	 */
	public function __construct() {
		parent::__construct();

		$this->tableKeys = array(
			'uid',
			'pid',
			'author', 
			'title', 
			'journal', 
			'date', 
			'volume',
			'pages', 
			'note', 
			'pmid', 
			'staff', 
			'section', 
			'number', 
			'file',
		);
	}

	/**
	 * Fill the template for the publications.
	 * Based on the 'view' setting in the typoscript, the method displays all the publications or
	 * the publications filtered per year.
	 *
	 * @see pi1/helper/tx_cgswigmore_helper_base#fillTemplate()
	 * @return string The result of the publication view
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillTemplate()
	 */
	public function fillTemplate($select = array()) {
		$view = $this->conf['view'];

		$template = $this->getTemplateParts($this->masterTemplateMarker, array('###TEMPLATE_VIEW_LPY###', '###TEMPLATE_PUBLICATION_ROW###'));
		if (strncmp($view, 'LPY', 3) == 0) { /* LPY */
			$subpartArray['###TEMPLATE_VIEW_LPY###'] = $this->fillTemplateViewLpy($template['item0']);
			$year = intval($this->getPvalue('year'));
				
			if (!is_null($year) && intval($year) > 0 && strlen($year) == 4) {
				$under = mktime(0,0,0,1,1,$year);
				$upper = mktime(0,0,0,12,31,$year);
				$select['where'][] = 'tx_cgswigmore_publication.date >= ' .$under. ' AND tx_cgswigmore_publication.date <= ' . $upper;

				$rowRes = $this->getDbResult($this->conf['sort'], $select);
				$subpartArray['###TEMPLATE_PUBLICATION_ROW###'] = $this->fillTemplateWithResource($rowRes, $template['item1']);
			} else {
				$subpartArray['###TEMPLATE_PUBLICATION_ROW###'] = '';
			}
		} else { /* AIO */
			$rowRes = $this->getDbResult($this->conf['sort'], $select);
			$subpartArray['###TEMPLATE_PUBLICATION_ROW###'] = $this->fillTemplateWithResource($rowRes, $template['item1']);
			$subpartArray['###TEMPLATE_VIEW_LPY###'] = '';
		}

		$markerArray = $this->getTemplateMarkers();
		return $this->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
	}

	/**
	 * This method fills a single publication in the subtemplate.
	 *
	 * @param array	$row The publication's data
	 * @param mixed	$template The subtemplate to fill
	 * @return string The content of the publication filled in the subtemplate
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#fillRow()
	 */
	public function fillRow($row, $template) {
		$markerArray = $this->getMarker($row);

		$index = 0;
		if (!isset($this->conf['link']) || !$this->conf['link'])
			$index = 1;

		$subPartNames = array('###TEMPLATE_PUBLICATION_ROW_WITH_LINKS###', '###TEMPLATE_PUBLICATION_ROW_WITHOUT_LINKS###');
		$subTemplate = $this->getSubTemplate($template, $subPartNames[$index]);

		$iSubpartArray['###TEMPLATE_PUBLICATION_ROW_FILE_ICON###'] = '';
		if (isset($this->conf['icon']) && $this->conf['icon']) {
			$iMarkerArray['###PUBLICATION_FILE_ICON###'] = $this->getFileIconLink($row);
			$iSubTemplate = $this->getSubTemplate($subTemplate, '###TEMPLATE_PUBLICATION_ROW_FILE_ICON###');
			$iSubpartArray['###TEMPLATE_PUBLICATION_ROW_FILE_ICON###'] = $this->substituteMarkerArrayCached($iSubTemplate, $iMarkerArray);
		}

		$subpartArray[$subPartNames[$index]] = $this->substituteMarkerArrayCached($subTemplate, $markerArray, $iSubpartArray);
		$subpartArray[$subPartNames[($index+1)%2]] = '';

		return $this->substituteMarkerArrayCached($template, array(), $subpartArray);
	}

	/**
	 * Select the publications, get them from the database.
	 * The sort is implemented as parameter, because we want to get all
	 * the different years in a ordered way. This is for the select box, where
	 * the user can choose the year, that he want's to display the publications.
	 * The select is an optional parameter used in the LPY (List Publications per
	 * Year) to predefine the time range, the publications should be
	 * displayed/selected.
	 *
	 * @param string $sort How the publications should by sorted
	 * @return mixed The result from the request for publications
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getDbResult()
	 */
	public function getDbResult($sort, $select = array()) {
		$idArr = $this->getStorageIds();
		$select['select'][] = 'tx_cgswigmore_publication.*';
		$select['table'][] = 'tx_cgswigmore_publication';
		$select['where'][] = $this->getWhereAdd('tx_cgswigmore_publication');
		$select['where'][] = 'tx_cgswigmore_publication.pid IN ('.implode(',', $idArr).')';
		$select['sort'] = $sort;

		return self::getSelectDbRes($select);
	}

	/**
	 * This function sets all markers for the publication.
	 *
	 * @param array $row The publication's information.
	 * @return An array with the publication's markers.
	 * @author Christoph Gostner
	 * @see pi1/helper/util/tx_cgswigmore_helper_base_interface#getMarker()
	 */
	public function getMarker($row, $object = NULL) {
		$markerArray = $this->getMarkerFromArr($row);
		$markerArray['###PUBLICATION_LINK###'] = '';

		if ($row['date'] != 0) {
			$markerArray['###PUBLICATION_DATE###'] = date('j M Y', $row['date']);
		} else {
			$markerArray['###PUBLICATION_DATE###'] = '';
		}
		if (isset($this->conf['link']) && $this->conf['link']) {
			$userFunc = $this->conf['link.']['userFunc'];
			$link = $this->callUserFunc($userFunc, $row);

			$markerArray['###PUBLICATION_LINK###'] = $link;
		}
		return $markerArray;
	}

	/**
	 * This method fills the part of the LPY view when the view is set per typoscript.
	 * If the selectbox was already submitted, the selected year will be marked and set
	 * as selected in the select box.
	 * If the select box was submitted with an empty result, the method isn't called, because
	 * this is filtered upstairs.
	 *
	 * @param mixed $template The template to fill with the years to select
	 * @return string The filled templates with the select box containig all the different years for the publications
	 * @author Christoph Gostner
	 */
	private function fillTemplateViewLpy($template) {
		$yearArr = $this->getUniqueYears();
		$sYear = intval($this->getPvalue('year'));
		$content = '';
		$subTemplate = $this->getSubTemplate($template, '###TEMPLATE_VIEW_LPY_YEAR_ROW###');

		foreach ($yearArr as $year) {
			$markerArray['###TEMPLATE_VIEW_LPY_YEAR###'] = $year;
			if ($year == $sYear)
			$markerArray['###TEMPLATE_VIEW_LPY_YEAR_SELECTED###'] = 'selected="selected"';
			else
			$markerArray['###TEMPLATE_VIEW_LPY_YEAR_SELECTED###'] = '';
			$content .= $this->substituteMarkerArrayCached($subTemplate, $markerArray);
		}

		$subpartArray['###TEMPLATE_VIEW_LPY_YEAR_ROW###'] = $content;
		$markerArray = $this->getTemplateMarkers();
		$subpartArray['###TEMPLATE_VIEW_LPY_YEAR_TITLE###'] = $sYear == 0 ? '' : $sYear;
		return $this->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
	}

	/**
	 * This method selects all the publications in the database and returns an
	 * array with the years, when the publications where published.
	 *
	 * @return array An array with the years, when the publications where published
	 * @author Christoph Gostner
	 */
	private function getUniqueYears() {
		$years = array();
		$res = $this->getDbResult('date ASC');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$y = date('Y', $row['date']);
			$years[$y] = $y;
		}
		return array_keys($years);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_publication.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cgswigmore/pi1/helper/class.tx_cgswigmore_publication.php']);
}

?>
