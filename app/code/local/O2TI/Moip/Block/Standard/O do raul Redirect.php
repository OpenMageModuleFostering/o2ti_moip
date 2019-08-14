<?php
class O2TI_Moip_Block_Standard_Redirect extends Mage_Sales_Block_Order_Totals{
	public $data = $this->getRequest()->getParams();
	public $token = '';
	public $moipToken = Mage::registry('token');
	public $tokenpagamento = '';
	public $refazerpagamento = '';
	public $xmlgerado = Mage::registry('xml');
	public $standard = Mage::getSingleton('moip/standard');
	public $sale_id =  Mage::getSingleton('checkout/session')->getLastRealOrderId();
	public $realorder_id =  Mage::getSingleton('checkout/session')->getLastOrderId();
	public $order = Mage::getModel('sales/order')->load($this->realorder_id);
	public $address = Mage::getModel('sales/order_address')->load($shippingId);
	protected $urldosite = Mage::getBaseUrl('web', true);
	public $totalparaparcelado = $order->getGrandTotal();


	public function __construct() {
		parent::__construct();

		if (Mage::registry('erro') !="") {
			echo Mage::registry('erro');
			Mage::log(Mage::registry('erro'), null, 'O2TI_Moip.log', true);
			Mage::log(Mage::registry('xml'), null, 'O2TI_Moip.log', true);
		}

		if (!$this->data['token']) {
			//primeira compra grava os dados.
			if (!$this->moipToken) {
				$url = $this->moipToken;
				$xml_sent = (string)$this->xmlgerado;
				$session = Mage::getSingleton('customer/session');
				$customer = $session->getCustomer();
				if ($this->order->getIsVirtual()) {
					$shippingId = $this->order->getBillingAddress()->getId();
				}
				else {
					$shippingId = $this->order->getShippingAddress()->getId();
				}
				$status = "Sucesso";
				$connR = Mage::getSingleton('core/resource')->getConnection('core_read');
				$sql = "SELECT *
			          FROM moip
			          WHERE sale_id IN (".$this->sale_id.") AND status ='Sucesso'";
				$_venda = $connR->fetchAll($sql);
				foreach ($_venda as $venda) {
					$tokenpagamento = $venda['xml_return'];
				}
				if ($tokenpagamento == "") {
					$conn = Mage::getSingleton('core/resource')->getConnection('core_write');
					$results = $conn->query("INSERT INTO `moip` (`transaction_id` ,`realorder_id` ,`sale_id` ,`xml_sent` ,`xml_return` ,`status` ,`formapg` ,`bandeira` ,`digito` ,`vencimento` ,`datetime`) VALUES (NULL , '".$this->realorder_id."', '".$this->sale_id."', '".$xml_sent."', '".$this->moipToken."', '".$status."', '".$opcaopg['forma_pagamento']."', '".$bandeira."', '', '".$vencpedido."', '".date('Y-m-d H:i:s')."');");
					$tokenpagamento = $this->moipToken;
					$url = $this->moipToken;
				}
			}
			else {
				//usado para o reload da pagina pelos infelizes clientes que navegam com ie.
				$tokenpagamento = $this->moipToken;
				$url = $tokenpagamento;
				$status = "Sucesso";
			}
		}else {//refaz pedido vindo do my acocunt

			$LastRealOrderId = $data['realorder'];
			$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
			$sql = "SELECT * FROM moip WHERE sale_id IN ({$LastRealOrderId})";
			$_venda = $conn->fetchAll($sql);
			foreach ($_venda as $venda) {
				$tokenpagamento = $venda['xml_return'];
				$url = $venda['xml_return'];
				$incrementId = $venda['realorder_id'];
				$order = $data['realorder'];
				$opcaopg['forma_pagamento'] = $venda['formapg'];
				$bandeira = $venda['bandeira'];
				$connRW = Mage::getSingleton('core/resource')->getConnection('core_write');
				$results = $connRW->query("UPDATE `moip` SET vencimento='".$vencpedido."' WHERE sale_id IN (".$LastRealOrderId.");");
			}
			$refazerpagamento = 1;
			$url = $venda['xml_return'];
			$order = Mage::getModel('sales/order')->load($incrementId);
			$session = Mage::getSingleton('customer/session');

			$oque = $order->getIsVirtual();
			if ($oque) {
				$shippingId = $order->getBillingAddress()->getId();
				}
			else {
				$shippingId = $order->getShippingAddress()->getId();
				}
		}

		$this->setTemplate("O2TI/moip/redirect.phtml");
	}
	public function getMoipUrl() {
		if ($enviapara == "teste") {
			Mage::log(Mage::registry('erro'), null, 'O2TI_Moip.log', true);
			Mage::log(Mage::registry('xml'), null, 'O2TI_Moip.log', true);
			Mage::log(Mage::registry('token'), null, 'O2TI_Moip.log', true);
			$urldomoip = "https://desenvolvedor.moip.com.br/sandbox";
		}
		else {
			$urldomoip = "https://www.moip.com.br/";
		}
		return $urldomoip;
	}
	public function getGoogleId() {
		return Mage::getStoreConfig('o2tiall/google/idgoogle');
	}
	public function getOpcaoPagamento() {
		$opcaopg = Mage::getModel('moip/api')->generatemeiopago(Mage::registry('formapg'));
		if ($opcaopg['forma_pagamento'] == "DebitoBancario") {
			$bandeira = $opcaopg['debito_instituicao'];
			$vencpedido = date('c', strtotime("+2 days"));
		}
		if ($opcaopg['forma_pagamento'] == "CartaoCredito" || $opcaopg['forma_pagamento'] == "Cofre") {
			$bandeira = $opcaopg['credito_instituicao'];
			$vencpedido = date('c', strtotime("+2 days"));
		}
		if ($opcaopg['forma_pagamento'] == "BoletoBancario") {
			$bandeira = "Bradesco";
			$vencpedido = $opcaopg['timevencimentoboleto'];
		}
		return  array($bandeira, $vencpedido);
	}
	public function getSiteUrl(){
		return $this->urldosite;
	}
	public function getSalesId(){
		return $this->sale_id;
	}
}
