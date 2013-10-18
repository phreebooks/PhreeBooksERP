<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/inventory/language/en_us/language.php
//
//

define('INV_HEADING_NEW_ITEM', 'Novo Item Estoque'); 
define('INV_TYPES_SI','Item Estoque');
define('INV_TYPES_SR','Item de Série');
define('INV_TYPES_MS','Item Mestre');
define('INV_TYPES_AS','Item Montagem');
define('INV_TYPES_SA','Montagem de Série');
define('INV_TYPES_NS','Item Não Estoque');
define('INV_TYPES_LB','Mão Obra');
define('INV_TYPES_SV','Serviço');
define('INV_TYPES_SF','Serviço - Taxa Líquida');
define('INV_TYPES_CI','Item Cobrável');
define('INV_TYPES_AI','Item Atividade');
define('INV_TYPES_DS','Descrição');
define('INV_TYPES_IA','Parte Montagem Item');
define('INV_TYPES_MI','Estoque Mestre sub Item');
define('INV_TEXT_FIFO','FIFO');
define('INV_TEXT_LIFO','LIFO');
define('INV_TEXT_AVERAGE','Médio');
define('INV_TEXT_GREATER_THAN','Maior que');
define('TEXT_DIR_ENTRY','Entrada Direta');
define('TEXT_ITEM_COST','Custo Item');
define('TEXT_RETAIL_PRICE','Preço Varejo');
define('TEXT_PRICE_LVL_1','Nível Preço 1');	
define('TEXT_DEC_AMT','Desconto por Quantidade');
define('TEXT_DEC_PCNT','Desconto por Percentual');
define('TEXT_INC_AMT','Aumento por Quantidade');
define('TEXT_INC_PCNT','Aumento por Percentual');
define('TEXT_NEXT_WHOLE','Próximo Dólar');
define('TEXT_NEXT_FRACTION','Centavos Contantes');
define('TEXT_NEXT_INCREMENT','Próximo Incremento');
define('INV_XFER_SUCCESS','Transferência bem sucedida de %s peças do sku %s');
define('TEXT_INV_MANAGED','Estoque Controlado');
define('INV_DATE_ACCOUNT_CREATION', 'Data Criação');
define('INV_DATE_LAST_UPDATE', 'Última Atualização');
define('INV_DATE_LAST_JOURNAL_DATE', 'Data Última Entrada');
define('INV_SKU_HISTORY','SKU Histórico');
define('INV_OPEN_PO','Ordens Compra Pendentes');
define('INV_OPEN_SO','Ordens Venda Pendentes');
define('INV_PURCH_BY_MONTH','Compras por Mês');
define('INV_SALES_BY_MONTH','Vendas por Mês');
define('INV_NO_RESULTS','Não foram encontrados resultados');
define('INV_PO_NUMBER','Número OCompra');
define('INV_SO_NUMBER','Número OVenda');
define('INV_PO_DATE','Data OCompra');
define('INV_SO_DATE','Data OVenda');
define('INV_PO_RCV_DATE','Data Recebimento');
define('INV_SO_SHIP_DATE','Data Envio');
define('TEXT_REQUIRED_DATE','Data Exigida');
define('INV_PURCH_COST','Custo Compra');
define('INV_SALES_INCOME','Receita Venda');
define('TEXT_MONTH','Este Mês');
define('INV_ENTRY_PURCH_TAX','Taxa Compra Padrão');
define('TEXT_LAST_MONTH','Último Mês');
define('TEXT_LAST_3_MONTH','3 Meses');
define('TEXT_LAST_6_MONTH','6 Meses');
define('TEXT_LAST_12_MONTH','12 Meses');
define('TEXT_WHERE_USED','Utilizado em');
define('TEXT_CURRENT_COST','Custo Corrente Montagem');
define('JS_INV_TEXT_ASSY_COST','O Custo Corrente Montagem deste sku é: ');
define('JS_INV_TEXT_USAGE','Este SKU é utilizado nas seguintes montagens: ');

// Tradução tem um problema neste bloco
// A traução desliga a função de busca de produto e retorno das informações 
// para a tela de Ordem Venda
define('JS_INV_TEXT_USAGE_NONE','This SKU is not used in any assemblies.');

define('INV_HEADING_UPC_CODE','Código UPC');
define('INV_SKU_ACTIVITY','Atividade SKU');
define('INV_ENTRY_INVENTORY_DESC_SALES','Descrição Vendas');
define('INV_ASSY_HEADING_TITLE', 'Inventário Montagem/Desmontagem');
define('TEXT_INVENTORY_REVALUATION', 'Revalorização Inventário');
define('INV_POPUP_WINDOW_TITLE', 'Itens Inventário');
define('INV_POPUP_ADJ_WINDOW_TITLE','Ajustes Inventário');
define('INV_ADJUSTMENT_ACCOUNT','Conta Ajustes');
define('INV_BULK_SKU_ENTRY_TITLE','Entrada de Preços SKU em lote');
define('INV_POPUP_XFER_WINDOW_TITLE','Transferir Estoque entre Locais Armazenagem');
define('INV_HEADING_QTY_ON_HAND', 'Qtd em Mãos');
define('INV_QTY_ON_HAND', 'Quantidade em Mãos');
define('INV_HEADING_SERIAL_NUMBER', 'Número Serial');
define('INV_HEADING_QTY_TO_ASSY', 'Qtd para Montar');
define('INV_HEADING_QTY_ON_ORDER', 'Qtd Encomendada');
define('INV_HEADING_QTY_IN_STOCK', 'Qtd em Estoque');
define('TEXT_QTY_THIS_STORE','Qtd nesta Filial');
define('INV_HEADING_QTY_ON_SO', 'Qtd Vendida');
define('INV_HEADING_QTY_ON_ALLOC', 'Qtd Empenhada');
define('INV_QTY_ON_SALES_ORDER', 'Quantidade em Ordens de Venda');
define('INV_QTY_ON_ALLOCATION', 'Quantitade Empenhada');
define('INV_HEADING_PREFERRED_VENDOR', 'Fornecedor Prioritário');
define('INV_HEADING_LEAD_TIME', 'Giro(dias)');
define('INV_QTY_ON_ORDER', 'Quantidade em Ordens de Compra');
define('INV_ASSY_PARTS_REQUIRED','Componentes necessários para esta montagem');
define('INV_TEXT_REMAINING','Qtd Remanescente');
define('INV_TEXT_UNIT_COST','Custo Unitário');
define('INV_TEXT_CURRENT_VALUE','Valor Atual');
define('INV_TEXT_NEW_VALUE','Novo Valor');
define('INV_ADJ_QUANTITY','Aju. Qtd');
define('INV_REASON_FOR_ADJUSTMENT','Motivo Ajuste');
define('INV_ADJ_VALUE', 'Aju. Valor');
define('INV_ROUNDING', 'Arredondamento');
define('INV_RND_VALUE', 'Valor Arredondamento');
define('INV_BOM','Lista de Materiais');
define('INV_ADJ_DELETE_ALERT', 'Tem certeza de que quer remover este Ajuste de Inventário?');
define('INV_MSG_DELETE_INV_ITEM', 'Tem certeza de que quer remover este Item de Inventário');
define('INV_XFER_FROM_STORE','Transferir do Local ID');
define('INV_XFER_TO_STORE','Para o Local ID');
define('INV_XFER_QTY','Quantidade a Transferir');
define('INV_XFER_ERROR_SAME_STORE_ID','As IDs dos locais de origem e destino são iguais, a transferência não foi realizada!');
define('INV_XFER_ERROR_NOT_ENOUGH_SKU','Transferência do item de inventário %s não foi realizada, não há quantidade suficiente em estoque!');
define('INV_ENTER_SKU','Entre o SKU, tipo de item e método de custo e pressione Continuar<br />Tamanho máximo do SKU %s caracteres (%s para Estoque Mestre)');
define('INV_MS_ATTRIBUTES','Atributos Estoque Mestre');
define('INV_TEXT_ATTRIBUTE_1','Atributo 1');
define('INV_TEXT_ATTRIBUTE_2','Atributo 2');
define('INV_TEXT_ATTRIBUTES','Atributo');
define('INV_MS_CREATED_SKUS','O seguinte SKUserá criado');
define('INV_ENTRY_INVENTORY_TYPE', 'Tipo Inventário');
define('INV_ENTRY_INVENTORY_DESC_SHORT', 'Descrição Curta');
define('INV_ENTRY_INVENTORY_DESC_PURCHASE', 'Descrição Compra');
define('INV_ENTRY_IMAGE_PATH','Caminho Relativo Imagem');
define('INV_ENTRY_SELECT_IMAGE','Selecione Imagem');
define('INV_ENTRY_ACCT_SALES', 'Conta Vendas/Receitas');
define('INV_ENTRY_ACCT_INV', 'Conta Inventário/Salário');
define('INV_ENTRY_ACCT_COS', 'Conta Custo de Vendas');
define('INV_ENTRY_INV_ITEM_COST','Custo Item');
define('INV_ENTRY_FULL_PRICE', 'Preço Cheio');
define('INV_ENTRY_FULL_PRICE_WT', 'Preço Cheio com Imposto');
define('INV_MARGIN','Margem');
define('INV_ENTRY_ITEM_WEIGHT', 'Peso Item');
define('INV_ENTRY_ITEM_MINIMUM_STOCK', 'Estoque Mínimo');
define('INV_ENTRY_ITEM_REORDER_QUANTITY', 'Quantidade Limite Compra');
define('INV_ENTRY_INVENTORY_COST_METHOD', 'Método Custo');
define('INV_ENTRY_INVENTORY_SERIALIZE', 'Serializar Item');
define('INV_MASTER_STOCK_ATTRIB_ID','ID (Max 2 Carac)');
define('TEXT_CUSTOMER_DETAILS','Detalhes Cliente');
define('TEXT_VENDOR_DETAILS','Detalhes Fornecedor');
define('TEXT_ITEM_DETAILS','Detalhes Item');
define('TEXT_ADJ_ITEMS','%s Itens');
define('TEXT_MULTIPLE_ENTRIES','Ajustes Múltiplos');
define('TEXT_TRANSFERS','Transferir');
define('TEXT_FROM_BRANCH','da Filial');
define('TEXT_DEST_BRANCH','para a Filial');
define('TEXT_TRANSFER_REASON','Motivo Transferência');
define('TEXT_TRANSFER_ACCT','Conta Transferência');
define('TEXT_AVERAGE_USAGE','Utilização Média (não inclui omês corrente))');
define('TEXT_PACKAGE_QUANTITY','Qtd Embalagem');
define('INV_MSG_DELETE_VENDOR_ROW','Tem certeza de que quer remover este fornecedor?');
define('INV_MSG_COPY_INTRO', 'Por favor, entre o novo SKU ID de destino para a cópia:');
define('INV_MSG_RENAME_INTRO', 'Por favor, entre o novo SKU ID para renomear este SKU:');
define('INV_ERROR_DUPLICATE_SKU','O novo item de inventário não pode ser criado porque o SKU já está em uso.');
define('INV_ERROR_CANNOT_DELETE','O item de inventário não pode ser excluído porque há lançamentos que o utilizam');
define('INV_ERROR_BAD_SKU','Houve um erro com a lista de montagem do item, por favor, valide os valores de SKU e verifique as quantidades. O SKU falho é: ');
define('INV_ERROR_SKU_INVALID','SKU é inválido. Por favor, verifique o SKU, item de inventário, contas contábeis para informação perdida ou erros.');
define('INV_ERROR_SKU_BLANK','O campo SKU foi deixado em branco. Por favor, entre um valor de SKU e tente novamente.');
define('INV_ERROR_FIELD_BLANK','O nome de campo foi deixado em branco. Por favor, entre o nome do campo e tente novamente.');
define('INV_ERROR_FIELD_DUPLICATE','O nome de campo digitado já existe, por favor, tente outro nome.');
define('INV_ERROR_NEGATIVE_BALANCE','Erro descontruindo inventário, não há quantidade suficiente em mãos para desconstruir a quantidade solicitada!');
define('INV_DESCRIPTION', 'Descrição: ');
define('TEXT_USE_DEFAULT_PRICE_SHEET','Usar Especificações Tabela Preços Padrão');
define('INV_POST_SUCCESS','Ajuste de Inventário Gravado com Sucesso Ref # ');
define('INV_POST_ASSEMBLY_SUCCESS','Montagem com Sucesso SKU: ');
define('INV_NO_PRICE_SHEETS','Não foram definidas tabelas preço!');

// Tradução tem um problema neste bloco
// A traução desliga a função de busca de produto e retorno das informações 
// para a tela de Ordem Venda
define('ORD_INV_STOCK_LOW','Not enough stock on hand of this item.');
define('ORD_INV_STOCK_BAL','The number of units in stock is: ');

define('ORD_INV_OPEN_POS','As seguintes OCs estão abertas no sistema:');
define('ORD_INV_STOCK_STATUS','Local: %s OC: %s Qtd: %s Data: %s');
define('ORD_JS_SKU_NOT_UNIQUE','Não foi possível encontrar resposta única para este SKU. Ou o campo de busca resultou em várias respostas ou nenhuma foi encontrada.');
define('SRVCS_DUPLICATE_SHEET_NAME','Este nome de tabela de preços já existe. Por favor, entre um novo nome de tabela de preços.');
define('INV_ERROR_DELETE_HISTORY_EXISTS','Não é possível remover este item porque há lançamentos na tabela de histórico de inventário.');
define('INV_ERROR_DELETE_ASSEMBLY_PART','Não é possível remover este item porque é parte de uma montagem.');
define('INV_ADJ_QTY_ZERO','Não é possível ajustar inventário com uma quantidade zerada!');
define('INV_MS_ERROR_DELETE_HISTORY_EXISTS','Não é possível remover este SKU %s porque tem registros na tabela de histórico de inventário.');
define('INV_MS_ERROR_DELETE_ASSEMBLY_PART','Não é possível remover este SKU %s porque é parte de uma montagem. Será marcado como inativo.');
define('INV_MS_ERROR_CANNOT_DELETE','O SKU %s não pode ser removido porque há lançamentos. Será marcado como inativo.');

// java script errors and messages
define('AJAX_INV_NO_INFO','Não foi passada informação suficiente para recuperar os detalhes do item');
define('JS_SKU_BLANK', '* O novo item precisa de um SKU ou código UPC\n');
define('JS_COGS_AUTO_CALC','Atenção: Para quantidades negativas, o preço unitário será calculado pelo sistema.');
define('JS_NO_SKU_ENTERED','Um valor de SKU é necessário.\n');
define('JS_ASSY_VALUE_ZERO','Uma quantidade maior que zero de montagem é necessária.\n');
define('JS_NOT_ENOUGH_PARTS','Não há estoque suficiente para montar as quantidades necessárias');
define('JS_MS_INVALID_ENTRY','ID e Descrição são campos obrigatórios. Por favor, digite ambos e pressione OK.');
define('JS_ERROR_NO_SHEET_NAME','O nome da tabela de preço não pode estar vazio.');

// audit log messages
define('INV_LOG_ADJ','Inventário Ajuste - ');
define('INV_LOG_ASSY','Inventário Montagem - ');
define('INV_LOG_FIELDS','Inventário Campos - ');
define('INV_LOG_INVENTORY','Inventário Item - ');
define('INV_LOG_PRICE_MGR','Inventário Preços - ');
define('INV_LOG_TRANSFER','Inventário Transferência de %s para %s');
define('PRICE_SHEETS_LOG','Tabela Preço - ');
define('PRICE_SHEETS_LOG_BULK','Preço Lote - ');

// Price sheets defines
define('PRICE_SHEET_NEW_TITLE','Criar Nova Tabela Preço');
define('PRICE_SHEET_EDIT_TITLE','Alterar Tabela Preço - ');
define('PRICE_SHEET_NAME','Nome Tabela Preço');
define('TEXT_USE_AS_DEFAULT','Usar como Padrão');
define('TEXT_PRICE_SHEETS','Tabelas Preço');
define('TEXT_SALES_PRICE_SHEETS','Tabela Preço Vendas');
define('TEXT_SHEET_NAME','Nome Tabela');
define('TEXT_REVISE','Nova Revisão');
define('TEXT_REVISION','Rev. Nível');
define('TEXT_EFFECTIVE_DATE','Data Inicial');
define('TEXT_EXPIRATION_DATE','Data Expiração');
define('TEXT_BULK_EDIT','Carregar Preços Item');
define('TEXT_SPECIAL_PRICING','Preços Especiais');
define('PRICE_SHEET_MSG_DELETE','Tem certeza de que quer remover esta Tabela de Preço?');
define('PRICE_SHEET_DEFAULT_DELETED','A Tabela de Preço padrão foi removida, por favor, selecione uma nova Tabela de Preço padrão!');
define('TEXT_AVERAGE_USE','Giro Médio (não inclui o mês corrente)');
define('TEXT_MS_HELP','Ao gravar o %s digitado em uma das descrições será substituido pela descrição daquele campo.');
define('JS_MS_COMMA_NOT_ALLOWED','Não é permitido vírgula na descrição.');
define('JS_MS_COLON_NOT_ALLOWED','Não é permitido dois pontos na descrição.');
define('INV_CALCULATING_ERROR', 'Quando Phreebooks tiver que calcular o preço cheio com impostos será = ');
define('INV_WHAT_TO_CALCULATE','Digite 1 para recalcular a margem \n Digite 2 para recalcular o preço de venda');
define('INV_CHEAPER_ELSEWHERE','SKU %s é mais barato em outro lugar.');
define('INV_IMAGE_DUPLICATE_NAME','O nome da imagem já foi utilizado na base de dados. Altere o nome para continuar. ');
?>