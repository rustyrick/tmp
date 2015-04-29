<?php
/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );


class com_pbbookingInstallerScript
{
    var $tables;
    var $data;
    var $displayTemplateWarning;


    public function __construct()
    {
        $this->tables = array('block_days','cals','config','customfields','customfields_data','events','lang_override',
                            'logs','surveys','sync','treatments');
        $this->data = array();

    }

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

        //do a clean migration to 3.0.0
        $db = JFactory::getDbo();
        $config = JFactory::getConfig();

        $current = $db->setQuery('select * from #__extensions where element = "com_pbbooking" and type="component"')->loadObject();
        if ($current) {
            $curmanifest = json_decode($current->manifest_cache,true);
            $needsMigration = version_compare('3.0.0',$curmanifest['version']);
        } else
            $needsMigration = 0;
        
        if ($current && $needsMigration == 1) 
        {
            $this->displayTemplateWarning = true;
            //do the existing tables need to be dropped?
            
            
            //back up the user data
            $tablelist = $db->getTableList();
            $tableprefix = str_ireplace('#__', $config->get('dbprefix'), '#__pbbooking_');

            foreach ($this->tables as $table)
            {   
                if (in_array($tableprefix.$table,$tablelist))
                {
                    $this->data[$table] = $db->setQuery('select * from #__pbbooking_'.$table)->loadObjectList();
                    $db->dropTable($tableprefix.$table);
                }
            }
        }


    }

	function install($parent) {

	}


	function update($parent)
	{
        $db = JFactory::getDbo();
        //restore the data
        if (isset($this->data['config']) && count($this->data['config'])>0)
        {
            foreach ($this->data as $table=>$data)
            {
                foreach ($data as $row)
                {
                    //only want to restore the columns that are in the new data structure
                    $columns = $db->getTableColumns('#__pbbooking_'.$table);

                    $newrow = array();
                    foreach ($columns as $column=>$value)
                        $newrow[$column] = (isset($row->$column)) ? $row->$column : null;

                    //**** CODE FOR EDGE CASES ****/

                    //edge case if (table is events and the verified is NOT set then we're comign from the free version)
                    //and need to set the event verified to 1.
                    if ($table == 'events' && !isset($newrow['verified']))
                        $newrow['verified'] =1;

                    if ($table == 'config')
                    {
                        $newrow['time_groupings'] = '{"morning":{"shift_start":"1000","shift_end":"1200","display_label":"morning"},"afternoon":{"shift_start":"1330","shift_end":"1700","display_label":"afternoon"},"evening":{"shift_start":"1700","shift_end":"1930","display_label":"evening"}}';
                        $newrow['booking_details_template'] = '<p><table><tr><th>{{COM_PBBOOKING_SUCCESS_DATE}}</th><td>{{dstart}}</td></tr><tr><th>{{COM_PBBOOKING_SUCCESS_TIME}}</th><td>{{dtstart}}</td></tr><tr><th>{{COM_PBBOOKING_BOOKINGTYPE}}</th><td>{{service.name}}</td></tr></table></p>';
                    }

                    //****** END CODE FOR EDGE CASES *******/

                    $db->insertObject('#__pbbooking_'.$table,new JObject($newrow),(isset($row->id)) ? 'id' : null);
                }
            }
        }
	}

	function postflight($type,$parent)
	{
		$db = JFactory::getDbo();



		//update the standard ACL rules....
		$db->setQuery('update #__assets set rules = "{\"pbbooking.create\":{\"1\":1},\"pbbooking.deleteown\":{\"1\":1},\"pbbooking.browse\":{\"1\":1}}" where name="com_pbbooking"')->query();

        // Check the extension params and if not already set then set to a default.
        $extension = $db->setQuery('select * from #__extensions where name = "com_pbbooking"')->loadObject();
        $params = json_decode($extension->params,true);
        if (!isset($params['clientid']) && !isset($params['clientsecret']))
        {
            $params['clientid'] = "764986187655-g9r032usscqqmoks1p20hki8vrte71j1.apps.googleusercontent.com";
            $params['clientsecret'] = "P92QlaK0XQYBMYQccBivlkFk";

            // Now push them back in
            $extension->params = json_encode($params);
            $db->updateObject('#__extensions',$extension,'extension_id');
        }


		//install the library
		$installer = new JInstaller();
        //get dir.... some installs don't support __DIR__ constant...
        if (!defined('DS'))
        	define('DS',DIRECTORY_SEPARATOR);

        $dir_arr = explode(DS,__FILE__);
        $dir_arr = array_slice($dir_arr, 0,(count($dir_arr)-1));
        
        //now install.
        $installer->install(implode(DS,$dir_arr).DS.'library');
        $installer->install(implode(DS,$dir_arr).DS.'pbbookinglib');
        $installer->install(implode(DS,$dir_arr).DS.'frontendmanage');

        if (isset($this->displayTemplateWarning) && $this->displayTemplateWarning == true) {
            echo '<div class="alert alert-info">';
            echo JText::_('COM_PBBOOKING_TEMPLATE_COMPAT_WARNING');
            echo '</div>';
        }

        $config = $db->setQuery('select * from #__pbbooking_config')->loadObject();
        if ($config && !isset($config->google_cal_sync_secret))
        {
            $config->google_cal_sync_secret = substr(md5(rand()), 0, 12);
            $db->updateObject('#__pbbooking_config', $config, 'id');
        }

	}
}


?>