<?php

namespace Purplebeanie\View;

class PbFrontView extends \JViewLegacy {

    function display($tpl = null)
    {

		\JHtml::_('jquery.framework');
		parent::display($tpl);
    }


}


?>