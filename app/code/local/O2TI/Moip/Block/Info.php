<?php

class O2TI_Moip_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('O2TI/moip/info.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->_prepareInfo();
        return parent::_beforeToHtml();
    }
	
    public function getMoip()
    {
        return Mage::getSingleton('moip/standard');
    }

   

	private function getNomePagamento($param) {
		$nome = "";
		switch ($param) {
		case "BoletoBancario":
		    $nome = "Boleto Bancário";
		    break;
		case "DebitoBancario":
		    $nome = "Debito Bancário";
		    break;
		case "CartaoCredito":
		    $nome = "Cartão de Crédito";
		    break;
		}
		return $nome;
	}


    protected function _prepareInfo()
    {

            $order = $this->getInfo()->getQuote();

    }
}
