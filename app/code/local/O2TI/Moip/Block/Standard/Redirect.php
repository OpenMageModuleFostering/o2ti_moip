<?php
class O2TI_Moip_Block_Standard_Redirect extends Mage_Sales_Block_Order_Totals{
    public function __construct(){
		parent::__construct();
		$this->setTemplate("O2TI/moip/redirect.phtml");
	}
}
