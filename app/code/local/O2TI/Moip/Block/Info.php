<?php

class O2TI_Moip_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('O2TI/moip/info.phtml');
    }

   
	
    public function getMoip()
    {
        return Mage::getSingleton('moip/api');
    }

   

	private function getNomePagamento($param) {
		$nome = "";
		switch ($param) {
		case "BoletoBancario":
		    $nome = "Boleto Bancário";
		    break;
		case "DebitoBancario":
		    $nome = "Transferência Bancária";
		    break;
		case "CartaoCredito":
		    $nome = "Cartão de Crédito";
		    break;
		}
		return $nome;
	}


    protected function _prepareInfo()
    {
            
                $order_get = Mage::app()->getRequest()->getParam('order_id');              
                $order = $this->getInfo()->getOrder();
                $order =  $order->getIncrementId();
                $model = Mage::getModel('moip/write');
                $result = $model->load($order, 'realorder_id');
                $dados = array();
                $dados['result_meio'] = $this->getNomePagamento($result->getMeio_pg());
                $dados['meio_pago'] = $this->getNomePagamento($result->getMeio_pg());
                $dados['realorder_id'] = $result->getRealorder_id();
                $dados['order_idmoip'] = $result->getorder_idmoip(); 
                $dados['boleto_line'] = $result->getBoleto_line();
                $dados['brand'] = $result->getBrand();
                $dados['creditcard_parc'] = $result->getCreditcard_parc();
                $dados['first6'] = $result->getFirst6();
                $dados['last4'] = $result->getLast4();
                $dados['token'] = $result->getToken();
                $dados['url'] = $result->getUrlcheckout_pg();
            return $dados;
    }
}
