<?php

/**
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.application.module.helper' );

  
$doc = JFactory::getDocument();

JHtml::_('behavior.framework');             //the only reason this is still needed is to allow the Joomla.Jtext functions.
Jhtml::_('script','com_pbbooking/jquery-cookie/jquery.cookie.js',false,true);
JHtml::_('script','com_pbbooking/moment/min/moment.min.js', false, true);
JHtml::_('script','com_pbbooking/jquery-dateFormat/jquery-dateFormat.min.js',false,true);
JHtml::_('script','com_pbbooking/com_pbbooking.individual.freeflow.js',false,true);
Jhtml::_('stylesheet','com_pbbooking/user_view.css',false,true);



?>

<h1><?php echo $doc->title;?></h1>

<div id="pbbooking-notifications"></div>

<?php
    $modules = JModuleHelper::getModules('pbbookingpagetop');
    foreach ($modules as $module) {
        echo JModuleHelper::renderModule($module);
    }
?>

<?php 


include('individual_freeflow_view.php');

?>

<?php
    $modules = JModuleHelper::getModules('pbbookingpagebottom');
    foreach ($modules as $module) {
        echo JModuleHelper::renderModule($module);
    }
?>

<?php
if ($this->config->show_link) {
	echo '<p>Powered by <a href="http://hotchillisoftware.com">PBBooking - Online Booking for Joomla</a>.</p>';
}

?>