<?php
class MOIP_Account_Block_Adminhtml_System_Config_Form_Button_Button 

    extends Mage_Adminhtml_Block_System_Config_Form_Field implements Varien_Data_Form_Element_Renderer_Interface
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MOIP/account/system/config/button/button.phtml');
    }
 
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_moipaccount/check');
    }
 
    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'moipaccount_button',
            'label'     => $this->helper('adminhtml')->__('Criar Conta Moip'),
            'onclick'   => 'javascript:check(); return false;'
        ));
 
        return $button->toHtml();
    }

     public function render(Varien_Data_Form_Element_Abstract $element)
    {

         $login = Mage::getStoreConfig('account/config/moip_login',Mage::app()->getStore()); 
         $email = Mage::getStoreConfig('account/config/moip_email',Mage::app()->getStore()); 
         $person_email = Mage::getStoreConfig('account/config/email',Mage::app()->getStore()); 
         $id = Mage::getStoreConfig('account/config/moip_id',Mage::app()->getStore()); 
         $conta_criada = Mage::getStoreConfig('account/config/conta_configurada',Mage::app()->getStore()); 
         if($conta_criada == 0){
            $this->_element = $element;
            return $this->toHtml();
         } else {
          $useContainerId = $element->getData('use_container_id');
            return sprintf(
                '<tr class="system-fieldset-sub-head" id="row_%s">
                    <td colspan="5" style="max-width:580px;">
                        <h4 id="%s">Sua conta está configurada</h4>
                        <p class="subheading-note">Seu Login é: <span  style="font-size:11px;font-style:italic;color:#999;">%s</span></p>
                        <p class="subheading-note">Seu Email da conta é: <span  style="font-size:11px;font-style:italic;color:#999;">%s</span></p>
                        <p class="subheading-note">Seu ID Moip é: <span  style="font-size:11px;font-style:italic;color:#999;">%s</span></p>
                    </td>
                </tr>',
                $element->getHtmlId(), $element->getHtmlId(), $login, $email, $id
            );  
         }
        
        
          
        

    
    }


    public function getValueConfig($value){
        $configValue = Mage::getStoreConfig(
                   'account/config/'.$value,
                   Mage::app()->getStore()
               ); 
        return $configValue;
    }

   
}
?>