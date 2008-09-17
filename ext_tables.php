<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:cgswigmore/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Company managment tool");


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_location');

$TCA["tx_cgswigmore_location"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY title",	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_location.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, title, address, zip, city, country, mail, phone, fax, description, file",
	)
);


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_section');

$TCA["tx_cgswigmore_section"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section',		
		'label'     => 'section',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_section.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, section, title, photo, description, leader1, leader2, files",
	)
);


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_staff');

$TCA["tx_cgswigmore_staff"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'default_sortby' => "ORDER BY name",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_staff.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, name, firstname, section, description, phone, mobile, fax, mail, image",
	)
);


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_publication');

$TCA["tx_cgswigmore_publication"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'default_sortby' => "ORDER BY crdate DESC",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_publication.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, author, title, journal, date, volume, number, pages, note, pmid, staff, section, file",
	)
);


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_jobcategory');

$TCA["tx_cgswigmore_jobcategory"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobcategory',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_jobcategory.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, name",
	)
);


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_jobs');

$TCA["tx_cgswigmore_jobs"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobs',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_jobs.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, title, categories, text, contact, file",
	)
);


t3lib_extMgm::addToInsertRecords('tx_cgswigmore_files');

$TCA["tx_cgswigmore_files"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_files',		
		'label'     => 'description',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cgswigmore_files.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, file, description",
	)
);
?>