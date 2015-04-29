<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class PbbookingModelMultilangs extends JModelList
{

    protected function getListQuery()
    {
            // Create a new query object.           
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            // Select some fields from the hello table
            $query
                ->select('*')
                ->from('#__pbbooking_lang_override');
                 
            return $query;
    }


    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   12.2
     */
    public function getItems()
    {
        $db = JFactory::getDbo();
        $items = $db->setQuery('select * from #__pbbooking_lang_override')->loadObjectList();
        $model = JModelLegacy::getInstance('Multilang','PbbookingModel');

        // Get a storage key.
        $store = $this->getStoreId();


        foreach ($items as $item)
        {
            switch ($item->type)
            {
                case 'customfield':
                    $item->primaryitem = $db->setQuery('select fieldname as originalvalue from #__pbbooking_customfields where id = '.(int)$item->original_id)->loadObject();
                    break;
                case 'calendar':
                    $item->primaryitem = $db->setQuery('select name as originalvalue from #__pbbooking_cals where id = '.(int)$item->original_id)->loadObject();
                    break;
                case 'service':
                    $item->primaryitem = $db->setQuery('select name as originalvalue from #__pbbooking_treatments where id = '.(int)$item->original_id)->loadObject();
                    break;
                case 'message':
                    $messagekey = $model->messageMap[$item->original_id];
                    $item->primaryitem = new JObject(array('originalvalue'=>$GLOBALS['com_pbbooking_data']['config']->$messagekey));
                    break;
                case 'subject':
                    $subjectkey = $model->subjectMap[$item->original_id];
                    $item->primaryitem = new JObject(array('originalvalue'=>$GLOBALS['com_pbbooking_data']['config']->$subjectkey));
                    break;

            }
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }
}