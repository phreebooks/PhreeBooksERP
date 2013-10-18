<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008, 2009, 2010, 2011, 2012 PhreeSoft, LLC       |
// | http://www.PhreeSoft.com                                        |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/shipping/language/en_us/language.php
//

// Headings 
define('HEADING_TITLE_MODULES_SHIPPING','Serviços Entrega');
define('SHIPPING_HEADING_SHIP_MGR','Módulo Gerenciamento Entrega');
define('TEXT_SHIPPING_MODULES_AVAILABLE','Métodos Entrega Disponíveis');
// General Defines
define('TEXT_PRODUCTION','Produção');
define('TEXT_TEST','Teste');
define('TEXT_PDF','PDF');
define('TEXT_GIF','GIF');
define('TEXT_THERMAL','Térmica');
define('TEXT_PAGKAGE_DEFAULTS','Padrão Embalagem');
define('TEXT_SHIPMENT_DEFAULTS','Padrão Entrega');
define('TEXT_SHIPMENTS_ON','Entregas em: ');
define('TEXT_REMOVE_MESSAGE','Tem certeza de que quer remover este método de entrega?');
define('SHIPPING_BUTTON_CREATE_LOG_ENTRY','Criar uma Entrada de EntregaCreate a Shipment Entry');
define('SHIPPING_SET_BY_SYSTEM',' (Especificado pelo Sistema)');
define('SHIPPING_POPUP_WINDOW_TITLE','Estimativa Taxa Entrega');
define('SHIPPING_POPUP_WINDOW_RATE_TITLE','Estimativa Entrega - Taxas');
define('SHIPPING_ESTIMATOR_OPTIONS','Estimativa Entrega - Opções Entrega');
define('SHIPPING_TEXT_SHIPPER','Transportador:');
define('SHIPPING_TEXT_SHIPMENT_DATE','Data Entrega');
define('SHIPPING_TEXT_SHIP_FROM_CITY','Entrega da Cidade: ');
define('SHIPPING_TEXT_SHIP_TO_CITY','Entrega para Cidade: ');
define('SHIPPING_RESIDENTIAL_ADDRESS','Endereço Residencial');
define('SHIPPING_TEXT_SHIP_FROM_STATE','Entrega da UF: ');
define('SHIPPING_TEXT_SHIP_TO_STATE','Entrega para UF: ');
define('SHIPPING_TEXT_SHIP_FROM_ZIP','Entrega do CEP: ');
define('SHIPPING_TEXT_SHIP_TO_ZIP','Entrega para CEP: ');
define('SHIPPING_TEXT_SHIP_FROM_COUNTRY','Entrega do País: ');
define('SHIPPING_TEXT_SHIP_TO_COUNTRY','Entrega para País: ');
define('SHIPPING_TEXT_PACKAGE_INFORMATION','Informação Embalagem');
define('SHIPPING_TEXT_PACKAGE_TYPE','Tipo de Embalagem ');
define('SHIPPING_TEXT_PICKUP_SERVICE','Serviço Coleta ');
define('SHIPPING_TEXT_DIMENSIONS','Dimensões: ');
define('SHIPPING_ADDITIONAL_HANDLING','Manuseio Adicional Necessário (Excesso Tamanho)');
define('SHIPPING_INSURANCE_AMOUNT','Seguro: Valor ');
define('SHIPPING_SPLIT_LARGE_SHIPMENTS','Dividir entregas grandes entre pequenos transportadores ');
define('SHIPPING_TEXT_PER_BOX',' por caixa');
define('SHIPPING_TEXT_DELIVERY_CONFIRM','Confirmação Entrega ');
define('SHIPPING_SPECIAL_OPTIONS','Opções Especiais');
define('SHIPPING_SERVICE_TYPE','Tipo Serviço');
define('SHIPPING_HANDLING_CHARGE','Taxar Manuseio: Valor ');
define('SHIPPING_COD_AMOUNT','COD: Coletar ');
define('SHIPPING_SATURDAY_PICKUP','Coleta Sábado');
define('SHIPPING_SATURDAY_DELIVERY','Entrega Sábado');
define('SHIPPING_HAZARDOUS_MATERIALS','Material Perigoso');
define('SHIPPING_TEXT_DRY_ICE','Gelo Seco');
define('SHIPPING_TEXT_RETURN_SERVICES','Serviços Retorno ');
define('SHIPPING_TEXT_METHODS','Métodos Entrega');
define('SHIPPING_TOTAL_WEIGHT','Peso Total Entrega');
define('SHIPPING_TOTAL_VALUE','Valor Total Entrega');
define('SHIPPING_EMAIL_SENDER','E-mail Remetente');
define('SHIPPING_EMAIL_RECIPIENT','E-mail Destinatário');
define('SHIPPING_EMAIL_SENDER_ADD','Endereço E-mail Remetente');
define('SHIPPING_EMAIL_RECIPIENT_ADD','Endereço E-mail Destinatário');
define('SHIPPING_TEXT_EXCEPTION','Exceção');
define('SHIPPING_TEXT_DELIVER','Entrega');
define('SHIPPING_BILL_CHARGES_TO','Cobrar encargos de ');
define('SHIPPING_THIRD_PARTY','Recibo/Terceiro Conta #');
define('SHIPPING_THIRD_PARTY_ZIP','Terceiro CEP');
define('SHIPPING_LTL_FREIGHT_CLASS','LTL Classe Frete');
define('SHIPPING_NUM_PIECES','Número Volumes');
define('SHIPPNIG_SUMMARY','Sumário Entrega');
define('SHIPPING_SHIPMENT_DETAILS','Detalhes Entrega');
define('SHIPPING_PACKAGE_DETAILS','Detalhes Embalagem');
define('SHIPPING_VOID_SHIPMENT','Entrega Cancelada');

define('SHIPPING_TEXT_CARRIER','Transportador');
define('SHIPPING_TEXT_SERVICE','Serviço');
define('SHIPPING_TEXT_FREIGHT_QUOTE','Cotação Frete');
define('SHIPPING_TEXT_BOOK_PRICE','Livro Preços');
define('SHIPPING_TEXT_COST','Custo');
define('SHIPPING_TEXT_NOTES','Notas');
define('SHIPPING_TEXT_PRINT_LABEL','Imprimir Etiqueta');
define('SHIPPING_TEXT_CLOSE_DAY','Fechamento Diário');
define('SHIPPING_TEXT_DELETE_LABEL','Remover Entrega');
define('SHIPPING_TEXT_SHIPMENT_ID','ID Entrega');
define('SHIPPING_TEXT_REFERENCE_ID','ID Referência');
define('SHIPPING_TEXT_TRACKING_NUM','Número Rastreio');
define('SHIPPING_TEXT_EXPECTED_DATE','Data Estimada Entrega');
define('SHIPPING_TEXT_ACTUAL_DATE','Data Efetiva Entrega');
define('SHIPPING_TEXT_DOWNLOAD','Baixar Etiqueta Térmica');
define('SHIPPING_THERMAL_INST','<br />TO arquivo é pré-formatado para impressoras de etiqueta térmica. Para iprimnir a etiqueta:<br /><br />
		1. Clique no botão Download para iniciar o download.<br />
		2. Clique em \'Salvar\' no popup de confirmação para salvar o arquivo na sua máquina local.<br />
		3. Enviar o arquivo direto para a impressora. (o arquivo deve ser enviado em formato raw)');
define('SHIPPING_TEXT_NO_LABEL','Não forma encontradas etiquetas!');
define('SHIPPING_NO_PACKAGES','Não há volumes para entrega, ou a quantidade ou o peso estão zerados.');
define('SHIPPING_DELETE_ERROR','Erro - Não é possível remover a entrega, informação insuficiente.');
define('SHIPPING_CANNOT_DELETE','Erro - Não é possível remover entrega cuja etiqueta tenha sido gerada em dias anteriores.');
define('SHIPPING_LABEL_DELETED','Etiqueta Removida');
define('SHIPPING_END_OF_DAY','Fechamento Dia - %s');

define('SHIPPING_ERROR_WEIGHT_ZERO','Peso entrega nãopdoe ser zero.');
define('SHIPPING_DELETE_CONFIRM', 'Tem certeza de que quer remover este volume?');
define('SHIPPING_NO_SHIPMENTS', 'Não há entregas desta transportadora hoje!');
define('SHIPPING_ERROR_CONFIGURATION', '<strong>Erros Configuração Entrega!</strong>');
define('SHIPPING_BAD_QUOTE_DATE','A data da Entrega não pode ser anterior a hoje para uma cotação. A data entrega foi mudada para hoje para propósito de cotação.');
// Audit log messages
define('SHIPPING_LOG_LABEL_PRINTED','Etiqueta Gerada');
// shipping options
define('SHIPPING_1DEAM','1 Com Antecedência a.m.');
define('SHIPPING_1DAM','1 Dia a.m.');
define('SHIPPING_1DPM','1 Dia p.m.');
define('SHIPPING_1DFRT','1 Dia Frete');
define('SHIPPING_2DAM','2 Dia a.m.');
define('SHIPPING_2DPM','2 Dia p.m.');
define('SHIPPING_2DFRT','2 Dia Frete');
define('SHIPPING_3DPM','3 Dia');
define('SHIPPING_3DFRT','3 Dia Frete');
define('SHIPPING_GND','Terrestre');
define('SHIPPING_GDR','Terrestre Residencial');
define('SHIPPING_GNDFRT','Terrestre LTL Frete');
define('SHIPPING_I2DEAM','Internacional Expresso');
define('SHIPPING_I2DAM','Internacional Expresso');
define('SHIPPING_I3D','Internacional Urgente');
define('SHIPPING_IGND','Terrestre (Canada)');

define('SHIPPING_SHIP_PACKAGE','Entregar um Volume');
define('SHIPPING_CREATE_ENTRY','Criar uma Entrada de Entrega');
define('SHIPPING_RECON_BILL','Reconciliar Cobrança');
define('SHIPPING_RECP_INFO','Informação Receptor');
define('SHIPPING_EMAIL_NOTIFY','Email Notificações');
define('SHIPPING_BILL_DETAIL','Detalhes Cobrança');
define('SHIPPING_LTL_FREIGHT','LTL Frete');
define('SHIPPING_LTL_CLASS','Classe Frete');
define('TEXT_TRACK_CONFIRM','Confirmar Entrega');

define('SHIPPING_DAILY','Coleta Diária');
define('SHIPPING_CARRIER','Contador Cliente no Transportador');
define('SHIPPING_ONE_TIME','Solicitar/ùnico Coleta');
define('SHIPPING_ON_CALL','Sobreaviso Aéreo');
define('SHIPPING_RETAIL','Taxas Sugeridas Varejo');
define('SHIPPING_DROP_BOX','Baixar Caixa/Centro');
define('SHIPPING_AIR_SRV','Centro Serviço Aéreo');

define('SHIPPING_TEXT_LBS','lbs');
define('SHIPPING_TEXT_KGS','kgs');

define('SHIPPING_TEXT_IN','in');
define('SHIPPING_TEXT_CM','cm');

define('SHIPPING_ENVENLOPE','Envelope/Carta');
define('SHIPPING_CUST_SUPP','Cliente Fornecido');
define('SHIPPING_TUBE','Tubo');
define('SHIPPING_PAK','Pacote');
define('SHIPPING_BOX','Caixa');
define('SHIPPING_25KG','25kg Caixa');
define('SHIPPING_10KG','10kg Caixa');

define('SHIPPING_CASH','Dinheiro');
define('SHIPPING_CHECK','Cheque');
define('SHIPPING_CASHIERS','Cheque Administrativo');
define('SHIPPING_MO','Ordem Pagamento');
define('SHIPPING_ANY','Qualquer');

define('SHIPPING_NO_CONF','Sem confirmação entrega');
define('SHIPPING_NO_SIG_RQD','Sem assinatura exigida');
define('SHIPPING_SIG_REQ','Assinatura Exigida');
define('SHIPPING_ADULT_SIG','Assinatura Adulto Exigida');

define('SHIPPING_RET_CARRIER','Etiqueta Retorno Transportador');
define('SHIPPING_RET_LOCAL','Imprimir Etiqueta Retorno Local');
define('SHIPPING_RET_MAILS','Transportador Imprime e Envia Etiqueta Retorno');

define('SHIPPING_SENDER','Remetente');
define('SHIPPING_RECEIPIENT','Destinatário');
define('SHIPPING_THIRD_PARTY','Terceiro');
define('SHIPPING_COLLECT','Coleta');
?>