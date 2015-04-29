<?php
/**
 * @package    PurpleBeanie.PBBooking
 * @subpackage Components
 * @link http://www.purplebeanie.com
 * @license    GNU/GPL
*/
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$config = JFactory::getConfig();
$version = new JVersion();
$input = JFactory::getApplication()->input;


//set some defines cause we're goign to use things a lot!!
define('PBBOOKING_TIMEZONE',$config->get('offset')); 
if (!defined('DS'))
    define('DS',DIRECTORY_SEPARATOR);
define('JOOMLA_VERSION','3.0');

//check if there's anything additional that needs to be loaded due to Joomla CMS core changes
jimport('cms.html.html');


//pull in my own framework files....
require_once(JPATH_LIBRARIES.DS.'pbbookinglib'.DS.'vendor'.DS.'autoload.php');
require_once(JPATH_LIBRARIES.DS.'purplebeanie'.DS.'autoload.php');

//some requires 
require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'views'.DS.'pbbooking'.DS.'view.html.php' );
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'pbbookingpaypalhelper.php');

\Pbbooking\Pbbookinghelper::bootstrapPbbooking();

 
// Require specific controller if requested
if($controller = $input->get('view')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
    	Purplebeanie\Util\Pbdebug::log_msg('pbbooking.php - importing controller '.$controller,'com_pbbooking');
        require_once $path;
    } else {
        $controller = '';
    }
}
 
// Create the controller
$c_name = ($input->get('view') == 'pbbooking') ? 'PbbookingController' : 'PbbookingController'.$input->get('view');

//$controller   = new $c_name;
$controller = new $c_name;


$task = $input->get('task');

if(!$task || $task == 'view')
	$task == 'display';

$controller->execute($task);

 
// Redirect if set by the controller
$controller->redirect();