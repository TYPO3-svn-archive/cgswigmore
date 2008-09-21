<?php


interface tx_cgswigmore_helper_base_interface {

	/**
	 * Fill the template with the selected data from the database.
	 *
	 * @abstract To implement in the sub classes
	 * @param array A part of the SQL query
	 * @return string The filled template
	 * @author Christoph Gostner
	 */
	public function fillTemplate($select = array());
	
	/**
	 * The method iterates to the selected data.
	 * The method gets the database resouce that holds all the data rows
	 * in the database. It iterates thru the rows and calls 
	 * tx_cgswigmore_helper_base->fillRow(...) to fill the template
	 * with the data's content. 
	 * 
	 * @abstract To implement in the sub classes
	 * @param resource $res The database resource that holds the data
	 * @param mixed $template The template to fill with the data
	 * @return string The result containig all data
	 * @author Christoph Gostner
	 */
	public function fillTemplateWithResource($res, $template);
	
	/**
	 * This method fills a row's data in the subtemplate.
	 *
	 * @abstract To implement in the sub classes
	 * @param array	$row The data to fill in the template
	 * @param mixed	$template The subtemplate to fill
	 * @return string The filled template with the row's data
	 * @author Christoph Gostner
	 */
	public function fillRow($row, $template);

	/**
	 * This method generates new template markers.
	 *
	 * @abstract To implement in the sub classes
	 * @param array $row The data from the database to generate the template markers
	 * @param mixed $object An optional parameter to pass new arguments
	 * @return array The generated template markers
	 * @author Christoph Gostner
	 */
	public function getMarker($row, $object = NULL);

	/**
	 * The method returns a resource containing the selected data.
	 *
	 * @abstract To implement in the sub classes
	 * @param string $sort The sort order for the result
	 * @param array $select Optional parameter to modify the SQL query
	 * @return resource The resource holing the selected data
	 * @author Christoph Gostner
	 */
	public function getDbResult($sort, $select = array());
}

?>