<?php
class O2TI_Onestepcheckout_Model_Observer
{
	public function model_config_data_save_before($ovserver)
	{
		$config_onecheckout = $_POST;
		$config = $config_onecheckout['groups']['config']['fields'];
		
				Mage::getModel('core/config')->saveConfig('customer/address/street_lines',4);
			
		
		
		
	}
	public function checkout_cart_add_product_complete($ovserver)
	{
		if(Mage::getStoreConfig('onestepcheckout/config/disable_shop_cart'))
		{				
			Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));			
			Mage::app()->getResponse()->sendResponse();
			exit;
		}
	}
}
