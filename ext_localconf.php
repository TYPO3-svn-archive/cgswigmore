<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_cgswigmore_pi1 = < plugin.tx_cgswigmore_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cgswigmore_pi1.php','_pi1','list_type',1);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cgswigmore_section=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cgswigmore_staff=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cgswigmore_publication=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cgswigmore_jobcategory=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cgswigmore_jobs=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cgswigmore_files=1
');
?>