<?php
class O2TI_Moip_Model_Standard extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'o2ti_moip_standard';
    protected $_formBlockType = 'moip/standard_form';
    protected $_infoBlockType = 'moip/info';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_allowCurrencyCode = array('BRL');
    /**
     * Armazena as informações passadas via formulário no frontend
     * @access public
     * @param array $data
     * @return O2TI_Moip_Model_Standard
     */
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setFormaPagamento($data->getFormaPagamento());
        $info->setDebitoInstituicao($data->getDebitoInstituicao());
        $info->setCreditoInstituicao($data->getCreditoInstituicao());
        $info->setCreditoNumero($data->getCreditoNumero());
        $info->setCreditoExpiracaoMes($data->getCreditoExpiracaoMes());
        $info->setCreditoExpiracaoAno($data->getCreditoExpiracaoAno());
        $info->setCreditoCodigoSeguranca($data->getCreditoCodigoSeguranca());
        $info->setCreditoParcelamento($data->getCreditoParcelamento());
        $info->setCreditoPortadorNome($data->getCreditoPortadorNome());
        $info->setCreditoPortadorCpf($data->getCreditoPortadorCpf());
        $info->setCreditoPortadorTelefone($data->getCreditoPortadorTelefone());
        $info->setCreditoPortadorNascimento($data->getCreditoPortadorNascimento());
        return $this;
    }

    public function getPayment() {
        return $this->getQuote()->getPayment();
    }
    public function getSession() {
        return Mage::getSingleton('moip/session');
    }
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }
    public function getPaymentMethods() {
        return $this->getConfigData('formas_pagamento');
    }
    public function getQuote() {
        return $this->getCheckout()->getQuote();
    }
    public function validate() {
        parent::validate();
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        if (!in_array($currency_code, $this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('moip')->__('Selected currency code (' . $currency_code . ') is not compatabile with Moip'));
        }
        return $this;
    }
    public function prepare() {
        $info = $this->getInfoInstance();
		
        $pgtoArray = array();
        $pgtoArray['forma_pagamento'] = $info->getFormaPagamento();
        $pgtoArray['debito_instituicao'] = $info->getDebitoInstituicao();
        $pgtoArray['pagamento_direto'] = $this->getConfigData('pagamento_direto');
        $pgtoArray['tipoderecebimento'] = $this->getConfigData('tipoderecebimento');
        $pgtoArray['parcelamento'] = $this->getConfigData('parcelamento');
        $pgtoArray['nummaxparcelamax'] = $this->getConfigData('nummaxparcelamax');                
        $pgtoArray['comissionamento'] = $this->getConfigData('comissionamento');
        $pgtoArray['logincomissionamento'] = $this->getConfigData('logincomissionamento');
        $pgtoArray['porc_comissionamento'] = $this->getConfigData('porc_comissionamento');
        $pgtoArray['pagadordataxa'] = $this->getConfigData('pagadordataxa');
        $pgtoArray['conta_moip'] = $this->getConfigData('conta_moip');
        $pgtoArray['apelido'] = $this->getConfigData('apelido');
        $pgtoArray['credito_instituicao'] = $info->getCreditoInstituicao();
        $pgtoArray['credito_numero'] = $info->getCreditoNumero();
        $pgtoArray['credito_expiracao_mes'] = $info->getCreditoExpiracaoMes();
        $pgtoArray['credito_expiracao_ano'] = $info->getCreditoExpiracaoAno();
        $pgtoArray['credito_codigo_seguranca'] = $info->getCreditoCodigoSeguranca();
        $pgtoArray['credito_parcelamento'] = $info->getCreditoParcelamento();
        $pgtoArray['credito_portador_nome'] = $info->getCreditoPortadorNome();
        $pgtoArray['credito_portador_cpf'] = $info->getCreditoPortadorCpf();
        $pgtoArray['credito_portador_telefone'] = $info->getCreditoPortadorTelefone();
        $pgtoArray['credito_portador_nascimento'] = $info->getCreditoPortadorNascimento();
        $api = Mage::getModel('moip/api');
        $api->setAmbiente($this->getConfigData('ambiente'));
        $session = Mage::getSingleton('checkout/session');
        $session->setPgtoArray($pgtoArray);
        $session->setMoIPFields($this->getStandardCheckoutFormFields());
    }
    public function getOrderPlaceRedirectUrl() {
        $this->prepare();
        return Mage::getUrl('moip/standard/redirect', array('_secure' => true));
    }
    public function getFormasPagamento() {
        $formas = $this->getConfigData('formas_pagamento');
        $formas = explode(",", $formas);
        return $formas;
    }
    public function getStandardCheckoutFormFields() {
        if ($this->getQuote()->getIsVirtual()) {
            $a = $this->getQuote()->getBillingAddress();
            $b = $this->getQuote()->getShippingAddress();
        } else {
            $a = $this->getQuote()->getShippingAddress();
            $b = $this->getQuote()->getBillingAddress();
        };
		$ss=Mage::getSingleton('customer/session')->getCustomer()->getEmail();		
		if(!empty($ss)){
			$email=Mage::getSingleton('customer/session')->getCustomer()->getEmail();
		}elseif($b->getEmail() != "n/a@na.na")
		$email= $b->getEmail();
		elseif($a->getEmail() != "n/a@na.na")
		$email= $a->getEmail();
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        $totalArr = $a->getTotals();
        $cep = substr(preg_replace("/[^0-9]/", "", $a->getPostcode()) . '00000000', 0, 8);
        $amount = $a->getGrandTotal();
        $valor = $amount;
        $dob = Mage::app()->getLocale()->date($this->getQuote()->getCustomerDob(), null, null, false)->toString('ddMMyyyy');
        $taxvat = $this->getQuote()->getCustomerTaxvat();
        $website_id = Mage::app()->getWebsite()->getId();
        $website_name = Mage::app()->getWebsite()->getName();
        $store_name = Mage::app()->getStore()->getName();
        $Arr = array(
            'id_carteira' => $this->getConfigData('conta_moip'),
            'valor' => $valor,
            'nome' => 'Pagamento a ' . $website_name,
            'descricao' => $this->getListaProdutos(),
            'id_transacao' => $this->getCheckout()->getLastRealOrderId(),
            'peso_compra' => $this->getPesoProdutosPedido(),
            'pagador_nome' => $a->getFirstname() . ' ' . $a->getLastname(),
            //'pagador_email' => Mage::getSingleton('customer/session')->getCustomer()->getEmail(),// this is original code of Moip
			'pagador_email' => strtolower($email),
            'pagador_telefone' => $this->getNumberOrDDD($a->getTelephone(), true) . '' . $this->getNumberOrDDD($a->getTelephone()),
            'pagador_logradouro' => $a->getStreet(1),
            'pagador_numero' => $this->getNumEndereco($a->getStreet(1)),
            'pagador_complemento' => $a->getStreet(2),
            'pagador_bairro' => $a->getStreet(4),
            'pagador_cep' => $cep,
            'pagador_cidade' => $a->getCity(),
            'pagador_estado' => strtoupper($a->getRegionCode()),
            'pagador_pais' => $a->getCountry(),
            'pagador_cpf' => $taxvat,
            'pagador_celular' => $this->getNumberOrDDD($a->getFax(), true) . '' . $this->getNumberOrDDD($a->getFax()),
            'pagador_sexo' => '',
            'pagador_data_nascimento' => $dob,
        );
        return $Arr;
    }
    public function getInfoParcelamento() {
        $config = array();
        $max = 12;
        $config['de1'] = (int) $this->getConfigData('parcelamento_de1');
        $config['ate1'] = (int) $this->getConfigData('parcelamento_ate1');
        $config['juros1'] = $this->getConfigData('parcelamento_juros1');
        $config['de2'] =  $this->getConfigData('parcelamento_de2');
        $config['ate2'] = (int) $this->getConfigData('parcelamento_ate2');
        $config['juros2'] = $this->getConfigData('parcelamento_juros2');
        $config['de3'] = (int) $this->getConfigData('parcelamento_de3');
        $config['ate3'] = (int) $this->getConfigData('parcelamento_ate3');
        $config['juros3'] = $this->getConfigData('parcelamento_juros3');
        $config['vcmentoboleto'] = $this->getConfigData('vcmentoboleto');
        if ($config['ate1'] > $max)
            $config['ate1'] = $max;
        if ($config['ate2'] > $max)
            $config['ate2'] = $max;
        if ($config['ate3'] > $max)
            $config['ate3'] = $max;
        return $config;
    }
    private function getNumEndereco($endereco) {
        $numEndereco = '';

        $posSeparador = $this->getPosSeparador($endereco, false);
        if ($posSeparador !== false)
            $numEndereco = trim(substr($endereco, $posSeparador + 1));

        $posComplemento = $this->getPosSeparador($numEndereco, true);
        if ($posComplemento !== false)
            $numEndereco = trim(substr($numEndereco, 0, $posComplemento));

        return($numEndereco);
    }
    function getPosSeparador($endereco, $procuraEspaco = false) {
        $posSeparador = strpos($endereco, ',');
        if ($posSeparador === false)
            $posSeparador = strpos($endereco, '-');

        if ($procuraEspaco)
            if ($posSeparador === false)
                $posSeparador = strrpos($endereco, ' ');

        return($posSeparador);
    }

    function getNumberOrDDD($param_telefone, $param_ddd = false) {

        $cust_ddd = '00';
        $cust_telephone = preg_replace("/[^0-9]/", "", $param_telefone);
        $st = strlen($cust_telephone) - 8;
        if ($st > 0) { //No caso essa seqüência é mais de 8 caracteres
            $cust_ddd = substr($cust_telephone, 0, 2);
            $cust_telephone = substr($cust_telephone, $st, 8);
        }

        if ($param_ddd === false) {
            $retorno = $cust_telephone;
        } else {
            $retorno = $cust_ddd;
        }

        return $retorno;
    }
    function getListaProdutos() {
        $items = $this->getQuote()->getAllVisibleItems();
        $lista = array();

        foreach ($items as $item) {
            $valor_item = ($item->getBaseCalculationPrice() - $item->getBaseDiscountAmount());
            $valor_item = Mage::helper('core')->currency($valor_item, true, false);

            $lista[] = $item->getName() . ' - ' . (int) $item->getQty() . ' - ' . $valor_item;
        }
        if ($this->getQuote()->getIsVirtual()) {
            $a = $this->getQuote()->getBillingAddress();
            $b = $this->getQuote()->getShippingAddress();
        } else {
            $a = $this->getQuote()->getShippingAddress();
            $b = $this->getQuote()->getBillingAddress();
        }
        $totalArr = $a->getTotals();
        if ($totalArr['shipping']) {
            $valor_frete = $totalArr['shipping']->getValue();
            $lista[] = 'Frete: R$ ' . number_format($valor_frete, 2, ",", ".");
        }
        return $lista;
    }
    function getPesoProdutosPedido() {
        $items = $this->getQuote()->getAllVisibleItems();
        if ($items) {
            $item_peso = 0;
            foreach ($items as $item) {
                $item_peso = $item_peso + round($item->getWeight());
            }
        }
        return $item_peso;
    }
}

