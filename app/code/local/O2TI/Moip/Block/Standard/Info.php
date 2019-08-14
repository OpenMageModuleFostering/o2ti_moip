<?php
class O2TI_Moip_Block_Info extends Mage_Payment_Block_Info{
    protected function _construct(){
        parent::_construct();
        $this->setTemplate('O2TI/moip/info.phtml');
    }
 
}


