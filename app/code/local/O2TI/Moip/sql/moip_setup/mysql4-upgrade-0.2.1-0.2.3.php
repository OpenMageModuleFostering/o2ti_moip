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


$installer->run("ALTER TABLE  `moip` ADD  `num_parcelas` INT NOT NULL AFTER  `datetime` ,
ADD  `ult_dig`  VARCHAR( 30 ) NOT NULL AFTER  `num_parcelas` ,
ADD  `taxa_moip` VARCHAR( 30 ) NOT NULL AFTER  `ult_dig` ,
ADD  `valor_pago` VARCHAR( 30 ) NOT NULL AFTER  `taxa_moip` ,
ADD  `aceito_cofre` INT NOT NULL AFTER  `valor_pago`");

$installer->startSetup();

$installer->endSetup();
?>
