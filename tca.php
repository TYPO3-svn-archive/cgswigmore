<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_cgswigmore_location"] = array (
	"ctrl" => $TCA["tx_cgswigmore_location"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,title,address,zip,city,country,mail,phone,fax,description,file"
	),
	"feInterface" => $TCA["tx_cgswigmore_location"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_cgswigmore_location',
				'foreign_table_where' => 'AND tx_cgswigmore_location.pid=###CURRENT_PID### AND tx_cgswigmore_location.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"address" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.address",		
			"config" => Array (
				"type" => "text",
				"wrap" => "OFF",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"zip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.zip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"city" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.city",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"country" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.country",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "static_countries",	
				"foreign_table_where" => "ORDER BY static_countries.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"mail" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.mail",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"phone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.phone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"fax" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.fax",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_location.file",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_cgswigmore",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, title;;;;2-2-2, address;;;;3-3-3, zip, city, country, mail, phone, fax, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], file")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cgswigmore_section"] = array (
	"ctrl" => $TCA["tx_cgswigmore_section"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,section,title,photo,description,leader1,leader2,files"
	),
	"feInterface" => $TCA["tx_cgswigmore_section"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_cgswigmore_section',
				'foreign_table_where' => 'AND tx_cgswigmore_section.pid=###CURRENT_PID### AND tx_cgswigmore_section.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"section" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.section",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"photo" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.photo",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_cgswigmore",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"leader1" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.leader1",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cgswigmore_staff",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"leader2" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.leader2",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cgswigmore_staff",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"files" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_section.files",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cgswigmore_files",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 10,	
				"MM" => "tx_cgswigmore_section_files_mm",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, section, title;;;;2-2-2, photo;;;;3-3-3, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], leader1, leader2, files")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cgswigmore_staff"] = array (
	"ctrl" => $TCA["tx_cgswigmore_staff"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,name,firstname,section,description,phone,mobile,fax,mail,image"
	),
	"feInterface" => $TCA["tx_cgswigmore_staff"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"firstname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.firstname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"section" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.section",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_cgswigmore_section",	
				"foreign_table_where" => "AND tx_cgswigmore_section.pid=###STORAGE_PID### ORDER BY tx_cgswigmore_section.uid",	
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 15,	
				"MM" => "tx_cgswigmore_staff_section_mm",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"phone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.phone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"mobile" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.mobile",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"fax" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.fax",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"mail" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.mail",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_staff.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_cgswigmore",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, name;;;;3-3-3, firstname, section, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], phone, mobile, fax, mail, image")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cgswigmore_publication"] = array (
	"ctrl" => $TCA["tx_cgswigmore_publication"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,author,title,journal,date,volume,number,pages,note,pmid,staff,section,file"
	),
	"feInterface" => $TCA["tx_cgswigmore_publication"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"author" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.author",		
			"config" => Array (
				"type" => "text",
				"wrap" => "OFF",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.title",		
			"config" => Array (
				"type" => "text",
				"wrap" => "OFF",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"journal" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.journal",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"volume" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.volume",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"number" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.number",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"pages" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.pages",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"note" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.note",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"pmid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.pmid",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"staff" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.staff",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cgswigmore_staff",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 20,	
				"MM" => "tx_cgswigmore_publication_staff_mm",
			)
		),
		"section" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.section",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_cgswigmore_section",	
				"foreign_table_where" => "AND tx_cgswigmore_section.pid=###STORAGE_PID### ORDER BY tx_cgswigmore_section.uid",	
				"size" => 8,	
				"minitems" => 0,
				"maxitems" => 20,	
				"MM" => "tx_cgswigmore_publication_section_mm",
			)
		),
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_publication.file",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_cgswigmore",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, author, title;;;;2-2-2, journal;;;;3-3-3, date, volume, number, pages, note, pmid, staff, section, file")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cgswigmore_jobcategory"] = array (
	"ctrl" => $TCA["tx_cgswigmore_jobcategory"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,name"
	),
	"feInterface" => $TCA["tx_cgswigmore_jobcategory"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_cgswigmore_jobcategory',
				'foreign_table_where' => 'AND tx_cgswigmore_jobcategory.pid=###CURRENT_PID### AND tx_cgswigmore_jobcategory.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobcategory.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cgswigmore_jobs"] = array (
	"ctrl" => $TCA["tx_cgswigmore_jobs"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,categories,text,contact,file"
	),
	"feInterface" => $TCA["tx_cgswigmore_jobs"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_cgswigmore_jobs',
				'foreign_table_where' => 'AND tx_cgswigmore_jobs.pid=###CURRENT_PID### AND tx_cgswigmore_jobs.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobs.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"categories" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobs.categories",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_cgswigmore_jobcategory",	
				"foreign_table_where" => "AND tx_cgswigmore_jobcategory.pid=###STORAGE_PID### ORDER BY tx_cgswigmore_jobcategory.uid",	
				"size" => 6,	
				"minitems" => 0,
				"maxitems" => 20,	
				"MM" => "tx_cgswigmore_jobs_categories_mm",
			)
		),
		"text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobs.text",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"contact" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobs.contact",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cgswigmore_staff",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 5,	
				"MM" => "tx_cgswigmore_jobs_contact_mm",
			)
		),
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_jobs.file",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_cgswigmore",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, categories;;;;3-3-3, text;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], contact, file")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cgswigmore_files"] = array (
	"ctrl" => $TCA["tx_cgswigmore_files"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,file,description"
	),
	"feInterface" => $TCA["tx_cgswigmore_files"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_cgswigmore_files',
				'foreign_table_where' => 'AND tx_cgswigmore_files.pid=###CURRENT_PID### AND tx_cgswigmore_files.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_files.file",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_cgswigmore",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cgswigmore/locallang_db.xml:tx_cgswigmore_files.description",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, file, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts]")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>