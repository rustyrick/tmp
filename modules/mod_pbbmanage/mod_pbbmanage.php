<?php

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

require_once( dirname(__FILE__).'/helper.php' );

modPbbmanageHelper::setupModule();
\Pbbooking\Pbbookinghelper::jqueryUiInternationalise(true);



require(JModuleHelper::getLayoutPath('mod_pbbmanage'));
?>