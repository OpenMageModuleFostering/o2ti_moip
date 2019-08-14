<?php
/**
 * MoIP - Moip Payment Module
 *
 * @title      Magento -> Custom Payment Module for Moip (Brazil)
 * @category   Payment Gateway
 * @package    O2TI_Moip
 * @author     O2ti solucoes web ldta
 * @copyright  Copyright (c) 2010 MoIP Pagamentos S/A
 * @license    Autorizado o uso por tempo indeterminado
 */
class O2TI_Moip_StandardController extends Mage_Core_Controller_Front_Action {
	public function getOrder() {
		if ($this->_order == null) {}
		return $this->_order;
	}
	public function getStandard() {
		return Mage::getSingleton('moip/standard');
	}
	protected function _expireAjax() {
		if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit;
		}
	}
	public function redirectAction() {
		$session = Mage::getSingleton('checkout/session');
		$standard = $this->getStandard();
		$fields = $session->getMoIPFields();
		$fields['id_transacao'] = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$pgtoArray = $session->getPgtoArray();
		$api = Mage::getModel('moip/api');
		$api->setAmbiente($standard->getConfigData('ambiente'));
		$xml = $api->generateXML($fields, $pgtoArray);
		Mage::register('xml', $xml);
		$formapgto = $api->generateforma($fields, $pgtoArray);
		Mage::register('formapgto', $formapgto);
		$formapg = $api->generateformapg($fields, $pgtoArray);
		Mage::register('formapg', $formapg);
		$token = $api->getToken($xml);
		$session->setMoipStandardQuoteId($session->getQuoteId());
		Mage::register('token', $token['token']);
		Mage::register('erro', $token['erro']);
		Mage::register('StatusPgdireto', $token['pgdireto_status']);
		Mage::register('current_order', Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId()));
		$this->loadLayout();
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('O2TI_Moip_Block_Standard_Redirect'));
		$this->renderLayout();

		$session->unsQuoteId();
	}

	public function cancelAction() {
		$session = Mage::getSingleton('checkout/session');
		$session->setQuoteId($session->getMoipStandardQuoteId(true));

		if ($session->getLastRealOrderId()) {
			$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
			if ($order->getId()) {
				$order->cancel()->save();
			}
		}
		$this->_redirect('checkout/cart');
	}

	public function successAction() {
		$standard = $this->getStandard();
		$order = Mage::getModel('sales/order');
		$session = Mage::getSingleton('checkout/session');
		if (!$this->getRequest()->isPost()) {
			$session->setQuoteId($session->getMoipStandardQuoteId(true));
			Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
			$order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
			if ($order->getId()) {
			}
			$this->_redirect('Moip/standard/redirect/', array('_secure' => true));
		} else {
			$data = $this->getRequest()->getPost();
			$login = $standard->getConfigData('conta_moip');
			$order->loadByIncrementId(ereg_replace($login, "", $data['id_transacao']));
			$LastRealOrderId = ereg_replace($login, "", $data['id_transacao']);
			$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
			$sql = "SELECT * FROM moip WHERE sale_id IN (".$LastRealOrderId.") AND status ='Sucesso'";
			$_venda = $conn->fetchAll($sql);
			foreach ($_venda as $venda) {
				$tokenpagamento = $venda['xml_return'];
				$Formadepagamento = $venda['formapg'];
				$bandeira = $venda['bandeira'];
			}


			if ($order->isCanceled() && $data['status_pagamento'] != 5) {
				if (Mage::helper('sales/reorder')->canReorder($order)) {
					$orderId = $order['entity_id'];

					$order2 = Mage::getModel('sales/order')->load($orderId);
					$order2->setState(
					    Mage_Sales_Model_Order::STATE_HOLDED,
					    Mage_Sales_Model_Order::STATE_HOLDED
					);
					$order2->save();
					foreach ($order2->getAllItems() as $item) {
					    $item->setQtyCanceled(0);
					    $item->save();
					}
					$order2 = Mage::getModel('sales/order')->load($orderId);
					$order2->getState() == Mage_Sales_Model_Order::STATE_HOLDED;
				}
			}elseif($order->isCanceled() && $data['status_pagamento'] == 5){
				return false;
			}
			switch ($data['status_pagamento']) {
			case 1:
				if ($_SERVER['SERVER_ADDR'] == "208.82.206.66" || $_SERVER['SERVER_ADDR'] == "69.162.91.38") {
					$connRW = Mage::getSingleton('core/resource')->getConnection('core_write');
					$query = array("UPDATE `moip` SET num_parcelas='".$data['parcelas']."', ult_dig='".$data['cartao_final']."' WHERE sale_id IN (".$LastRealOrderId.");");
					$state = Mage_Sales_Model_Order::STATE_PROCESSING;
					$status = 'authorized';
					$comment = $this->getStatusPagamentoMoip($data['status_pagamento']);
					$comment = $comment ." Pagamento realizado por: ". $this->getNomePagamento($Formadepagamento);
					$comment = $comment ."\n Via instuição: ". $bandeira;
					$comment =  $comment; "\n ID MOIP" .$data['cod_moip'];
					$invoice = $order->prepareInvoice();
                                if ($this->getStandard()->canCapture())
                                        {
                                                $invoice->register()->capture();
                                        }
                                Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder())
                                ->save();
                                $invoice->sendEmail();
                                $invoice->setEmailSent(true);
                                $invoice->save();

				$order->setState($state, $status, '', $notified = true, $includeComment = false);
				$order->save();
				}
				else {
					$state = Mage_Sales_Model_Order::STATE_CANCELED;
					$status = 'canceled';
					$comment = "Tentativa de Fraude no retorno Moip";

					$order->cancel();
				}
				break;
			case 2:
				$state = Mage_Sales_Model_Order::STATE_HOLDED;
				$status = 'iniciado';
				$comment = $this->getStatusPagamentoMoip($data['status_pagamento']);
				$comment = $comment ." Pagamento realizado por: ". $this->getNomePagamento($Formadepagamento);
				$comment = $comment ."\n Via instuição: ". $bandeira;

				Mage::dispatchEvent('moip_order_canceled_fraud', array("order" => $order));
				break;
			case 3:
				$state = Mage_Sales_Model_Order::STATE_HOLDED;
				$status = 'boleto_impresso';
				$comment = $this->getStatusPagamentoMoip($data['status_pagamento']);
				$comment = $comment. "\n ID MOIP " .$data['cod_moip'];
				Mage::dispatchEvent('moip_order_hold_printed', array("order" => $order));
				break;
			case 4:
				return false;
				break;
			case 5:
				$state = Mage_Sales_Model_Order::STATE_CANCELED;
				$status = 'canceled';
				$comment = $this->getStatusPagamentoMoip($data['status_pagamento']);
				$comment = $comment ." Pagamento realizado por: ". $this->getNomePagamento($Formadepagamento);
				$comment = $comment ."\n Via instuição: ". $bandeira;
				$comment = $comment . "\n ID MOIP " .$data['cod_moip']. "\n Motivo: ".utf8_encode($data['classificacao']);
				Mage::dispatchEvent('moip_order_canceled', array("order" => $order));
				$this->_sendStatusMail($order, $tokenpagamento);
				$order->cancel();
				break;
			case 6:
				$state = Mage_Sales_Model_Order::STATE_HOLDED;
				$status = 'payment_review';
				$comment = $this->getStatusPagamentoMoip($data['status_pagamento']);
				$comment = $comment ." Pagamento realizado por: ". $this->getNomePagamento($Formadepagamento);
				$comment = $comment ."\n Via instuição: ". $bandeira;
				$comment = $comment. "\n ID MOIP " .$data['cod_moip'];
				Mage::dispatchEvent('moip_order_holded_review', array("order" => $order));
				break;
			}
			$order->setState($state, $status, $comment, $notified = true, $includeComment = true);
			$order->save();
			$order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
			if ($order->getId()) {}

			if($status == 'authorized'){
				Mage::dispatchEvent('moip_order_authorize', array("order" => $order));
			}
			echo 'Processo de retorno concluido para o pedido #'.$data['id_transacao'];
		}
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

	private  function _sendStatusMail($order, $tokenpagamento)
    	{
		$emailTemplate  = Mage::getModel('core/email_template');
		$emailTemplate->loadDefault('o2ti_ordem_tpl');
		$emailTemplate->setTemplateSubject('Pedido Cancelado');
		$salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
		$salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');
		$emailTemplate->setSenderName($salesData['name']);
		$emailTemplate->setSenderEmail($salesData['email']);
		$emailTemplateVariables['username']  = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
		$emailTemplateVariables['order_id'] = $order->getIncrementId();
		$emailTemplateVariables['token'] = $tokenpagamento;
		$emailTemplateVariables['store_name'] = $order->getStoreName();
		$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$emailTemplate->send($order->getCustomerEmail(), $order->getStoreName(), $emailTemplateVariables);
    	}
	public function email_erro_pgtoAction() {
		if ($_GET['erro'] != "true"):
		$erro = $_GET['erro'];
		$pedido = $_GET['pedido'];
		$navegador = $_GET['navegador'];
		Mage::log("Cliente do pedido ".$pedido. " - Erro - " .$erro. " navegador ". $navegador, null, 'O2TI_Moip.log', true);
		endif;

	}
	public function buscaCepAction() {
		if ($_GET['meio'] == "buscaend") {
			function simple_curl($url, $post=array(), $get=array()) {
				$url = explode('?', $url, 2);
				if (count($url)===2) {
					$temp_get = array();
					parse_str($url[1], $temp_get);
					$get = array_merge($get, $temp_get);
				}
				$ch = curl_init($url[0]."?".http_build_query($get));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				return curl_exec($ch);
			}
			$cep = $_GET['s'];
			$vSomeSpecialChars = array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ç", "Ç", "ã", "Ã", "õ", "Õ");
			$vReplacementChars = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "c", "C", "a", "A", "o", "O");
			$cep = str_replace($vSomeSpecialChars, $vReplacementChars, $cep);
			$cep = preg_replace('/[^\p{L}\p{N}]/u', '+', $cep);
			$html = simple_curl('http://m.correios.com.br/movel/buscaCepConfirma.do', array(
					'cepEntrada'=>''.utf8_encode($cep).'',
					'metodo'=>'buscarCep'
				));
			$topo = "<style>#divTelaAguarde, .secao, .divopcoes, .mopcoes, .botoes, .rodape  { display:none;}.caixacampobranco {padding: 8px 8px 8px 8px;margin-top: 5px;background-color: #E0E2EE;-moz-border-radius: 3px;-moz-border-radius-bottomright: 10px;-webkit-border-radius: 3px;-webkit-border-bottom-right-radius: 10px;}.caixacampoazul {padding: 8px 8px 8px 8px;margin-top: 5px;background-color: #DADCEB;-moz-border-radius: 3px;-webkit-border-radius: 3px;-moz-border-radius-bottomright: 10px;-webkit-border-bottom-right-radius: 10px;}.conteudo {max-width: 372px;text-align: left;padding: 10px;}</style>";
			echo $topo;
			$html = $html;
			echo $html;
		}
		if ($_GET['meio'] == "cep") {
			function simple_curl($url, $post=array(), $get=array()) {
				$url = explode('?', $url, 2);
				if (count($url)===2) {
					$temp_get = array();
					parse_str($url[1], $temp_get);
					$get = array_merge($get, $temp_get);
				}
				$ch = curl_init($url[0]."?".http_build_query($get));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				Mage::log(curl_exec($ch));
				return curl_exec($ch);

			}
			$cep = $_GET['cep'];
			$dados['tipo_logradouro'] = "";
			$html = simple_curl('http://www.buscacep.correios.com.br/servicos/dnec/consultaLogradouroAction.do', array(
					'relaxation'=>$cep,
					'TipoConsulta'=>'relaxation',
					'StartRow'=>'1',
					'EndRow'=>'10',
					'Metodo'=>'listaLogradouro',
					'TipoCep' => 'ALL',
					'semelhante' => 'N',
					'cfm' => '1'
				));
			$start = strpos($html, '<table border="0" cellspacing="1" cellpadding="5" bgcolor="gray">');
			$end = strpos($html, '</table>');
			$tabela = substr($html, $start);
			$startTable = strpos($tabela,'<table');
			$endTable = strpos($tabela,'</table>');
			$table = substr($tabela,$startTable,$endTable - $startTable)."</table>";
			$dom = new Zend_Dom_Query($tabela);
			$results = $dom->query('td');
			$counter = 0;
			foreach ($results as $result) {
				switch ($counter) {
				case 0:
					$logradouro = trim(($result->textContent));
					$logradouro = explode("-", $logradouro);
					$logradouro = trim($logradouro[0]);
					break;
				case 1:
					$bairro = trim(($result->textContent));
					break;
				case 2:
					$cidade = trim(($result->textContent));
					break;
				case 3:
					$estado = trim(($result->textContent));
					break;
				}
				$counter++;
			}
			$dados =
				array(
				"logradouro"=> $logradouro,
				"bairro"=> $bairro,
				"cidade" => $cidade,
				"uf"=> $estado,
				"cep"=> $cep
			);

			switch ($dados['uf']) {
			case "AC":
				$estado = "485";
				break;
			case "AL":
				$estado = "486";
				break;
			case "AP":
				$estado = "487";
				break;
			case "AM":
				$estado = "488";
				break;
			case "BA":
				$estado = "489";
				break;
			case "CE":
				$estado = "490";
				break;
			case "DF":
				$estado = "491";
				break;
			case "ES":
				$estado = "492";
				break;
			case "GO":
				$estado = "493";
				break;
			case "MA":
				$estado = "494";
				break;
			case "MT":
				$estado = "495";
				break;
			case "MS":
				$estado = "496";
				break;
			case "MG":
				$estado = "497";
				break;
			case "PA":
				$estado = "498";
				break;
			case "PB":
				$estado = "499";
				break;
			case "PR":
				$estado = "500";
				break;
			case "PE":
				$estado = "501";
				break;
			case "PI":
				$estado = "502";
				break;
			case "RJ":
				$estado = "503";
				break;
			case "RN":
				$estado = "504";
				break;
			case "RS":
				$estado = "505";
				break;
			case "RO":
				$estado = "506";
				break;
			case "RR":
				$estado = "507";
				break;
			case "SC":
				$estado = "508";
				break;
			case "SP":
				$estado = "509";
				break;
			case "SE":
				$estado = "510";
				break;
			case "TO":;
				$estado = "511";
				break;
			}
			$dados['valor_uf'] = $estado;
			/*if ($estado != "") {
				$separa_end = explode('- ', $dados['logradouro']);
				if ($dados['uf'] != ""):
					$texto = array('logradouro'=>$logradouro, 'bairro'=>$bairro, 'estado' => $cidade, 'estado'=>$estado);
					//$texto = utf8_decode($separa_end[0]).":".utf8_decode($dados['bairro']).":".utf8_decode($dados['cidade']).":".$estado.";";
				else:
					$texto = $dados['tipo_d']." :".$dados['d'].":".utf8_decode($dados['logradouro']).":".$estado.";";
				endif;
			}*/
			echo json_encode($dados);
		}
	}
}
