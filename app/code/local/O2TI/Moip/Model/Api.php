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

        $standard = Mage::getSingleton('moip/standard');
        $parcelamento = $standard->getInfoParcelamento();
        $meio = $pgto["forma_pagamento"];
        $vcmentoboleto = $pgto["vcmentoboleto"];
        $forma_pgto = "";
        $validacao_nasp = $standard->getConfigData('validador_retorno');
        $url_retorno =  Mage::getBaseUrl('web', true)."/Moip/standard/success/validacao/".$validacao_nasp."/";
        $valorcompra = $data['valor'];
        $vcmentoboleto = $standard->getConfigData('vcmentoboleto');
        $vcmento = date('c', strtotime("+" . $vcmentoboleto . " days"));
        $forma_boleto = array('DataVencimento' => $vcmento);
        if($pgto['tipoderecebimento'] =="0"):
          $tipoderecebimento = "Parcelado";
        else:
           $tipoderecebimento = ""; 
        endif;
        $parcelamento = $standard->getInfoParcelamento();
        $max_parcelas = $parcelamento['ate1'];
        $min_parcelas = $parcelamento['de1'];
        $juros = $parcelamento['juros1'];
        if($max_parcelas == 12){
          $pacelamento_xml = array(
                              'Parcelamento' => array(
                                      'MinimoParcelas' => $min_parcelas,
                                      'MaximoParcelas' => $max_parcelas,
                                      'Juros' => $juros,
                              ),
                            );
        } else{
          $pacelamento_xml = array(
                'Parcelamento' => array(
                                    'MinimoParcelas' => $min_parcelas,
                                    'MaximoParcelas' => $max_parcelas,
                                    'Juros' => $juros,
                              ),
                'Parcelamento' => array(
                                    'MinimoParcelas' => $max_parcelas+1,
                                    'MaximoParcelas' => '12',
                                    'Juros' => '1.99',
                              ),
                            );
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
        $id_proprio = $alterapedido.$pgto['conta_moip'].'_'.$data['id_transacao'];
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

    public function getParcelamento($valor) {
        $standard = Mage::getSingleton('moip/standard');
        $parcelamento = $standard->getInfoParcelamento();
        $result = array();
        if ($parcelamento['ate1'] >= 1) {
            $result1 = $this->getJurosComposto($valor, $parcelamento['juros1'], $parcelamento['ate1']);
            foreach ($result1 as $k => $v) {
                if ($k >= $parcelamento['de1'])
                    $result[$k] = $v;
            }
        }
        if ($parcelamento['ate1'] < 13) {
            $result1 = $this->getJurosComposto($valor, "1.99", "12");
            foreach ($result1 as $k => $v) {
                if ($k > $parcelamento['ate1'])
                    $result[$k] = $v;
            }
        }
        return $result;
    }

    public function getJurosComposto($valor, $juros, $parcelasR) {
        $parc1 = "";
        $parc = "";
        $juros = $juros / 100;
        $valParcela = $valor * pow((1 + $juros), $parcelasR);
        $valParcela = $valParcela / $parcelasR;
        if ($juros <= 0 || empty($juros)):
            $valor = $valor;
        else:
            $valor = (($valor / 100) * $juros) + $valor;
        endif;
        $j = '';
        $splitss = (int) ($valor / 5);
        if ($splitss <= 12):
            $div = $splitss;
        else:
            $div = 12;
        endif;
        if ($valor <= 5):
            $div = 1;
        endif;
        $standard = Mage::getModel('moip/standard');
        $nummaxparcelamax = $standard->getConfigData('nummaxparcelamax');
        $valorminimo = $standard->getConfigData('valorminimoparcela');
        $parc1 .= '<ValorDaParcela Total="' . number_format($valor, 2) . '" Juros="0" Valor="' . number_format($valor, 2) . '">1</ValorDaParcela>';
        for ($j = 2; $j <= $parcelasR; $j++) {
            $cf = pow((1 + $juros), $j);
            $cf = (1 / $cf);
            $cf = (1 - $cf);
            if($cf > 0)
            $cf = ($juros / $cf);
            $parcelas = ($valor * $cf);
            $parcelas = $parcelas;
            $valors = $valor;
            if ($juros != 0 && $parcelas >= $valorminimo && $j <= $nummaxparcelamax):
                $parc .= '<ValorDaParcela Total="' . number_format(($parcelas * $j), 2) . '" Juros="' . ($juros * 100) . '" Valor="' . number_format($parcelas, 2) . '">' . $j . '</ValorDaParcela>';
            endif;

            if ($juros == 0 && $j <= $parcelasR && ($valor / $j) >= $valorminimo):
                $parc .= '<ValorDaParcela Total="' . number_format($valor, 2) . '" Juros="0" Valor="' . number_format(($valor / $j), 2) . '">' . $j . '</ValorDaParcela>';
            endif;
        }
        $responseMoIP = '<ns1:ChecarValoresParcelamentoResponse xmlns:ns1="http://www.moip.com.br/ws/alpha/">
		<Resposta>
		' . $parc1 . '
		' . $parc . '
		</Resposta>
		</ns1:ChecarValoresParcelamentoResponse>';
        $res = simplexml_load_string($responseMoIP);
        $result = array();
        $i = 1;
        foreach ($res as $resposta) {
            foreach ($resposta as $data) {
                if ($data->getName() == "ValorDaParcela") {
                    $result[$i]['total'] = $data->attributes()->Total;
                    $result[$i]['valor'] = $data->attributes()->Valor;
                    $result[$i]['juros'] = $data->attributes()->Juros;
                    $i++;
                }
            }
        }
        return $result;
    }

}
