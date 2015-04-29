<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.application.component.controller');
jimport('cms.html.html');
JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');


//set some defines cause we're goign to use things a lot!!
define('PBBOOKING_TIMEZONE',JFactory::getConfig()->get('offset')); 
if (!defined('DS'))
    define('DS',DIRECTORY_SEPARATOR);
define('JOOMLA_VERSION','3.0');
define('COMPONENT_NAME','com_pbbooking');




//check if there's anything additional that needs to be loaded due to Joomla CMS core changes
jimport('cms.html.html');

//pull in my own framework files....
require_once(JPATH_LIBRARIES.DS.'pbbookinglib'.DS.'vendor'.DS.'autoload.php');
require_once(JPATH_LIBRARIES.DS.'purplebeanie'.DS.'autoload.php');

//set a default action
$input = JFactory::getApplication()->input;
$task = $input->get('task');
$view = $input->get('view');
$format = $input->get('format');

if (in_array($view,array('reports')))
    \Pbbooking\Pbbookinghelper::bootstrapPbbooking();
else
    \Pbbooking\Pbbookinghelper::bootstrapPbbooking(false);


//check the config for any major errors that need to be dealt with
\Pbbooking\Pbbookinghelper::check_for_errors();
 


//define a default.
if ($task=='' && $view=='')
    $input->set('task','cpanel.display');


if ($format !== 'raw')
    \Pbbooking\Pbbookinghelper::jqueryUiInternationalise(false);
//end set the default action route.

 
// Perform the Request task
$controller = JControllerLegacy::getInstance('Pbbooking');
$controller->execute($input->getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();