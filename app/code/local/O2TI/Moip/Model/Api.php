<?php
class O2TI_Moip_Model_Api {

    const TOKEN_TEST = "3UNGHOJCLVXZVOYF85JGILKALZSROU2O";
    const KEY_TEST = "VX2MOP4AEXFQYHBYIWT0GINNVXFZO9TJCKJ6AWDR";
    const TOKEN_PROD = "FEE5P78NA6RZAHBNH3GLMWZFWRE7IU3D";
    const KEY_PROD = "Y8DIATTADUNVOSXKDN0JVDAQ1KU7UPJHEGPM7SBA";

    private $conta_moip = null;

    public function getContaMoip() {
        return $this->conta_moip;
    }

    public function setContaMoip($conta_moip) {
        $this->conta_moip = $conta_moip;
    }

    public function getAmbiente() {
        return $this->ambiente;
    }

    public function setAmbiente($ambiente) {
        $this->ambiente = $ambiente;
    }

    public function generateXml($data, $rootNodeName = 'ENVIARINSTRUCAO', $xml = null) {

        if (ini_get('zend.ze1_compatibility_mode') == 1)
            {
              ini_set ('zend.ze1_compatibility_mode', 0);
            }
        // turn off compatibility mode as simple xml throws a wobbly if you don't.

        if ($xml == null) {
            $xml = new SimpleXmlElement('<?xml version="1.0" encoding="utf-8" ?><EnviarInstrucao></EnviarInstrucao>');
        }

        // loop through the data passed in.
        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $node = $xml->addChild($key);
                $this->generateXml($value, $rootNodeName, $node);
            } else {
                $value = $this->decode($value);
                $xml->addChild($key, $value);
            }
        }
        $return = $this->convert_encoding($xml->asXML(), true);

        return str_ireplace("\n", "", $return);
    }

    private function convert_encoding($text, $post = false) {
        if ($post) {
            return mb_convert_encoding($text, 'UTF-8');
        } else {
            /* No need to convert if its already in utf-8 */
            if ($this->encoding === 'UTF-8') {
                return $text;
            }
            $texto = mb_convert_encoding($text, $this->encoding, 'UTF-8');
            return strtoupper($texto);
        }
    }

    public function decode($string) {

        $find = array('&Aacute;', '&aacute;', '&Acirc;', '&acirc;', '&Agrave;', '&agrave;', '&Aring;', '&aring;', '&Atilde;', '&atilde;', '&Auml;', '&auml;', '&AElig;', '&aelig;', '&Eacute;', '&eacute;', '&Ecirc;', '&ecirc;', '&Egrave;', '&egrave;', '&Euml;', '&euml;', '&ETH;', '&eth;', '&Iacute;', '&iacute;', '&Icirc;', '&icirc;', '&Igrave;', '&igrave;', '&Iuml;', '&iuml;', '&Oacute;', '&oacute;', '&Ocirc;', '&ocirc;', '&Ccedil;', '&Ograve;', '&ccedil;', '&ograve;', '&Oslash;', '&Ntilde;', '&oslash;', '&ntilde;', '&Otilde;', '&otilde;', '&Yacute;', '&Ouml;', '&yacute;', '&ouml;', '&quot;', '&Uacute;', '&lt;', '&uacute;', '&gt;', '&Ucirc;', '&amp;', '&ucirc;', '&Ugrave;', '&reg;', '&ugrave;', '&copy;', '&Oacute;', '&Uuml;', '&THORN;', '&oacute;', '&uuml;', '&thorn;', '&Ocirc;', '&szlig;');
        $replace = array('Á', 'á', 'Â', 'â', 'À', 'à', 'Å', 'å', 'Ã', 'ã', 'Ä', 'ä', 'Æ', 'æ', 'É', 'é', 'Ê', 'ê', 'È', 'è', 'Ë', 'ë', 'Ð', 'ð', 'Í', 'í', 'Î', 'î', 'Ì', 'ì', 'Ï', 'ï', 'Ó', 'ó', 'Ô', 'ô', 'Ç', 'Ò', 'ç', 'ò', 'Ø', 'Ñ', 'ø', 'ñ', 'Õ', 'õ', 'Ý', 'Ö', 'ý', 'ö', '"', 'Ú', '<', 'ú', '>', 'Û', '&', 'û', 'Ù', '®', 'ù', '©', 'Ó', 'Ü', 'Þ', 'ó', 'ü', 'þ', 'Ô', 'ß');

        $decodedstring = str_replace($find, $replace, $string);
        return $decodedstring;
    }

    public function generatePedido($data, $pgto) {
        if($pgto['credito_parcelamento'] == ""){
            $pgto['credito_parcelamento'] = 2;
        }
        $standard = Mage::getSingleton('moip/standard');
        $parcelamento = $standard->getInfoParcelamento();
        $meio = $pgto["forma_pagamento"];
        $vcmentoboleto = $pgto["vcmentoboleto"];
        $forma_pgto = "";
        $validacao_nasp = $standard->getConfigData('validador_retorno');
        $url_retorno =  Mage::getBaseUrl()."Moip/standard/success/validacao/".$validacao_nasp."/";
        $valorcompra = $data['valor'];
        $vcmentoboleto = $standard->getConfigData('vcmentoboleto');
        $vcmento = date('c', strtotime("+" . $vcmentoboleto . " days"));
        $forma_boleto = array('DataVencimento' => $vcmento);
        if($pgto['tipoderecebimento'] =="0"):
          $tipoderecebimento = "Parcelado";
        else:
           $tipoderecebimento = "Avista"; 
        endif;
        $parcelamento = $standard->getInfoParcelamento();
         $tipo_parcelamento = Mage::getSingleton('moip/standard')->getConfigData('jurostipo');
        
        if($tipo_parcelamento == 1){
            
                $max_parcelas = $parcelamento['c_ate1'];
                $min_parcelas = $parcelamento['c_de1'];
                $juros = $parcelamento['c_juros1'];

                if($max_parcelas == 12){
                  $pacelamento_xml = array(
                                      'Parcelamento' => array(
                                              'MinimoParcelas' => $min_parcelas,
                                              'MaximoParcelas' => $max_parcelas,
                                              'Recebimento'=>$tipoderecebimento,
                                              'Juros' => $juros,
                                      ),
                                    );
                } else{
                  $pacelamento_xml = array(
                        'Parcelamento' => array(
                                            'MinimoParcelas' => $min_parcelas,
                                            'MaximoParcelas' => $max_parcelas,
                                            'Recebimento'=>$tipoderecebimento,
                                            'Juros' => $juros,
                                      ),
                        'Parcelamento' => array(
                                            'MinimoParcelas' => $max_parcelas+1,
                                            'MaximoParcelas' => '12',
                                            'Recebimento'=>$tipoderecebimento,
                                            'Juros' => '1.99',
                                      ),
                                    );
                }
        } else {
            $parcelas = array('');
            for ($i=1; $i <= 12; $i++) {
                $juros_parcela = 's_juros'.$i;
                $parcelas[$i] = array('Parcelamento' => array(
                                            'MinimoParcelas' => $i,
                                            'MaximoParcelas' => $i+1,
                                            'Juros' => $parcelamento[$juros_parcela],
                                            'Repassar' => 'true',
                                      ),
                );
                if($i == 12){
                    for ($i=2; $i <= 12; $i++) {
                        $pacelamento_xml[$i] = $parcelas[$pgto['credito_parcelamento']];
                    }
                }

             }
            $pacelamento_xml = end($pacelamento_xml);

        }

        

        if ($meio == "BoletoBancario"):
            if (Mage::getStoreConfig('o2tiall/pagamento_avancado/pagamento_boleto')):
                if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor')):
                    $valorcompra = $data['valor'];
                    $valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc') / 100;
                endif;
                if (Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2') != "" && $valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2') && $valorcompra < Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
                    $valorcompra = $data['valor'];
                    $valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc2') / 100;
                endif;
                if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
                    $valorcompra = $data['valor'];
                    $valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc3') / 100;
                endif;
            endif;
        endif;

        if ($pgto['forma_pagamento'] == "DebitoBancario"):
            $valorcompra = $data['valor'];
            if (Mage::getStoreConfig('o2tiall/pagamento_avancado/transf_desc')):
                if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor')):
                    $valorcompra = $data['valor'];
                    $valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc') / 100;
                endif;
                if (Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2') != "" && $valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2') && $valorcompra < Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
                    $valorcompra = $data['valor'];
                    $valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc2') / 100;
                endif;
                if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
                    $valorcompra = $data['valor'];
                    $valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc3') / 100;
                endif;
            endif;
        endif;

       
            $alterapedido = rand(999999, 99999999);
        
        $recebedor = array(
            'LoginMoIP' => $pgto['conta_moip'],
            'Apelido' => $pgto['apelido'],
        );
        $addresses = array(
            "Logradouro" => $data['pagador_logradouro'],
            "Numero" => $data['pagador_complemento'],
            "Complemento" => $data['pagador_complemento'],
            "Bairro" => $data['pagador_bairro'],
            "Cidade" => $data['pagador_cidade'],
            "Estado" => $data['pagador_estado'],
            "Pais" => 'BRA',
            "CEP" => $data['pagador_cep'],
            "TelefoneFixo" => $data['pagador_ddd'] . $data['pagador_telefone']
        );
        $id_proprio = $pgto['conta_moip'].'_'.$data['id_transacao'];
        $dados = array(
            "Razao" => "Pagamento do pedido #" . $data['id_transacao'],
            "Valores" => array('Valor' => number_format($valorcompra, 2, '.', '')),
            "Recebedor" => $recebedor,
            "IdProprio" => $id_proprio,
            "Pagador" => array(
                "Nome" => $data['pagador_nome'],
                "Email" => $data['pagador_email'],
                "IdPagador" => $data['pagador_email'],
                "EnderecoCobranca" => $addresses,
            ),
            "Parcelamentos" => $pacelamento_xml,
            "Boleto" => $forma_boleto,
            "URLNotificacao" => $url_retorno,
                #"mensagem" => $standard->getListaProdutos(),
        );
        $json = array('InstrucaoUnica' => $dados);
        $xml = $this->generateXml($json);
        return $xml;
    }
    public function generateUrl($token) {
        if ($this->getAmbiente() == "teste")
            $url = $token;
        else
            $url = $token;
        return $url;
    }

    public function getParcelamentoComposto($valor) {
        $standard = Mage::getSingleton('moip/standard');
        $parcelamento = $standard->getInfoParcelamento();
        $parcelas = array();
        $juros = array();
        $primeiro = 1;
        $max_div = $valor/(int)Mage::getSingleton('moip/standard')->getConfigData('valorminimoparcela');

        if($parcelamento['c_ate1'] < $max_div){
            $max_div = $parcelamento['c_ate1'];
        }

            for ($i=1; $i <= $max_div; $i++) {
                if($i > 1){
                    $total_parcelado[$i] = $this->getJurosComposto($valor, $parcelamento['c_juros1'], $i)*$i;
                    $parcelas[$i] = $this->getJurosComposto($valor, $parcelamento['c_juros1'], $i);
                    $juros[$i] = $parcelamento['c_juros1'];
                }
                else {
                    $total_parcelado[$i] =  $valor;
                    $parcelas[$i] = $valor*$i;
                    $juros[$i] = 0;
                }
                if($i <= Mage::getSingleton('moip/standard')->getConfigData('nummaxparcelamax')){
                    $json_parcelas[$i] = array( 
                                                'parcela' => Mage::helper('core')->currency($parcelas[$i], true, false),
                                                'total_parcelado' =>  Mage::helper('core')->currency($total_parcelado[$i], true, false), 
                                                'juros' => $juros[$i]
                                            );
                    $primeiro++;
                }
             }
             if($primeiro < 12 && $primeiro < ($valor/(int)Mage::getSingleton('moip/standard')->getConfigData('valorminimoparcela')) )
             {
                 while ($primeiro <= 12) {
                    $total_parcelado[$primeiro] = number_format($this->getJurosComposto($valor, '1.99', $i)*$primeiro, 2, '.', '');
                    $parcelas[$primeiro] = $this->getJurosComposto($valor, '1.99', $primeiro);
                    $juros[$primeiro] = '1.99';
                    
                    $json_parcelas[$primeiro] = array( 
                                                'parcela' => Mage::helper('core')->currency($parcelas[$primeiro], true, false),
                                                'total_parcelado' =>  Mage::helper('core')->currency($total_parcelado[$primeiro], true, false), 
                                                'juros' => '1.99'
                                            );
                    $primeiro++;
                 }
             }
        $json_parcelas = json_encode($json_parcelas);
        return $json_parcelas;

    }

     public function getParcelamentoSimples($valor) {
        $standard = Mage::getSingleton('moip/standard');
        $parcelamento = $standard->getInfoParcelamento();
        $parcelas = array();
        $juros = array();
        $primeiro = 1;
        $max_div = (int)($valor/Mage::getSingleton('moip/standard')->getConfigData('valorminimoparcela'));
        
        if(Mage::getSingleton('moip/standard')->getConfigData('nummaxparcelamax') > $max_div){
            $max_div = $max_div;
        } else {
            $max_div = Mage::getSingleton('moip/standard')->getConfigData('nummaxparcelamax');
        }

        for ($i=1; $i <= $max_div; $i++) {
                $juros_parcela = 's_juros'.$i;
              
                if($i > 1){
                    $taxa = $parcelamento[$juros_parcela] / 100;
                    $valor_add = $valor * $taxa;
                    $total_parcelado[$i] =  $valor + $valor_add;
                    $parcelas[$i] =  ($valor  + $valor_add)/$i;
                    $juros[$i] = $parcelamento[$juros_parcela];
                }
                else {
                    $total_parcelado[$i] =  $valor;
                    $parcelas[$i] = $valor*$i;
                    $juros[$i] = 0;
                }
                if($i <= Mage::getSingleton('moip/standard')->getConfigData('nummaxparcelamax')){
                    $json_parcelas[$i] = array( 
                                                'parcela' => Mage::helper('core')->currency($parcelas[$i], true, false),
                                                'total_parcelado' =>  Mage::helper('core')->currency($total_parcelado[$i], true, false), 
                                                'juros' => $juros[$i]
                                            );
                     }
             }
        $json_parcelas = json_encode($json_parcelas);
        return $json_parcelas;

    }

    public function getParcelamento($valor) {
      
        $tipo_parcelamento = Mage::getSingleton('moip/standard')->getConfigData('jurostipo');

        if($tipo_parcelamento == 1){
            $tipo = $this->getParcelamentoComposto($valor);
        } else {
            $tipo = $this->getParcelamentoSimples($valor);
        }

        return $tipo;
        
    }

    public function getJurosSimples($valor, $juros, $parcela) {
        
        return $valParcela;
    }

    public function getJurosComposto($valor, $juros, $parcela) {
        $taxa = $juros / 100;
        $valParcela = $valor * pow((1 + $taxa), $parcela);
        $valParcela = $valParcela/$parcela;
        return $valParcela;
    }

}
