<?php
/**
 * MoIP - Moip Payment Module
 *
 * @title      Magento -> Custom Payment Module for Moip (Brazil)
 * @category   Payment Gateway
 * @package    O2TI_Moip
 * @author     MoIP Pagamentos S/a
 * @copyright  Copyright (c) 2013 O2ti Soluções Web
 * @license    Licença válida por tempo indeterminado
 */
$installer = $this;
$installer->startSetup();


$installer->run("
CREATE TABLE IF NOT EXISTS `moip` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `realorder_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `xml_sent` text NOT NULL,
  `xml_return` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `formapg` varchar(60) NOT NULL,
  `bandeira` varchar(60) NOT NULL,
  `digito` varchar(60) NOT NULL,
  `vencimento` datetime NOT NULL,	
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

$installer->startSetup();

$installer->endSetup();
?>
