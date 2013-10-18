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
//  Path: /modules/shipping/language/en_us/admin.php
//

// Module information
define('MODULE_SHIPPING_TITLE','Módulo Entregas');
define('MODULE_SHIPPING_DESCRIPTION','O Módulo Entregas é um anexo para configuração de métodos de entrega. Alguns métodos estão incluidos no sistema.');

/************************** (Shipping Defaults) ***********************************************/
define('CD_10_01_DESC', 'Especifica a unidade de medida para todos os volumes. Valores válidos são: quilos, libras');
define('CD_10_02_DESC', 'Moeda padrão para usar nas entregas. Valores válidos são: Reais, US Dollars, Euros');
define('CD_10_03_DESC', 'Unidade medida volume. Valores válidos são: Centímetros, polegadas');
define('CD_10_04_DESC', 'Padrão pacote entrega residencial (unchecked - Comercial, checked - Residencial)');
define('CD_10_05_DESC', 'Tipo padrão pacote entrega');
define('CD_10_06_DESC', 'Tipo padrão de serviço de coleta para seu serviço de entrega');
define('CD_10_07_DESC', 'Dimensões padrão para utilizar em uma entrega normal (nas unidades especificadas acima).');
define('CD_10_10_DESC', 'Taxa adicional de manuseio do pacote');
define('CD_10_14_DESC', 'Seleçãpo de opção de seguro de entrega.');
define('CD_10_20_DESC', 'Pewrmite que entregas gandes possam ser divididas para utilizar serviço de embalagem menor');
define('CD_10_26_DESC', 'Confirmação de Entrega');
define('CD_10_32_DESC', 'Taxa adicional de manuseio');
define('CD_10_38_DESC', 'Habilitar caixa de COD e opções');
define('CD_10_44_DESC', 'Coleta Sábado');
define('CD_10_48_DESC', 'Entrega Sábado');
define('CD_10_52_DESC', 'Material perigoso');
define('CD_10_56_DESC', 'Gelo seco');
define('CD_10_60_DESC', 'Serviços de retorno');

define('NEXT_SHIPMENT_NUM_DESC','Número Próxima Entrega');
define('TEXT_SHIPPING_PREFS','Propriedades Livro Endereços de Entrega');
define('CONTACT_SHIP_FIELD_REQ', 'Exigir ou não o campo: %s ser digitado para um novo endereço de entrega');
define('PB_PF_SHIP_METHOD','Método Entrega');
define('SHIPPING_METHOD','Selecionar Método:');
define('SHIPPING_MONTH','Selecionar Mês:');
define('SHIPPING_YEAR','Selecionar Ano:');
define('SHIPPING_TOOLS_TITLE','Manutenção Arquivo de Etiquetas de Entrega');
define('SHIPPING_TOOLS_CLEAN_LOG_DESC','Esta operação cria um backup de seus arquivos de etiquetas de entrega. Isto vai manter o espaço de armazenagem do servidor baixo e reduzir o tamanho dos arquivos de backup da empresa. Fazer cópias de segurança destes arquivos é recomendável antes de limpar arquivos antigos para preservar o histórico de transações da CG. <br />INFORMAÇÃO: Limpar as etquetas de entrega vai deixar os registros correntes da base de dados do gerenciador de entregas e logs.');

?>