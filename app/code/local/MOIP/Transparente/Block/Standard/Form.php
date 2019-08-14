<?php
/**
 * Transparente - Transparente Payment Module
 *
 * @title      Magento -> Custom Payment Module for Transparente (Brazil)
 * @category   Payment Gateway
 * @package    MOIP_Transparente
 * @author     Transparente Pagamentos S/a
 * @copyright  Copyright (c) 2010 Transparente Pagamentos S/A
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MOIP_Transparente_Block_Standard_Form extends Mage_Payment_Block_Form {

	protected function _construct() {
		$dataToReturn = "";
		$transparente_layout = Mage::getStoreConfig('moipall/config/transparente_layout');
		if ($transparente_layout == "Vertical"):
			$this->setTemplate('MOIP/transparente/vertical_form.phtml');
		else:
			$this->setTemplate('MOIP/transparente/horizontal_form.phtml');
		endif;
		parent::_construct();
	}

	public function boletoDisponivel($dataToReturn) {

		$_Produtos = Mage::getSingleton('checkout/cart')->getQuote()->getAllItems();

		$stockProducts = array();
		$noBoletoProducts = array();
		$result = '';
		$error = false;

		foreach ($_Produtos as $product) {
			$_product = Mage::getModel('catalog/product')->load($product->getProductId());
			if ($_product->getProibirBoleto()) {
				$noBoletoProducts[] = $_product->getName();
				$error = true;
			}
			if ((int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty() <= Mage::getSingleton('transparente/standard')->getConfigData('estoqueboleto')) {
				$stockProducts[] = $_product->getName();
				$error = true;
			}
		}


		if ($error) {
			if (sizeof($stockProducts) > 0) {
				if (sizeof($stockProducts) > 1) {
					$result .= 'Os Produtos '.implode(', ', $stockProducts).' não tem estoque o suficiente para ser comprados por Boleto.<br />';
				}else {
					$result .= 'O Produto '.implode('', $stockProducts).' não tem estoque o suficiente para ser comprado por Boleto.<br />';
				}
			}

			if (sizeof($noBoletoProducts) > 0) {
				if (sizeof($noBoletoProducts) > 1) {
					$result .= 'Os Produtos '.implode(', ', $noBoletoProducts).' não podem ser comprados por Boleto.<br />';
				}else {
					$result .= 'O Produto '.implode('', $noBoletoProducts).' não pode ser comprado por Boleto.<br />';
				}
			}
		}

		if ($dataToReturn == 'text' && $error) {
			return '<div class="alert alert-error">'.$result.'</div>';
		}else if ($dataToReturn == 'valid') {
				return $error;
			}else if ($error) {
				return array('error'=>$error, 'text'=>'<div class="alert alert-error">'.$result.'</div>');
			}

	}

	public function mostraBoleto() {
		if (strpos(Mage::getSingleton('transparente/standard')->getConfigData('formas_pagamento'), "BoletoBancario") !== false) {
			return true;
		}else {
			return false;
		}
	}
	public function mostraTransferencia() {
		if (strpos(Mage::getSingleton('transparente/standard')->getConfigData('formas_pagamento'), "DebitoBancario") !== false) {
			return true;
		}else {
			return false;
		}
	}
	public function mostraCartao() {
		if (strpos(Mage::getSingleton('transparente/standard')->getConfigData('formas_pagamento'), "CartaoCredito") !== false) {
			return true;
		}else {
			return false;
		}
	}


	//textos de desconto
	public function getTextoBoleto($dataToReturn) {
		$valor_pedido = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
		if (Mage::getStoreConfig('moipall/pagamento_avancado/pagamento_boleto')) {
			if ($valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor')) {
				$textoresumo = "Com desconto de: ".Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc')."%.";
			}
			$descontotexto = $textoresumo ."<br/>Págavel em qualquer banco, casas lotéricas ou via internet bank.";


			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2') != "" &&  $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2')  && $valor_pedido < Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$textoresumo = "Com desconto de: ".Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc2')."%.";
				$descontotexto = $textoresumo ."<br/>Págavel em qualquer banco, casas lotéricas ou via internet bank.";
			}

			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') != "" && $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$textoresumo = "Com desconto de: ".Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc3')."%.";
				$descontotexto = $textoresumo ."<br/>Págavel em qualquer banco, casas lotéricas ou via internet bank.";
			}
		}else {
			$textoresumo = "Págavel em qualquer banco";
			$descontotexto = "Págavel em qualquer banco, casas lotéricas ou via internet bank.";
		}
		if ($dataToReturn == 'preview') {
			return $textoresumo;
		} else if ($dataToReturn == "texto" ) {
				return $descontotexto;
			}
	}
	public function getTextoTranferencia($dataToReturn) {
		$valor_pedido = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
		if (Mage::getStoreConfig('moipall/pagamento_avancado/transf_desc')) {
			if ($valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor')) {
				$textoresumo = "Com desconto de: ".Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc')."%.";
			}
			$descontotexto = $textoresumo ."<br/>Págavel unicamente via internet bank.";


			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2') != "" &&  $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2')  && $valor_pedido < Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$textoresumo = "Com desconto de: ".Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc2')."%.";
				$descontotexto = $textoresumo ."<br/>Págavel unicamente via internet bank.";
			}

			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') != "" && $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$textoresumo = "Com desconto de: ".Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc3')."%.";
				$descontotexto = $textoresumo ."<br/>Págavel unicamente via internet bank.";
			}

		}else {
			$textoresumo = "Págavel via internet bank.";
			$descontotexto = "";
		}
		if ($dataToReturn == 'preview') {
			return $textoresumo;
		} else if ($dataToReturn == "texto") {
				return $descontotexto;
			}
	}

	//Icones Principais
	public function getBoletoIcon() {
		if (Mage::getStoreConfig('moipall/config/trocar_icone')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/icone_boleto');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/ababoleto.png');
		}
	}
	public function getTransferenciaIcon() {
		if (Mage::getStoreConfig('moipall/config/trocar_icone')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/icone_transf');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/abatransf.png');
		}
	}
	public function getCartaoIcon() {
		if (Mage::getStoreConfig('moipall/config/trocar_icone')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/icone_cartao');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/abacartao.png');
		}
	}

	//imagem do boleto
	public function getBoletoImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/boleto');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Bradesco.png');
		}
	}

	//imagens de transferencia
	public function getBBImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_bb');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/BancoDoBrasil.png');
		}
	}
	public function getBradescoImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_bradesco');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Bradesco.png');
		}
	}
	public function getItauImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_itau');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Itau.png');
		}
	}
	public function getBanrisulImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_banrisul');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Banrisul.png');
		}
	}

	//imagens de cartao
	public function getVisaImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_visa');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Visa.png');
		}
	}
	public function getMastercardImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_master');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Mastercard.png');
		}
	}
	public function getDinersImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_diners');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Diners.png');
		}
	}
	public function getAmericanExpressImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_american');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/AmericanExpress.png');
		}
	}
	public function getHipercardImage() {
		if (Mage::getStoreConfig('moipall/config/trocar_bandeira_cartao')) {
			return Mage::getBaseUrl('media') . "moip/alltransparente/". Mage::getStoreConfig('moipall/config/cartao_hipercard');
		}else {
			return $this->getSkinUrl('MOIP/transparente/imagem/Hipercard.png');
		}
	}


	//confs de parcelamento
	public function getParcelamento($dataToReturn) {
		$parcelas = array();
		$k = "";
		$parcelax = "";
		$precox = "";
		$api = Mage::getSingleton('transparente/api');
		$api->setContaTransparente(Mage::getSingleton('transparente/standard')->getConfigData('conta_transparente'));
		$api->setAmbiente(Mage::getSingleton('transparente/standard')->getConfigData('ambiente'));

		$cartTotal = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();

		if ($cartTotal > 5) {
			$parcelamento = $api->getParcelamento($cartTotal);
			$parcela_decode = Mage::helper('core')->jsonDecode($parcelamento,true);
			foreach ($parcela_decode as $key => $value) {
				
				if ($key <= Mage::getSingleton('transparente/standard')->getConfigData('nummaxparcelamax')) {
					
					
						$juros = $parcela_decode[$key]['juros'];
						$parcelas_result = $parcela_decode[$key]['parcela'];
						$total_parcelado = $parcela_decode[$key]['total_parcelado'];
						if($juros > 0)
							$asterisco = '*';
						else 
							$asterisco = '';
						$parcelas[]= '<option value="'.$key.'">'.$key.'x de '.$parcelas_result.' no total de '.$total_parcelado.' '.$asterisco.'</option>';
					
					}
				}
		}else {
			$parcelas = "<option value=\"1\"> Pagamento à vista </option>";
		}

		if ($dataToReturn == 'preview') {
			if($parcelax > 0){
			$v['valor'] = Mage::helper('core')->currency($parcelax, true, false);
			return "{$v['valor']}";
			}
			else {
				$cartTotal = Mage::helper('core')->currency($cartTotal, true, false);
				return "{$cartTotal}";
			}
		}
		if ($dataToReturn == 'preview_parcelas') {
			if($k > 1){
			return "Pague em até {$k}x";
			} else {
				return "Pague em à vista";
			}

		}
		if ($dataToReturn == 'parcelas'){
			return $parcelas;
		}
	}
	public function getTextoParcelas() {

		if( $tipo_parcelamento = Mage::getSingleton('transparente/standard')->getConfigData('jurostipo') == 1){
			$parcelamento =  Mage::getSingleton('transparente/standard')->getInfoParcelamento();
			if ($parcelamento['c_juros1'] == 0) {
				echo "<div id=\"addparcelasdesc\"> Sem juros até ".$parcelamento['c_ate1']." parcelas";
				if ($parcelamento['c_ate1'] < 13) {
					return ", após juros de 1,99% ao mês.</div>";
				}
			}else {
				return "<div id=\"addparcelasdesc\"> Com juros de ".$parcelamento['c_juros1']."% ao mês.</div>";
			}
		} else {
			return; 
		}
	}

	//get customer data
	public function getCustomerData($selector) {

		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			if ($selector == "nome") {
				return Mage::getSingleton('customer/session')->getCustomer()->getName();
			}elseif ($selector == "cpf") {
				return Mage::getSingleton('customer/session')->getCustomer()->getTaxvat();
			}elseif ($selector == "telefone") {
				return Mage::getSingleton('customer/session')->getCustomer()->getTelephone();
			}elseif ($selector == "dob") {
				$dn =  Mage::getSingleton('customer/session')->getCustomer()->getdob();
				return date("d/m/Y", strtotime($dn));
			}

		}

	}

	public function getBoletoPrice() {
		$desconto = 0;
		$valor_pedido = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
		if (Mage::getStoreConfig('moipall/pagamento_avancado/pagamento_boleto')) {
			if ($valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor')) {
				$desconto = (float)Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc');
			}
			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2') != "" &&  $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2')  && $valor_pedido < Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$desconto = (float)Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc2');
			}

			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') != "" && $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$desconto = (float)Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc3');
			}
			return "R$".number_format((float)$valor_pedido-((float)$valor_pedido/100*$desconto), 2);
		}

		else {
			return "R$".number_format((float)$valor_pedido, 2);
		}


	}

	public function getTransferenciaPrice() {
		$desconto = 0;
		$valor_pedido = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
		if (Mage::getStoreConfig('moipall/pagamento_avancado/transf_desc')) {
			if ($valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor')) {
				$desconto = (float)Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc');
			}
			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2') != "" &&  $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor2')  && $valor_pedido < Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$desconto = (float)Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc2');
			}

			if (Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') != "" && $valor_pedido >= Mage::getStoreConfig('moipall/pagamento_avancado/boleto_valor3') ) {
				$desconto = (float)Mage::getStoreConfig('moipall/pagamento_avancado/boleto_desc3');
			}
			return "R$".number_format((float)$valor_pedido-((float)$valor_pedido/100*$desconto), 2);
		}

		else {
			return "R$".number_format((float)$valor_pedido, 2);
		}

	}
}
