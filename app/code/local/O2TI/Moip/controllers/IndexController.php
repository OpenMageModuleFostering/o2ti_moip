<?php
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';
class O2TI_Moip_IndexController extends Mage_Adminhtml_Sales_OrderController {
	protected function _construct() {
		$this -> setUsedModuleName('O2TI_Moip');
	}

	public function indexAction() {
		echo "test";
		$orderIds = $this -> getRequest() -> getPost('order_ids');

		$counter = 0;
		foreach ($orderIds as $currentOrderId) {
			$order = Mage::getModel("sales/order") -> load($currentOrderId);
			$done =false;
			try {
				$state = Mage_Sales_Model_Order::STATE_PROCESSING;
				$status = 'authorized';
				$comment = $this->getStatusPagamentoMoip($data['status_pagamento']);
				$comment = "Pedido Autorizado via Admin".$comment ." Pagamento realizado por: ". $this->getNomePagamento($Formadepagamento);
				$comment = $comment ."\n Via instuição: ". $bandeira;
				$comment =  $comment. "\n ID MOIP" .$data['cod_moip']. "\n Pagamento direto no MoiP https://www.moip.com.br/Instrucao.do?token=" .$LastRealOrderId;
				$order->setState($state, $status, $comment, $notified = true, $includeComment = true);
				$order->save();
				$order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
				Mage::dispatchEvent('moip_order_authorize', array("order" => $order));
				$done = true;
				$counter++;
			} catch (Exception $e) {
				$order -> addStatusHistoryComment('Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: ' . $e -> getMessage(), false);
				$order -> save();
				$this->_getSession()->addError($this->__('Não foi possivel autorizados o pedido %s ', $currentOrderId));
			}
		}
		if($done){
			$this->_getSession()->addSuccess($this->__('%s pedidos autorizados com sucesso', $counter));
		}
		$this -> _redirect('adminhtml/sales_order/', array());
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
	private function getStatusPagamentoMoip($param) {
		$status = "";
		switch ($param) {
		case 1:
			$status = "Autorizado";
			break;
		case 2:
			$status = "Iniciado";
			break;
		case 3:
			$status = "Boleto Impresso";
			break;
		case 4:
			$status = "Concluido";
			break;
		case 5:
			$status = "Cancelado";
			break;
		case 6:
			$status = "Em análise";
			break;
		case 7:
			$status = "Estornado";
			break;
		}
		return $status;
	}

}
