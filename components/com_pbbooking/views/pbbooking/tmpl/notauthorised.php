<?php

/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or la<ter; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/

// No direct access
 
defined('_JEXEC') or die('Restricted access'); 

?>
<h2><?php echo JText::_('COM_PBBOOKING_LOGIN_MESSAGE');?></h2>
<?php 
	$login = JModuleHelper::getModule('mod_login');
	$params = json_decode($login->params,true);
	$login->params = json_encode($params);
	echo JModuleHelper::renderModule($login,array('login'=>522));
?>

