<?php
// +-----------------------------------------------------------------+
// Arquivo Tradução Idioma  Phreedom
// Generated: 2013-10-22 05:28:45
// Module/Method: inventory
// ISO Language: pt_br
// Version: 3.6
// +-----------------------------------------------------------------+
// Path: /modules/inventory/language/pt_br/admin.php

define('INV_TOOLS_IN_BALANCE','Seus saldos de inventário estão OK.');
define('INV_TOOLS_STOCK_ROUNDING_ERROR','SKU: %s -> Estoque indica saldo %s mas é menos do que a precisão exigida. Por favor, corrija seus saldos de estoque, o saldo será arredondado para %s.');
define('INV_TOOLS_BALANCE_CORRECTED','SKU: %s -> O saldo de estoque foi alterado para %s.');
define('INV_TOOLS_OUT_OF_BALANCE','SKU: %s -> Estoque indica saldo de %s mas Histórico lista somente %s disponível');
define('INV_TOOLS_BTN_REPAIR','Sinc Qtd em Estoque');
define('TEXT_VERIFY_STOCK_BALANCES','Verificar Saldos Estoque');
define('INV_TOOLS_REPAIR_FIX','Corrigir Saldos Inventário com Histórico');
define('INV_TOOLS_REPAIR_CONFIRM','Tem certeza de que quer corrigir os saldos de estoque para acertar com o Histórico?');
define('INV_TOOLS_REPAIR_TEST','Testar Saldos Inventário com Histórico');
define('INV_TOOLS_VALIDATE_INV_DESC','Esta operação testa para assegurar que a quantidade no inventário listada na tabela de inventário e mostrada nas telas de inventário são iguais às quantidades no histórico de inventário calculadas pelo PhreeBooks quando há movimentos de inventário. Os únicos itens testados são aqueles que são rastreados no cálculo de custo de mercadoria vendida. Corrigir saldos de inventário corrigirá a quantidade em estoque e não alterará o histórico de inventário. ');
define('INV_TOOLS_AUTDIT_LOG_SO_PO','Inv Ferramentas - Corrigir OV/OC Qtd (%s)');
define('INV_TOOLS_VALIDATE_INVENTORY','Validar Quantidade Mostrada Saldo Inventário');
define('INV_TOOLS_SO_PO_RESULT','Finalizado processamento quantidades ordens no Inventário. O número total de itens processados foi %s. O número de registros com erro foi %s.');
define('INV_TOOLS_PO_ERROR','SKU: %s tinha uma quantidade na Ordem de Compra de %s e deveria ser %s. O saldo na tabela de inventário foi corrigido.');
define('INV_TOOLS_SO_ERROR','SKU: %s tinha uma quantidade na Ordem de Venda de %s e deveria ser %s. O saldo na tabela de inventário foi corrigido.');
define('INV_TOOLS_REPAIR_SO_PO','Testar e Corrigir Quantidade Inventário nos Valores de Ordens');
define('INV_TOOLS_BTN_SO_PO_FIX','Inciar Testar e Corrigir');
define('INV_TOOLS_VALIDATE_SO_PO_DESC','Esta operação testa para assegurar que a quantidade no inventário da OC e a quantidade da OV batem com os lançamentos no diário. Os valores calculados nos lançamentos do diário sobrescrevem os valores da tabela de inventário.');
define('CD_05_75_DESC','Quando habilitado, PhreeBooks atualizará o custo do item na tabela de inventário ou com o preço da OC ou com o preço Compra/Recepção. Útil para compras rápidas e atualização de preços a partir da tela da OC sem necessidade de atualizar as tabelas de inventário primeiro.');
define('INV_TOOLS_VALIDATE_SO_PO','Validar Quantidade Inventário nos Valores da OC');
define('CD_05_70_DESC','Estabelece o número de caracteres esperados quando leitores de código de barra são utilizados. PhreeBooks só realiza a busca quando o número de caracteres foi atingido. Valores típicos são 12 ou 13 caracteres.');
define('CD_05_65_DESC','Quando habilitado, PhreeBooks procura por um comprimento de SKU na ordem igual ao comprimento do código de barra e quando este comprimento é encontrado, tenta encontrar um item no inventário. Isto permite a entrada mais rápida de itens quando leitores de código de barra são utilizados.');
define('CD_05_60_DESC','Permite uma chamada ajax preencher possíveis opções a medida que dados são digitados no campo SKU. Esta função é importante quando os SKUs são conhecidos e agiliza o preenchimento de ordens de compras. Pode tornar lentas entradas de SKU quando leitores de código de barra são utilizados.');
define('CD_05_55_DESC','Permite a criação automática de itens de inventário nas telas de ordem de compra. SKUs não são necessários no PhreeBooks para tipos de inventário não rastreáveis. Esta função permite a criação automática de SKUs na tabela de inventário. O tipo de inventário utilizado será item estoque. As contas utilizadas serão as padrões e métodos de custo para item estoque.');
define('CD_05_50_DESC','Determina a taxa padrão de vendas para utilizar quando inserir itens de inventário. ATENÇÃO: Este valor é aplicado ao inventário Auto-Add mas pode ser modificado na tela Inventário => Manutenção. Os valores de taxas são selecionados da tabela taxas_valores e devem ser especificadas na tela Setup => Taxas de Vendas.');
define('CD_05_52_DESC','Determina a taxa padrão de compras para utilizar quando inserir itens de inventário. ATENÇÃO: Este valor é aplicado ao inventário Auto-Add mas pode ser modificado na tela Inventário => Manutenção. Os valores de taxas são selecionados da tabela taxas_valores e devem ser especificadas na tela Setup => Taxas de Compras.');
define('TEXT_ACT_ITEMS','Atividade');
define('MODULE_INVENTORY_NOTES_1','PRIORIDADE MÉDIA: Especifique contas padrão para tipos de inventário depois de carregar Plano Contas (Empresa -> Inventário Propriedades Módulo -> Inventário)');
define('TEXT_CHARGE_ITEMS','Taxar');
define('TEXT_LABOR_ITEMS','Mão Obra');
define('TEXT_SRV_ITEMS','Serviço');
define('TEXT_NS_ITEMS','Não Estoque');
define('TEXT_MS_ITEMS','Estoque Mestre');
define('TEXT_ASSEMBLIES','Montagens');
define('TEXT_SERIALIZED','Serializado');
define('TEXT_COST_METHOD','Método Custo');
define('TEXT_STOCK_ITEMS','Estoque');
define('TEXT_COGS_ACCOUNT','Conta Custo Vendas');
define('TEXT_INVENTORY_ACCOUNT','Conta Inventário');
define('TEXT_INVENTORY_TYPES','Tipo Inventário');
define('TEXT_SALES_ACCOUNT','Conta Vendas');
define('TEXT_DEFAULT_GL_ACCOUNTS','Contas Padrão Contabilidade Geral');
define('INV_HEADING_FIELD_PROPERTIES','Tipo e Propriedades de Campo (Selecione Uma)');
define('BOX_INV_ADMIN','Administração Inventário');
define('MODULE_INVENTORY_DESCRIPTION','O Módulo Inventário contém todas as funcionalidades para armazenar e manipular produtos e serviços utilizados em sua empresa. Isto inclui itens para uso interno e externo e produtos vendidos. <b>ATENÇÃO: Este é um módulo central e não deve ser removido!</b>');
define('MODULE_INVENTORY_TITLE','Módulo Inventário');
define('TEXT_NEXT_SKU','Próximo SKU');

?>
