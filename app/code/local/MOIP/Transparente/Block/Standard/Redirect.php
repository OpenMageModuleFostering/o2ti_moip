<?php
class MOIP_Transparente_Block_Standard_Redirect extends Mage_Checkout_Block_Onepage_Success{
	public function __construct(){
		parent::__construct();
	}
	public function getSaveDate($order, $result_decode, $customerData = null, $pgtoArray, $client_array){
		
	if (Mage::getSingleton('transparente/standard')->getConfigData('ambiente') == "teste") { 
	          $url = "https://desenvolvedor.moip.com.br/sandbox/Instrucao.do?token=";
	      }
	          else {
	              $url = "https://www.moip.com.br/Instrucao.do?token=";
	      }
	
		$model_del = Mage::getModel('transparente/write');
		
		$model_del->load($order, 'realorder_id');
		if($model_del->getRealorder_id()){
			Mage::getSingleton('core/session')->addError('A página não pode ser recarregada.');
			Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
			return;
		}
		$model = Mage::getModel('transparente/write');
		$model->setRealorder_id($order);
		$model->setMeio_pg($pgtoArray['forma_pagamento']);
		$model->setCreditcard_parc($pgtoArray['credito_parcelamento']);
		if($pgtoArray['forma_pagamento'] == "BoletoBancario"){
			$brand = "Bradesco";
		} elseif ($pgtoArray['forma_pagamento'] == "DebitoBancario") {
			$brand = $pgtoArray['debito_instituicao'];
		} else {
			$brand = $pgtoArray['credito_instituicao'];
			if($pgtoArray['use_cofre'] != 1){
				$model->setbrand_transparente($brand);
				$model->setCreditcard_parc($pgtoArray['credito_parcelamento']);
				$model->setFirst6(substr($pgtoArray['credito_numero'], 0, 6));
				$model->setLast4(substr($pgtoArray['credito_numero'],-4));
			} else {
				$model->setbrand_transparente($pgtoArray['cofre_brand']);
				$model->setCreditcard_parc($pgtoArray['cofre_parcelamento']);
				$model->setFirst6('****');
				$model->setLast4('****');
			}
			if($pgtoArray['save_cart'] == "on"){
				$model->setAceitaCofre(1);
			} else{
				$model->setAceitaCofre(0);
			}
		}
		$model->setCustomer_id($customerData->getId());
		$model->setUrlcheckout_pg($url.$result_decode['token']);
		$model->setToken($result_decode['token']);
		$model->setStatus_token($result_decode['status']);
		$model->save();
		
		try{
			$order_send = Mage::getModel('sales/order')->load($order, 'increment_id');
			$order_send->sendNewOrderEmail();
			}
		catch (Exception $ex) {  };
		return true;
	}
	
	public function getUrlAmbiente(){
		if (Mage::getSingleton('transparente/standard')->getConfigData('ambiente') == "teste")  
		    $url = "https://desenvolvedor.moip.com.br/sandbox/";   
		else 
		    $url = "https://www.moip.com.br/";
		
		return $url;
	}
	public function getJson($pgtoArray){

		if($pgtoArray['forma_pagamento'] == "BoletoBancario"){
					$json = array(
						'Forma' => $pgtoArray['forma_pagamento'],
					 );
				} elseif ($pgtoArray['forma_pagamento'] == "DebitoBancario") {
					$json = array(
						'Forma' => $pgtoArray['forma_pagamento'],
						'Instituicao' => $pgtoArray['debito_instituicao'],
					 );

				} else {
					if($pgtoArray['use_cofre'] != 1){
					$expiracao = $pgtoArray['credito_expiracao_mes']."/".$pgtoArray['credito_expiracao_ano'];
					$DataNascimento = date('d/m/Y', strtotime($pgtoArray['credito_portador_nascimento']));
						$json = array(
							'Forma' => $pgtoArray['forma_pagamento'],
							'Instituicao' => $pgtoArray['credito_instituicao'],
							'Parcelas' => $pgtoArray['credito_parcelamento'],
							'CartaoCredito' => array(
											'Numero' => $pgtoArray['credito_numero'],
											'Expiracao' => $expiracao,
											'CodigoSeguranca' => $pgtoArray['credito_codigo_seguranca'],
											'Portador' => array(
												'Nome' => $pgtoArray['credito_portador_nome'],
												'DataNascimento' => $DataNascimento,
												'Telefone' => $pgtoArray['credito_portador_DDD'].$pgtoArray['credito_portador_telefone'],
												'Identidade' => $pgtoArray['credito_portador_cpf']),
											 ),
						 );
					} else{
						$json = array(
							'Forma' => $pgtoArray['forma_pagamento'],
							'Instituicao' => $pgtoArray['cofre_brand'],
							'Parcelas' => $pgtoArray['cofre_parcelamento'],
							'CartaoCredito' => array(
												'Cofre' => $pgtoArray['cofre_numero'],
												'CodigoSeguranca' => $pgtoArray['cofre_cvv']
						 					),
							);
					}

				}
	$json = Mage::helper('core')->jsonEncode((object)$json);
	return $json;
		
	}
	public function getOrder_dados($order_dados){
	
		return $order_dados;
	}
	
	public function getErroCartao($result_decode)
	{
		if(array_key_exists('errors', $result_decode)){
			$dados_ErroCartao = array();
			$dados_ErroCartao['errors'] = $result_decode->errors;
			foreach ($result_decode->errors as $key => $value) 
			{
				$dados_ErroCartao['code'] = $value->code;
				$dados_ErroCartao['description'] = $value->description;
			}
			$dados_ErroCartao = Mage::helper('core')->jsonEncode((object)$dados_ErroCartao);
			return $dados_ErroCartao;
		} else{
			return false;
		}
	}
	public function getCartao($result_decode, $pagamento)
	{	
		
			return false;
		
	}
	 public function getTrackingTransparente($order_dados)
    {
    	
    	$order = Mage::getModel('sales/order')->loadByIncrementId($order_dados['id_transacao']);
        $orderIds = $order->getId();       
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseGrandTotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount(),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCity())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getRegion())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCountry()))
            );
            foreach ($order->getAllVisibleItems() as $item) {
            	$cProduct = Mage::getModel('catalog/product'); 
				$cProductId = $cProduct->getIdBySku($item->getSku()); 
				$cProduct->load($cProductId); 
				$category_list = ""; 
				$cats = $cProduct->getCategoryCollection()->exportToArray(); 
				 
				foreach($cats as $cat){ 
				$category_list .= Mage::getModel('catalog/category')->load($cat['entity_id'])->getName()."|"; 
				}
				
				 
				$category_list = rtrim($category_list,"|"); 

                $result[] = sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    $category_list, 
                    $item->getBasePrice(), $item->getQtyOrdered()
                );
            }
            $result[] = "_gaq.push(['_trackTrans']);";
        }
        
        return implode("\n", $result);
    }

	public function getTrans_Transparente($result_decode, $pagamento)
	{	
		
		return true;
	}

	public function getBoleto_Transparente($result_decode)
	{	
			
		return true;
	}
	
	private function getBarcode($valor){
		$fino = 1 ;
		$largo = 3 ;
		$altura = 50 ;
		$barcodes[0] = "00110" ;
		$barcodes[1] = "10001" ;
		$barcodes[2] = "01001" ;
		$barcodes[3] = "11000" ;
		$barcodes[4] = "00101" ;
		$barcodes[5] = "10100" ;
		$barcodes[6] = "01100" ;
		$barcodes[7] = "00011" ;
		$barcodes[8] = "10010" ;
		$barcodes[9] = "01010" ;
		for($f1=9;$f1>=0;$f1--)
		{
			for($f2=9;$f2>=0;$f2--){
				$f = ($f1 * 10) + $f2;
				$texto = "";
				for($i=1;$i<6;$i++){
					$texto .=  substr($barcodes[$f1],($i-1),1) . substr($barcodes[$f2],($i-1),1);
				}
				$barcodes[$f] = $texto;
			}
		}
		$image_p = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'MOIP/transparente/imagem/boleto/p.png';
		$image_b = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'MOIP/transparente/imagem/boleto/b.png';
		$image_boleto = "<img src='{$image_p}' width='{$fino}' height='{$altura}' border='0'>
		<img src='{$image_b}' width='{$fino}' height='{$altura}' border='0'>
		<img src='{$image_p}' width='{$fino}' height='{$altura}' border='0'>
		<img src='{$image_b}' width='{$fino}' height='{$altura}' border='0'>";
		$texto = $valor ;
		if((strlen($texto) % 2) <> 0){
			$texto = "0" . $texto;
		}			
		while (strlen($texto) > 0) {
			$i = round($this->esquerda($texto,2));
			$texto = $this->direita($texto,strlen($texto)-2);
			$f = $barcodes[$i];
			for($i=1;$i<11;$i+=2){
				if(substr($f,($i-1),1) == "0"){
						$f1 = $fino;
				} else {
						$f1 = $largo;
				}
				$image_boleto .= "<img src='{$image_p}' width='{$f1}' height='{$altura}' border='0'>";
				if(substr($f,$i,1) == "0") {
					$f2 = $fino;
				}else{
					$f2 = $largo;
				}
				$image_boleto .= "<img  src='{$image_b}' width='{$f2}' height='{$altura}' border='0'>";
			}
		}
		$image_boleto .= "<img src='{$image_p}' width='{$largo}' height='{$altura}' border='0'>
			<img src='{$image_b}' width='{$fino}' height='{$altura}' border='0'>
			<img src='{$image_p}' width='1' height='{$altura}' border='0'> ";
		return $image_boleto;
	}

	private function esquerda($entra,$comp){
		return substr($entra,0,$comp);
	}
	private function direita($entra,$comp){
		return substr($entra,strlen($entra)-$comp,$comp);
	}
}
