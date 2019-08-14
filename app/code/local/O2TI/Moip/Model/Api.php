<?php
class O2TI_Moip_Model_Api {
	const TOKEN_TEST = "3UNGHOJCLVXZVOYF85JGILKALZSROU2O";
	const KEY_TEST = "VX2MOP4AEXFQYHBYIWT0GINNVXFZO9TJCKJ6AWDR";
	const TOKEN_PROD = "FEE5P78NA6RZAHBNH3GLMWZFWRE7IU3D";
	const KEY_PROD = "Y8DIATTADUNVOSXKDN0JVDAQ1KU7UPJHEGPM7SBA";
    private $ambiente = null;
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
	public function generateformapg($data, $pgto) {
	$formapg[0] = $pgto['forma_pagamento'];
	$formapg[1] = $pgto['debito_instituicao'];
	$formapg[2] = $pgto['credito_instituicao'];
	return $formapg;
	}
	public function generateforma($data, $pgto) {
		$formapgto = "";
		$standard = Mage::getModel('moip/standard');
			if ($pgto['forma_pagamento'] == "DebitoBancario"):
				$formapgto .= "\"Forma\": \"DebitoBancario\",\"Instituicao\":";
				$formapgto .= "\"".$pgto['debito_instituicao']."\"";
		    	endif;

		    	if ($pgto['forma_pagamento'] == "BoletoBancario"):
		   		$formapgto .= "\"Forma\": \"BoletoBancario\"";
		    	endif;	
			if ($pgto['credito_parcelamento'] <> ""):
		            $pgto['credito_parcelamento'] = explode("|", $pgto['credito_parcelamento']);
		            $parcelamento = "\"2\"";                  
		        endif;
			if($pgto['tipoderecebimento'] =="0"):
				$tipoderecebimento = "Parcelado";
			else:
				 $tipoderecebimento = "AVista";	
			endif;            
			if ($pgto['parcelamento'] == "0"):
				$numeropar = "1";
			else:
				$numeropar_a = $pgto['credito_parcelamento'];
				$numeropar = $numeropar_a['0'];
			endif;
			if ($pgto['forma_pagamento'] == "CartaoCredito"):
				$formapgto .= "\"Forma\": \"".$pgto['forma_pagamento']."\",
	       			\"Instituicao\": \"".$pgto['credito_instituicao']."\",
				\"Parcelas\": \"".$numeropar."\",
				\"Recebimento\": \"".$tipoderecebimento."\",
				\"CartaoCredito\": {
					\"Numero\": \"".$pgto['credito_numero']."\",
					\"Expiracao\": \"".$pgto['credito_expiracao_mes'] . '/' . $pgto['credito_expiracao_ano']."\",
					\"CodigoSeguranca\": \"".$pgto['credito_codigo_seguranca']."\",
						\"Portador\": {
							\"Nome\": \"".$pgto['credito_portador_nome']."\",
			  				\"DataNascimento\": \"".$pgto['credito_portador_nascimento']."\",
			 				\"Telefone\": \"".$pgto['credito_portador_telefone']."\",
							\"Identidade\": \"".$pgto['credito_portador_cpf']."\"
		    				}
					}";
			endif;
	return $formapgto;
	}
    public function generateXML($data, $pgto) {
	$comissionamento = "";
	$formapgto = "";
	$xmlvcmeto = "";
	$parcelamento2 = "";
	$standard = Mage::getSingleton('moip/standard'); 
	$parcelamento = $standard->getInfoParcelamento();
	$urldosite = Mage::getBaseUrl('web', true);
	$valorcompra = $data['valor'];
	if($pgto['tipoderecebimento'] =="0"):
		$tipoderecebimento = "<Recebimento>Parcelado</Recebimento>";
	else:
		 $tipoderecebimento = "";	
	endif;
	if ($parcelamento['ate1'] <= 12):
		if ($parcelamento['ate1'] <= 12):
		$parcelamento1 = "<Parcelamento>
					<MinimoParcelas>1</MinimoParcelas>
					<MaximoParcelas>".$parcelamento['ate1']."</MaximoParcelas>
					<Juros>".$parcelamento['juros1']."</Juros>
					".$tipoderecebimento."
				</Parcelamento>";
		$parcldoze = $parcelamento['ate1']+1;
		endif;
		if ($parcldoze <= "12"):
		$parcelamento2 = "<Parcelamento>
					<MinimoParcelas>".$parcldoze."</MinimoParcelas>
					<MaximoParcelas>12</MaximoParcelas>
					<Juros>1.99</Juros>
					".$tipoderecebimento."
				</Parcelamento>";
		endif;
	else: $parcelamento1 = "<Parcelamento><MinimoParcelas>1</MinimoParcelas><MaximoParcelas>12</MaximoParcelas><Juros>1.99</Juros></Parcelamento>";
	endif;
	
	if ($pgto['forma_pagamento'] == "BoletoBancario"):
			$valorcompra = $data['valor'];
			$vcmentoboleto = $standard->getConfigData('vcmentoboleto');
			$vcmeto  = date('c', strtotime("+".$vcmentoboleto." days"));  
			$xmlvcmeto = "<DataVencimento>".$vcmeto."</DataVencimento>
			<Boleto>
			<DiasExpiracao Tipo=\"Corridos\">".$vcmentoboleto."</DiasExpiracao>
			</Boleto>";
			if (Mage::getStoreConfig('o2tiall/pagamento_avancado/pagamento_boleto')):
				if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor')):
				$valorcompra = $data['valor'];
				$valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc')/100;
				endif;
				if (Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2') != "" &&  $valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2')  && $valorcompra < Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
				$valorcompra = $data['valor'];
				$valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc2')/100;
				endif;
				if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
				$valorcompra = $data['valor'];
				$valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc3')/100;
				endif;
			endif;
	endif;
	
	if ($pgto['forma_pagamento'] == "DebitoBancario"):
		$valorcompra = $data['valor'];
		if (Mage::getStoreConfig('o2tiall/pagamento_avancado/transf_desc')):
				if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor')):
				$valorcompra = $data['valor'];
				$valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc')/100;
				endif;
				if (Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2') != "" &&  $valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor2')  && $valorcompra < Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
				$valorcompra = $data['valor'];
				$valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc2')/100;
				endif;
				if ($valorcompra >= Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_valor3')):
				$valorcompra = $data['valor'];
				$valorcompra = $valorcompra - $valorcompra * Mage::getStoreConfig('o2tiall/pagamento_avancado/boleto_desc3')/100;
				endif;
		endif;
	endif;
	if($this->getAmbiente() == "teste"):
	$alterapedido = rand(999,9999);
	else:
	$alterapedido = "";
	endif;
	if(Mage::getStoreConfig('o2tiall/mktplace/comissionamento') == 1):

			if(Mage::getStoreConfig('o2tiall/mktplace/pagadordataxa') == 1):
				$comissionamentotaxa = "<PagadorTaxa>
				<LoginMoIP>".Mage::getStoreConfig('o2tiall/mktplace/logincomissionamento')."</LoginMoIP>
				</PagadorTaxa>";
				if ($pgto['forma_pagamento'] == "CartaoCredito"):
					$comissionamentotaxa .= "<ValorPercentual>".(int)Mage::getStoreConfig('o2tiall/mktplace/porc_comissionamento_cartao').".0</ValorPercentual>";
				endif;
				if ($pgto['forma_pagamento'] == "BoletoBancario"):
					$comissionamentotaxa .= "<ValorPercentual>".(int)Mage::getStoreConfig('o2tiall/mktplace/porc_comissionamento_boleto').".0</ValorPercentual>";
				endif;
				if ($pgto['forma_pagamento'] == "DebitoBancario"):
					$comissionamentotaxa .= "<ValorPercentual>".(int)Mage::getStoreConfig('o2tiall/mktplace/porc_comissionamento_transf').".0</ValorPercentual>";
				endif;
				
			else:
			$comissionamentotaxa = "<ValorPercentual>".(int)Mage::getStoreConfig('o2tiall/mktplace/porc_comissionamento').".0</ValorPercentual>";
			endif;
			$comissionamento = "<Comissoes>
			<Comissionamento>
			<Razao>Pagamento do pedido #".$vcmeto." a ".$pgto['apelido']."</Razao>
			<Comissionado>
			<LoginMoIP>".Mage::getStoreConfig('o2tiall/mktplace/logincomissionamento')."</LoginMoIP>
			</Comissionado>

			

			".$comissionamentotaxa."
			</Comissionamento>
			</Comissoes>";
	endif;
	$valorcompra = number_format((float)$valorcompra, 2, '.', '');
	$rua = utf8_encode($data['pagador_logradouro']);
	$completo = utf8_encode($data['pagador_complemento']);
	$Complemento = utf8_encode($data['pagador_complemento']);
	$Bairro = utf8_encode($data['pagador_bairro']);
	$Cidade = utf8_encode($data['pagador_cidade']);
	$validacao_nasp = $standard->getConfigData('validador_retorno');
	$xml = "<EnviarInstrucao>
	<InstrucaoUnica TipoValidacao=\"Transparente\">
	<Razao>Pagamento do pedido #".$data['id_transacao']." a ". $pgto['apelido'] ."</Razao>
	<Recebedor>
	<LoginMoIP>".$pgto['conta_moip']."</LoginMoIP>
	<Apelido>".$pgto['apelido']."</Apelido>
	</Recebedor>
	".$comissionamento."
	".$xmlvcmeto."
	<Valores>
	<Valor Moeda=\"BRL\">".$valorcompra."</Valor>
	</Valores>
	<IdProprio>".$alterapedido."".$pgto['conta_moip']."".$data['id_transacao']."</IdProprio>
	<Pagador>
	<Nome>".$data['pagador_nome']."</Nome>
	<Email>".$data['pagador_email']."</Email>
	<IdPagador>".$data['pagador_email']."</IdPagador>
	<EnderecoCobranca>
	<Logradouro>".$data['pagador_logradouro']."</Logradouro>
	<Numero>0".$data['pagador_complemento']."</Numero>
	<Complemento>".$data['pagador_complemento']."</Complemento>
	<Bairro>-".$data['pagador_bairro']."</Bairro>
	<Cidade>".$data['pagador_cidade']."</Cidade>
	<Estado>".$data['pagador_estado']."</Estado>
	<Pais>BRA</Pais>
	<CEP>".$data['pagador_cep']."</CEP>
	<TelefoneFixo>".$data['pagador_telefone']."</TelefoneFixo>
	</EnderecoCobranca>
	</Pagador>
	<Parcelamentos>".$parcelamento2."".$parcelamento1."</Parcelamentos>
	<URLNotificacao>".$urldosite."index.php/Moip/standard/success/validacao/".$validacao_nasp."/</URLNotificacao>
	 </InstrucaoUnica>
	 </EnviarInstrucao>
	";
	return utf8_encode($xml);
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
       
    public function getToken($xml) {
	
		if ($this->getAmbiente() == "teste") { 
	    		$url = "https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica";
				$header = "Authorization: Basic " . base64_encode(O2TI_Moip_Model_Api::TOKEN_TEST . ":" . O2TI_Moip_Model_Api::KEY_TEST);
			}
	        else {
	            $url = "https://www.moip.com.br/ws/alpha/EnviarInstrucao/Unica";
				$header = "Authorization: Basic " . base64_encode(O2TI_Moip_Model_Api::TOKEN_PROD . ":" . O2TI_Moip_Model_Api::KEY_PROD);
			}
        $httpClient = new Zend_Http_Client($url);
        $httpClient->setHeaders($header);
        $httpClient->setRawData($xml);
        $responseMoIP = $httpClient->request('POST');       
        $res = simplexml_load_string($responseMoIP->getBody());
        Mage::log($res, null, 'O2TI_Moip.log', true);
        Mage::log($xml, null, 'O2TI_Moip.log', true);
        $pgdireto_status = "";
        $pgdireto_mensagem = "";
        $status = "";
        $moipToken = "";
        $pgdireto_codigoretorno = "";
		$erro = "";
        if ($res) {
			if($res->Resposta->Erro)
				$erro = $res->Resposta->Erro;
            foreach ($res->children() as $child) {
                foreach ($child as $c) {
                    if ($c->getName() == 'Token')
                        $moipToken = $c;

                    if ($c->getName() == "Status")
                        $status = $c;
					
                    foreach ($c as $pgdireto) {
                        if ($pgdireto->getName() == "Status")
                            $pgdireto_status = $pgdireto;

                        if ($pgdireto->getName() == "Mensagem")
                            $pgdireto_mensagem = $pgdireto;

                        if ($pgdireto->getName() == "CodigoRetorno")
                            $pgdireto_codigoretorno = $pgdireto;
                    }
                }
            }
        }

	$result = array();
	$result['erro'] = $erro;   
        
	
        if ($status == "Sucesso") {      
            $result['status'] = $status;
            $result['token'] = $moipToken;
            $result['pgdireto_status'] = $pgdireto_status;
            $result['pgdireto_mensagem'] = $pgdireto_mensagem;
            $result['pgdireto_codigoretorno'] = $pgdireto_codigoretorno;
		
		}
        else {
			$result['status'] = $status;
            $result['token'] = "";
            $result['pgdireto_status'] = "";
            $result['pgdireto_mensagem'] = "";
            $result['pgdireto_codigoretorno'] = "";
		}
        return $result;
    }
    public function generateUrl($token) {
        if ($this->getAmbiente() == "teste")
            $url = $token;
        else
            $url = $token;
        return $url;
    }

  public function generatemeip($formapgto) {
            $meiopg2 = $formapgto;
        return $meiopg2 ;
    }

	public function generatemeiopago($formapg) {
		$standard = Mage::getSingleton('moip/standard'); 
		$vcmentoboleto = $standard->getConfigData('vcmentoboleto');
		$vcmeto  = date('c', strtotime("+".$vcmentoboleto." days"));  
		$meiopgpg['forma_pagamento'] = $formapg[0];
		$meiopgpg['debito_instituicao'] = $formapg[1];
		$meiopgpg['credito_instituicao'] = $formapg[2];
		$meiopgpg['timevencimentoboleto'] = $vcmeto;
		return $meiopgpg;
	    }
	public function getJurosComposto($valor, $juros, $parcelasR) {
		$parc1 = "";
		$parc = "";
		$juros = $juros/100;
		$valParcela = $valor * pow((1 + $juros), $parcelasR);
		$valParcela = $valParcela/$parcelasR;
		if($juros<=0 || empty($juros)):
		$valor = $valor;
		else:
		$valor = (($valor/100)*$juros)+$valor;
		endif;
		$j ='';
		if($valor >= 5){
			$splitss = (int) ($valor/5);
		}
		if($splitss<=12):
		$div = $splitss;
		else:
		$div = 12;
		endif;
		if($valor<=5):
		$div = 1;
		endif;
		$standard = Mage::getModel('moip/standard');
		$nummaxparcelamax = $standard->getConfigData('nummaxparcelamax');
		$valorminimo = $standard->getConfigData('valorminimoparcela');
$parc1 .=  '<ValorDaParcela Total="'.number_format($valor, 2).'" Juros="0" Valor="'.number_format($valor, 2).'">1</ValorDaParcela>';
		for($j=2; $j<=$parcelasR;$j++) {
		$cf = pow((1 + $juros), $j);
		$cf = (1 / $cf);
		$cf = (1 - $cf);
		if($juros > 0){
		$cf = ($juros / $cf);
		}
		else {
			$cf = 1;
		}
		$parcelas = ($valor*$cf);
		$valors = $valor;
		if ($juros != 0 && $parcelas >= $valorminimo && $j <= $nummaxparcelamax ):
		$parc .= '<ValorDaParcela Total="'.number_format(($parcelas * $j), 2).'" Juros="'.($juros * 100).'" Valor="'.number_format($parcelas, 2).'">'.$j.'</ValorDaParcela>';
		endif;
		
		if ($juros == 0 && $j <= $parcelasR  &&  ($valor/$j) >= $valorminimo ):
		$parc .=  '<ValorDaParcela Total="'.number_format($valor, 2).'" Juros="0" Valor="'.number_format(($valor/$j), 2).'">'.$j.'</ValorDaParcela>';
		endif;
		}
		$responseMoIP = '<ns1:ChecarValoresParcelamentoResponse xmlns:ns1="http://www.moip.com.br/ws/alpha/">
		<Resposta>
		'.$parc1.'
		'.$parc.'
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

