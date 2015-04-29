<?php
/**
* @package      PurpleBeanie.PBBooking
* @license      GNU General Public License version 2 or later; see LICENSE.txt
* @link     http://www.purplebeanie.com
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );


class mod_pbbmanageInstallerScript
{

    function preflight($type,$parent) {
        $jversion = new JVersion();

        if (version_compare($jversion->getShortVersion(),'3.1.5') == -1) {
            Jerror::raiseWarning(null,'This version of PBBooking is not compatible with the version of Joomla you are using.');
            return false;
        }

        if (PHP_VERSION_ID < 50207) {
            JError::raiseWarning(null,'This version of PBBooking is not compatible with the version of PHP you are using.  PHP >= 5.3 is required.');
           return false;
        }

        //check if the libraries are installed
        $db = JFactory::getDbo();
        $config = JFactory::getConfig();

        $pbbookinglib = $db->setQuery('select * from #__extensions where element = "pbbookinglib" and type="library"')->loadObject();
        $pbfunctions = $db->setQuery('select * from #__extensions where element = "pbfunctions" and type="library"')->loadObject();

        if (!$pbbookinglib || !$pbfunctions) {
            JError::raiseWarning(null,'You do not have the needed libraries installed');
            return false;
        } 

        $pbbookinglib = json_decode($pbbookinglib->manifest_cache,true);
        if (version_compare($pbbookinglib['version'],'3.0.0' ) == -1) {
            JError::raiseWarning(null,'You need at least version 3.0.0 of the pbbooking library to use this module');
            return false;
        }

    }

    function install($parent) {

    }


    function update($parent)
    {

    }

    function postflight($type,$parent)
    {


    }
}


?>