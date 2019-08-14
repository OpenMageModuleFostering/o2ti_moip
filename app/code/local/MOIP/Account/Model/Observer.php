<?php
class MOIP_Account_Model_Observer
{

	public function sales_order_invoice_save_after($observer)
	{

		$invoice = $observer->getEvent()->getInvoice();
		$order = $invoice->getOrder();
		$order_id = $invoice->getOrder()->getId();
		$order_debug = $invoice->getOrder()->Debug();
		$payment = $order->getPayment();
		$addinfo = $payment->getAdditionalInformation();
		$invoices = $invoice->getOrder()->hasInvoices();
		$method = $payment->getMethod();
		$shipping_method = $order->getShippingDescription();
		$shipping = $order->getShippingMethod();
		$id_address = $order->getShippingAddressId();
		$street = $order->getShippingAddress()->getStreet(1);
		$address = $order->getShippingAddress();
		$debug = array(
			'invoce' => $invoice,
			'order_id' => $order_id,
			'method' => $method,
			'shipping' => $shipping_method,
			'shipping_code' => $shipping,
			'id_address' => $id_address,
			'street' => $street,
			'street2' => $street2,
			);
		$debug_json = json_encode($debug);
		$code_service = $this->_getShippingMethod($shipping_code);

		if($code_service == 'false'){
			return;
		}
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
		    <soapenv:Envelope
		        xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
		        xmlns:vis="http://www.visualset.inf.br/"
		    />
		');

	#var_dump($order->getCustomerTaxvat());
#die();	
		$configValue = Mage::getStoreConfig('shippingcode/config');
		
		$Usuario = Mage::helper('core')->decrypt($configValue['usuario']);
		$Token = Mage::helper('core')->decrypt($configValue['token']);
		$NrContrato = Mage::helper('core')->decrypt($configValue['NrContrato']);
		$CodigoAdministrativo  = Mage::helper('core')->decrypt($configValue['CodigoAdministrativo']);
		$NrCartao = Mage::helper('core')->decrypt($configValue['NrCartao']);

		$Header = $xml->addChild('Header');
		$body = $xml->addChild('Body');
		$PostarObjeto = $body->addChild('PostarObjeto', null,'http://www.visualset.inf.br/');
		$PostagemVipp = $PostarObjeto->addChild('PostagemVipp', null,'http://www.visualset.inf.br/');
		$PerfilVipp = $PostagemVipp->addChild('PerfilVipp', null,'http://www.visualset.inf.br/');
		    $PerfilVipp->addChild('Usuario', 'epsadmin752','http://www.visualset.inf.br/');
		    $PerfilVipp->addChild('Token', '67820778','http://www.visualset.inf.br/');
		    $PerfilVipp->addChild('IdPerfil', '722','http://www.visualset.inf.br/');
			$ContratoEct = $PostagemVipp->addChild('ContratoEct', null,'http://www.visualset.inf.br/');
		    $ContratoEct->addChild('NrContrato', $NrContrato,'http://www.visualset.inf.br/');
		    $ContratoEct->addChild('CodigoAdministrativo', $CodigoAdministrativo,'http://www.visualset.inf.br/');
		    $ContratoEct->addChild('NrCartao', $NrCartao,'http://www.visualset.inf.br/');
		$Destinatario = $PostagemVipp->addChild('Destinatario', null,'http://www.visualset.inf.br/');
		    $Destinatario->addChild('CnpjCpf', preg_replace("/[^0-9]/", "", $order->getCustomerTaxvat()),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('IeRg', null,'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Nome', $order->getCustomerFirstname().' '.$order->getCustomerLastname(),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Endereco', $address->getStreet(1),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Numero', $address->getStreet(2),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Complemento', $address->getStreet(3),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Bairro', $address->getStreet(4),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Cidade', $address->getCity(),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('UF', $address->getRegionCode(),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Cep', preg_replace("/[^0-9]/", "", $address->getPostcode()),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Telefone', preg_replace("/[^0-9]/", "", $address->getTelephone()),'http://www.visualset.inf.br/');
		    $Destinatario->addChild('TelefoneSMS', null,'http://www.visualset.inf.br/');
		    $Destinatario->addChild('Email', $order->getCustomerEmail(),'http://www.visualset.inf.br/');
		$Servico = $PostagemVipp->addChild('Servico', null,'http://www.visualset.inf.br/');
		    $Servico->addChild('ServicoECT', '40436','http://www.visualset.inf.br/');
		$Volumes = $PostagemVipp->addChild('Volumes',null,'http://www.visualset.inf.br/');
		    $VolumeObjeto = $Volumes->addChild('VolumeObjeto',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('Peso',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('Altura',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('Largura',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('Comprimento',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('CodigoBarraVolume',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('CodigoBarraCliente',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('ObservacaoVisual',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('PosicaoVolume',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('Conteudo',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('ValorDeclarado',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('AdicionaisVolume',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('VlrACobrar',null,'http://www.visualset.inf.br/');
		      $VolumeObjeto->addChild('Etiqueta',null,'http://www.visualset.inf.br/');


		$request = $xml->asXML();
		Mage::log('aciona'. $debug_json, null, 'Log_ShippingCode.log', true);
		
		$etiqueta = $this->sendXml($request);
		$this->completeShipment($order, $etiqueta, $shipping, $shipping_method );
	}

	public function sendXml($xml){
		Mage::log($xml, null, 'ShippingCode.xml', true); 
		
		
		$client = new SoapClient('http://sistemas.cvm.gov.br/webservices/Sistemas/SCW/CDocs/WsDownloadInfs.asmx?WSDL', array('trace' => TRUE));

		#$request = file_get_contents('/hd2/sites/magento_erp/magento-ce-1.9.0.1/xml_send.xml');
		#Mage::log($request, null, 'ShippingCode.xml', true); 

		$response = $client->__doRequest($xml, 'http://vpsrv.visualset.com.br/?wsdl', 'http://www.visualset.inf.br/PostarObjeto', '1.2');

        Mage::log($response, null, 'ShippingCode.xml', true); 

		$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
		$xml = simplexml_load_string($xml);
		$json = json_encode($xml);
		$responseArray = json_decode($json,true);
		
		$etiqueta = $responseArray['soapBody']['PostarObjetoResponse']['PostarObjetoResult']['Postagem']['Volumes']['VolumeObjeto']['Etiqueta'];
		
		Mage::log(json_encode($etiqueta), null, 'Log_ShippingCode.log', true); 
		return $etiqueta;
	}

	public function completeShipment($order , $shipmentTrackingNumber, $shipmentCarrierCode, $shipmentCarrierTitle )
	{
	    /**
	     * It can be an alphanumeric string, but definitely unique.
	     */
	    
	    if($shipmentTrackingNumber){
	    	$customerEmailComments = 'Seu produto já foi enviado para expedição e poderá ser rastreado pelo código de postagem. '.$shipmentTrackingNumber;	
	    }
	    
	 
	   
	 
	    if (!$order->getId()) {
	        Mage::throwException("Order does not exist, for the Shipment process to complete");
	    }
	 
	    if ($order->canShip() && $shipmentTrackingNumber) {
	        try {
	            $shipment = Mage::getModel('sales/service_order', $order)
	                            ->prepareShipment($this->_getItemQtys($order));
	 
	            
	 
	            $arrTracking = array(
	                'carrier_code' => 'correios',
	                'title' => isset($shipmentCarrierTitle) ? $shipmentCarrierTitle : $order->getShippingCarrier()->getConfigData('title'),
	                'number' => $shipmentTrackingNumber,
	            );
	 
	            $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
	            $shipment->addTrack($track);
	 
	            // Register Shipment
	            $shipment->register();
	 
	            // Save the Shipment
	            $this->_saveShipment($shipment, $order, $customerEmailComments);
	 
	            // Finally, Save the Order
	            $this->_saveOrder($order, $customerEmailComments);
	        } catch (Exception $e) {
	            throw $e;
	        }
	    }
	}
 
	/**
	 * Get the Quantities shipped for the Order, based on an item-level
	 * This method can also be modified, to have the Partial Shipment functionality in place
	 *
	 * @param $order Mage_Sales_Model_Order
	 * @return array
	 */
	protected function _getItemQtys(Mage_Sales_Model_Order $order)
	{
	    $qty = array();
	 
	    foreach ($order->getAllItems() as $_eachItem) {
	        if ($_eachItem->getParentItemId()) {
	            $qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
	        } else {
	            $qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
	        }
	    }
	 
	    return $qty;
	}
	 
	/**
	 * Saves the Shipment changes in the Order
	 *
	 * @param $shipment Mage_Sales_Model_Order_Shipment
	 * @param $order Mage_Sales_Model_Order
	 * @param $customerEmailComments string
	 */
	protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments)
	{
	    $shipment->getOrder()->setIsInProcess(true);
	    $transactionSave = Mage::getModel('core/resource_transaction')
	                           ->addObject($shipment)
	                           ->addObject($order)
	                           ->save();
	 
	    $emailSentStatus = $shipment->getData('email_sent');
	    
	        $shipment->sendEmail(true, $customerEmailComments);
	        $shipment->setEmailSent(true);
	    
	 
	    return $this;
	}
	 
	/**
	 * Saves the Order, to complete the full life-cycle of the Order
	 * Order status will now show as Complete
	 *
	 * @param $order Mage_Sales_Model_Order
	 */
	protected function _saveOrder(Mage_Sales_Model_Order $order, $customerEmailComments)
	{
	    $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
	    $order->setData('status', $configValue['order_status_holded']);
	    $configValue = Mage::getStoreConfig('shippingcode/config');
		$order->setStatus($configValue['order_status_complete']);
		$order->getState('Enviado para Expedição');
	 	$history = $order->addStatusHistoryComment($customerEmailComments, true);
        $history->setIsCustomerNotified(true);
	    $order->save();
	    
        
	 
	    return $this;
	}

	public function _getShippingMethod($shipping_code){
		//intelipost_3 - e-sedex
		//intelipost_2 - sedex
		//intelipost_1 - pac
		// correios
		/*
		Código Serviço
			40010 SEDEX Varejo
			40045 SEDEX a Cobrar Varejo
			40215 SEDEX 10 Varejo
			40290 SEDEX Hoje Varejo
			41106 PAC Varejo
		*/

		if($shipping_code == "intelipost_1")
			return 41106;
		elseif ($shipping_code == "intelipost_2") 
			return 40010;
		elseif ($shipping_code == "intelipost_3") 
			return 40010;
		elseif($shipping_code == "freeshipping_freeshipping")
			return false;
		else
			return false;
		
	}
}
