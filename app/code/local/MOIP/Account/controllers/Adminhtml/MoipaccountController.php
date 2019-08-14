<?php

class MOIP_Account_Adminhtml_MoipaccountController extends Mage_Adminhtml_Controller_Action
{

    const TOKEN_TEST = "8OKLQFT5XQZXU7CKXX43GPJOMIJPMSMF";
    const ENDPOINT_TEST = "https://sandbox.moip.com.br/v2/";
    const ENDPOINT_PROD = "https://api.moip.com.br/v2/";

    public function checkAction()
    {
        $name = $this->getValueConfig('name');
        $email = $this->getValueConfig('email');
        
        $cpf = $this->getValueConfig('cpf');
        $rg = $this->getValueConfig('rg');
        $birthDate = Mage::app()->getLocale()->date($this->getValueConfig('birthDate'), null, null, false)->toString('Y-MM-dd');
        // $mother = $this->getValueConfig('mother');
        // $father = $this->getValueConfig('father');

        $phone = $this->getValueConfig('phone');

        $phone_ddd = $this->getNumberOrDDD($phone, true);
        $phone_nun = $this->getNumberOrDDD($phone, false);
        $end_rua = $this->getValueConfig('end_rua');
        $end_numero = $this->getValueConfig('end_numero');
        $end_complemento = $this->getValueConfig('end_complemento');
        $end_bairro = $this->getValueConfig('end_bairro');
        $end_cidade = $this->getValueConfig('end_cidade');
        $end_zipcode = $this->getValueConfig('end_zipcode');
        $region_id = strtoupper($this->getValueConfig('region_id'));


        $use_pj = $this->getValueConfig('use_pj');

        
        $pj_name = $this->getValueConfig('pj_name');
        
        $pj_razao = $this->getValueConfig('pj_razao');
        $pj_cnpj = $this->getValueConfig('pj_cnpj');
        
        $pj_fone = $this->getValueConfig('pj_fone');
        $pj_phone_ddd = $this->getNumberOrDDD($pj_fone, true);
        $pj_phone_nun = $this->getNumberOrDDD($pj_fone, false);

        
        $pj_end_rua = $this->getValueConfig('pj_end_rua');
        $pj_end_numero = $this->getValueConfig('pj_end_numero');
        $pj_end_complemento = $this->getValueConfig('pj_end_complemento');
        $pj_end_bairro = $this->getValueConfig('pj_end_bairro');
        $pj_end_cidade = $this->getValueConfig('pj_end_cidade');
        $pj_region_id = $this->getValueConfig('pj_region_id');
        $pj_end_zipcode = strtoupper($this->getValueConfig('pj_end_zipcode'));

        if($use_pj != 1){
            $company =  array( "company" => array(
                                                    "name" => $pj_name,
                                                    "businessName" => $pj_razao,
                                                    "taxDocument"  => array(
                                                            "type"  => "CNPJ",
                                                            "number" =>  $pj_cnpj
                                                        ),
                                                    
                                                    
                                                    "phone" => array(
                                                                        "countryCode"  => "55", 
                                                                        "areaCode"  => $pj_phone_ddd, 
                                                                        "number"  => $pj_phone_nun
                                                                    ),
                                                        
                                                    
                                                    "address"  => array(
                                                        "street"  => $pj_end_rua,
                                                        "streetNumber"  => $pj_end_numero,
                                                        "complement"  => $pj_end_complemento,
                                                        "district"  => $pj_end_bairro,
                                                        "zipcode"  => $pj_end_zipcode,
                                                        "city"  => $pj_end_cidade,
                                                        "state"  => "SP",
                                                        "country" => "BRA"
                                                        )
                                                    )
                                                );
                
        } else {
            $company = null;
        }

        $array = array(
                "email" => array('address' => $email ),
                "person" => array(
                                    'name' => $name,
                                    'taxDocument' => array(
                                                            'type' => "CPF",
                                                            'number' => $cpf 
                                                            ),
                                    'identityDocument' => array(
                                                                   'type' => "RG",
                                                                   'number' => $rg 
                                                                ),
                                    'birthDate' => $birthDate,
                                    // 'parentsName' => array(
                                    //                         'mother' => $mother,
                                    //                         'father' => $father
                                    //                         ),
                                    'phone' => array(
                                                    'countryCode' => '55',
                                                    'areaCode' => $phone_ddd,
                                                    'number' => $phone_nun,
                                                        ),
                                    'address' => array(
                                                        'street' => $end_rua,
                                                        'streetNumber' => $end_numero,
                                                        'complement' => $end_complemento,
                                                        'district' => $end_bairro,
                                                        'zipcode' => $end_zipcode,
                                                        'city' => $end_cidade,
                                                        'state' => 'SP',
                                                        'country' => 'BRA',
                                                         ),
                                    
                                ),
                $company,
            );
        
        $account = $this->createAccount(json_encode($array)); 
        $model = new Mage_Core_Model_Config();
        $store_code =  'default';
        $store_id = Mage::app()->getStore()->getStoreId();
        if(!isset($account->errors)){
            $redirect = $account->_links->setPassword->href;
            $setRedirect['page_redirect'] = '1';
            $setRedirect['url_redirect'] = $redirect;
            $model->saveConfig('account/config/moip_login', $account->login, $store_code, $store_id);
            $model->saveConfig('account/config/moip_id', $account->id, $store_code, $store_id);
            $model->saveConfig('account/config/moip_email', $account->address, $store_code, $store_id);
            $model->saveConfig('account/config/conta_configurada', 1, $store_code, $store_id);
            
            return $this->getResponse()->setBody(json_encode($setRedirect));
        } else {
            $this->getResponse()->setHeader('Content-type', 'application/json');
               
           
            if(isset($account->additionalInfo)){
                $errors['page_redirect'] = '0';
                $model->saveConfig('account/config/moip_login', $account->additionalInfo->account->login, $store_code, $store_id);
                $model->saveConfig('account/config/moip_id', $account->additionalInfo->account->id, $store_code, $store_id);
                $model->saveConfig('account/config/moip_email', $account->additionalInfo->account->email, $store_code, $store_id);
                $model->saveConfig('account/config/conta_configurada', 1, $store_code, $store_id);

                $errors['id'] = $account->additionalInfo->account->id;
                $errors['login'] = $account->additionalInfo->account->login;
                $errors['email'] = $account->additionalInfo->account->email;
            } else {
                 foreach ($account->errors as $key => $value) {
                    $model->saveConfig('account/config/conta_configurada', 0, $store_code, $store_id);
                    $errors['page_redirect'] = '0';
                    $errors = $value->description;
                }
            }
        }

         return $this->getResponse()->setBody(json_encode($errors));
        
    }



    public function getValueConfig($value){
        $configValue = Mage::getStoreConfig(
                   'account/config/'.$value,
                   Mage::app()->getStore()
               ); 
        return $configValue;
    }

    public function createAccount($json)
    {
       
        $url    = self::ENDPOINT_TEST."accounts";
        $Basic  = base64_encode(self::TOKEN_TEST . ":" . self::KEY_TEST);
        $header = "Authorization: OAuth " . self::Oauth_TEST;
        $documento = 'Content-Type: application/json; charset=utf-8';
        $result = array();
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $header,
            $documento
        ));
        curl_setopt($ch, CURLOPT_USERAGENT, 'MoipMagento/2.0.0');
        $responseBody = curl_exec($ch);
        $info_curl = curl_getinfo($ch);
        curl_close($ch);
        $this->generateLog($json, 'MOIP_CreateAccount.log');
        $this->generateLog($responseBody, 'MOIP_CreateAccount.log');
        $this->generateLog($header, 'MOIP_CreateAccount.log');
        $this->generateLog(json_encode($info_curl), 'MOIP_CreateAccount.log');
        
        $decode = json_decode($responseBody);
        return $decode;
    }


    public function generateLog($variable, $name_log){
        $dir_log = Mage::getBaseDir('var').'/log/MOIP/';
        if (!file_exists($dir_log)) {
            mkdir($dir_log, 0755, true);
        }
        Mage::log($variable, null, 'MOIP/'.$name_log, true);
        return;
    }

    public function getNumberOrDDD($param_telefone, $param_ddd = false)
    {
        $cust_ddd       = '11';
        $cust_telephone = preg_replace("/[^0-9]/", "", $param_telefone);
        $st             = strlen($cust_telephone) - 8;
        if ($st > 0) {
            $cust_ddd       = substr($cust_telephone, 0, 2);
            $cust_telephone = substr($cust_telephone, $st, 8);
        }
        if ($param_ddd === false) {
            $retorno = $cust_telephone;
        } else {
            $retorno = $cust_ddd;
        }
        return $retorno;
    }

}

?>