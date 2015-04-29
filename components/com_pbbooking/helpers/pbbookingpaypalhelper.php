<?php
/**
* @package		PurpleBeanie.PBBooking
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @link		http://www.purplebeanie.com
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
  

class Pbbookingpaypalhelper
{
	/**
	* builds and returns paypal payment form for service
	* @param object service
	* @since 2.4
	* @access public
	* @return string some html to insert
	*/

	public static function build_form_for_service($service)
	{
		$db = JFactory::getDbo();
		$config = $GLOBALS['com_pbbooking_data']['config'];

		$url = ($config->paypal_test && $config->paypal_test == 1) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$business = $config->paypal_email;
		$item_name = $service->name;
		$currency = $config->paypal_currency;
		$uri = JURI::getInstance();
		$return_url = $uri->getScheme().'://'.$uri->getHost().JRoute::_('index.php?option=com_pbbooking&task=paypalpending');

		$return = '
			<form action="'.$url.'" method="post" id="pbbooking-paypal" onsubmit="return false;">
				<input type="submit" value="" id="paypal-buy-now" style="background:url(\''.JURI::root(false).'components/com_pbbooking/images/btn_buynowCC_LG.gif\');width:107px;height:47px;border:0px;margin:0px auto;">
				<input type="hidden" name="cmd" value="_cart"/> 
				<input type="hidden" name="upload" value="1"/> 
				<input type="hidden" name="business" value="'.$business.'"/> 
				<input type="hidden" name="item_name_1" value="'.$item_name.'"/> 
				<input type="hidden" name="amount_1" value="'.\Pbbooking\Pbbookinghelper::pbb_money_format($service->price,true).'"/> 
				<input type="hidden" name="currency_code" value="'.$currency.'"/>
				<input type="hidden" NAME="return" value="'.$return_url.'"/>
				<input type="hidden" name="notify_url" value=""/>
				<input type="hidden" name="custom" value=""/>
			</form>
		';

		return $return;

	}
}
