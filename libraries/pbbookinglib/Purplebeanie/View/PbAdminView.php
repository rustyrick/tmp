<?php

namespace Purplebeanie\View;


class PbAdminView extends \JViewLegacy {

    function display($tpl = null)
    {
    	$jversion = new \JVersion();

    	//(version_compare($jversion->getShortVersion(),'3.1.5') == -1)
    	if (version_compare($jversion->getShortVersion(),'3.1.5') == -1) {
    		//load mootools normally.
    		Jhtml::_('behavior.framework');
			Jhtml::_('behavior.framework','more');


    	} else {
    		//version >= 3.1.5 load the jscript frameworks in the normal fashion.
			\JHtml::_('jquery.framework');
			\Jhtml::_('behavior.framework');
			\Jhtml::_('behavior.framework','more');

            $doc = \JFactory::getDocument();

		}
		parent::display($tpl);
    }


}


?>