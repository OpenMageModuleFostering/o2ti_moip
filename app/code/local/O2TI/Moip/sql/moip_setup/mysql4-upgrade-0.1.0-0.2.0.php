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

$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');
$statusLabelTable   = $installer->getTable('sales/order_status_label');

$statuses = array(
	array('status' => 'authorized', 'label' => 'Autorizado'),
	array('status' => 'iniciado', 'label' => 'Iniciado'),
	array('status' => 'boleto_impresso', 'label' => 'Boleto Impresso'),
	array('status' => 'concluido', 'label' => 'Concluido')
);
$states = array(
	array('status' => 'authorized', 'state' => 'processing', 'is_default' => 1),
	array('status' => 'boleto_impresso', 'state' => 'holded', 'is_default' => 1),
	array('status' => 'iniciado', 'state' => 'processing', 'is_default' => 1),
	array('status' => 'concluido', 'state' => 'processing', 'is_default' => 1)
);


$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $statuses);
$installer->getConnection()->insertArray($statusStateTable, array('status', 'state', 'is_default'), $states);


$installer = $this;

$installer->startSetup();

$installer->endSetup();
