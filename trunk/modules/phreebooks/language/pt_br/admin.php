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
//  Path: /modules/phreebooks/language/en_us/admin.php
//

// Module information
define('MODULE_PHREEBOOKS_TITLE','Módulo Contabilidade');
define('MODULE_PHREEBOOKS_DESCRIPTION','O módulo Contabilidade provê lançamentos com contra-partida. Funções incluem Ordem Compra, Ordem Venda, Faturas, Lançamentos Diário e mais. <b>ATENÇÃO: Este é um módulo central e não deve ser removido!</b>');
// Headings
// Installation
define('MODULE_PHREEBOOKS_NOTES_1','PRIORIDADE MÉDIA: Entre informação da Empresas (aba Empresa -> Módulo Administração -> Minha Empresa)');
define('MODULE_PHREEBOOKS_NOTES_2','PRIORIDADE BAIXA: Complete as especificações de servidor de email (aba Empresa -> Módulo Administração -> Email)');
define('MODULE_PHREEBOOKS_NOTES_3','PRIORIDADE ALTA: Alterar ou importar plano de contas das especificações padrão (aba Empresa -> Módulo Administração -> Propriedades Módulo Contabilidade -> Plano de Contas)');
define('MODULE_PHREEBOOKS_NOTES_4','PRIORIDADE MÉDIA: Atualizar as contas padrão para cliente e fornecedor, depois de carregar Plano de Contas (aba Empresa -> Módulo Administração -> Propriedades Módulo Contabilidade -> Clientes/fornecedores)');
// General Defines
define('TEXT_DEFAULT_GL_ACCOUNTS','Plano Contas Padrão');
define('TEXT_PAYMENT_TERMS','Termos Pagamento');
define('TEXT_ACCOUNT_AGING','Atraso Contas');
define('TEXT_GENERAL_JOURNAL','Propriedades Contabilidade Geral');
define('TEXT_NUMBER','Número');
define('TEXT_BOTH', 'Ambos');
define('TEXT_SINGLE_MODE','Lançamento Simples');
define('TEXT_DOUBLE_MODE','Lançamento Duplo');
// PhreeForm processing Titles
define('PB_PF_JOURNAL_DESC','Descrição Diário');
define('PB_PF_ORDER_QTY','Quantidade Solicitada');
define('PB_PF_COA_TYPE_DESC','Tipo Plano Contas');
// Chart of Account Type definitions
define('COA_00_DESC','Caixa');
define('COA_02_DESC','COntas Receber');
define('COA_04_DESC','Inventário');
define('COA_06_DESC','Outros Ativos Correntes');
define('COA_08_DESC','Ativo FIxo');
define('COA_10_DESC','Depreciação Acumulada');
define('COA_12_DESC','Outros Ativos');
define('COA_20_DESC','Contas Pagar');
define('COA_22_DESC','Outros Passivos Correntes');
define('COA_24_DESC','Passivo Longo Prazo');
define('COA_30_DESC','Receita');
define('COA_32_DESC','Custo Vendas');
define('COA_34_DESC','Despesas');
define('COA_40_DESC','Equity - Doesn\'t Close');
define('COA_42_DESC','Equity - Gets Closed');
define('COA_44_DESC','Equity - Retained Earnings');
// Form Group Definitions
define('PB_PF_BANK_CHECK','Bancos Cheques');
define('PB_PF_BANK_DEP_SLIP','Bancos Recibos Depósito');
define('PB_PF_COLLECT_LTR','Collection Letters');
define('PB_PF_CUST_CRD_MEMO','Cliente - Memo Crédito');
define('PB_PF_CUST_LABEL','Labels - Customer');
define('PB_PF_CUST_QUOTE','Customer Quotes');
define('PB_PF_CUST_STATEMENT','Customer Statements');
define('PB_PF_DEP_SLIP','Recibos Depósito');
define('PB_PF_INV_PKG_SLIP','Invoices/Packing Slips');
define('PB_PF_PURCH_ORDER','Ordens Compra');
define('PB_PF_SALES_ORDER','Ordens Venda');
define('PB_PF_SALES_REC','Comprovantes Venda');
define('PB_PF_VENDOR_CRD_MEMO','Fornecedor - Memo Crédito');
define('PB_PF_VENDOR_LABEL','Labels - Fornecedor');
define('PB_PF_VENDOR_QUOTE','Fornecedor Cotações');
define('PB_PF_VENDOR_STATEMENT','Fornecedor Diretivas');
/************************** (PhreeBooks Utilities) ***********************************************/
define('GEN_ADM_TOOLS_AR','Clientes/Recebimentos');
define('GEN_ADM_TOOLS_AP','Fornecedores/Pagamentos');
define('GEN_ADM_TOOLS_RE_POST_FAILED','Não foram selecionados lançamentos para envio, nenhuma ação foi realizada.');
define('GEN_ADM_TOOLS_RE_POST_SUCCESS','Os lançamentos selecionados foram enviados com sucesso. O número de registros enviados foi: %s');
define('GEN_ADM_TOOLS_AUDIT_LOG_RE_POST','Reenviar Lançamentos: ');
define('GEN_ADM_TOOLS_REPOST_HEADING','Reenviar Lançamentos Diário');
define('GEN_ADM_TOOLS_REPOST_DESC','<b>CERTIFIQUE-SE DE FAZER CÓPIA DE SEGURANÇA DE SEUS DADOS ANTES DE REENVIAR LANÇAMENTOS!</b><br />Atenção 1: Reenviar lançamentos pode levar algum tempo, você pode limitar o reenvio definindo um intervalo menor entre datas ou limitando o número de lançamentos.');
define('GEN_ADM_TOOLS_REPOST_CONFIRM','Você tem certeza de que quer reenviar os lançamentos selecionados?\n\nVOCÊ DEVE FAZER CÓPIA DE SEGURANÇA DE SEUS DADOS ANTES DE REENVIAR LANÇAMENTOS!');
define('GEN_ADM_TOOLS_BNK_ETC','Bancos/Inventário/Outros');
define('GEN_ADM_TOOLS_DATE_RANGE','Intervalo Datas Reenvio');
define('GEN_ADM_TOOLS_START_DATE','Data Inicial');
define('GEN_ADM_TOOLS_END_DATE','Data Final');
define('GEN_ADM_TOOLS_BTN_REPOST','Reenviar Lançamentos');

define('GEN_ADM_TOOLS_REPAIR_CHART_HISTORY','Validar e Corrigir Saldos de Conta');
define('GEN_ADM_TOOLS_REPAIR_CHART_DESC','Esta operação valida e corrige os saldos do plano de contas. Se os saldos de balancete e plano de contas não batem, comece por aqui. Primeiro valide os saldos para ver se há um erro e corrija se necessário.');
define('GEN_ADM_TOOLS_REPAIR_TEST','Testar Saldos');
define('GEN_ADM_TOOLS_REPAIR_FIX','Corrigir Erros Saldo');
define('GEN_ADM_TOOLS_BTN_TEST','Testar saldos CG');
define('GEN_ADM_TOOLS_BTN_REPAIR','Corrigir Erros saldos CG');
define('GEN_ADM_TOOLS_REPAIR_CONFIRM','Tem certeza de que quer corrigir os saldos da CG?\n\nVOCÊ DEVE IMPRIMIR OS RELATÓRIOS FINANCEIROS E FAZER CÓPIA DE SEGURANÇA ANTES!');
define('GEN_ADM_TOOLS_REPAIR_ERROR_MSG','Há um erro de saldo no período  %s conta %s valores comparados: %s com: %s');
define('GEN_ADM_TOOLS_REPAIR_SUCCESS','Os saldos de seu plano de contas estão corretos.');
define('GEN_ADM_TOOLS_REPAIR_ERROR','Você deve corrigir os saldos de seu plano de contas. ATENÇÃO: FAÇA CÓPIA DE SEGURANÇA ANTES DE CORRIGIR OS SALDOS DO PLANO DE CONTAS!');
define('GEN_ADM_TOOLS_REPAIR_COMPLETE','O plano de contas foi corrigido.');
define('GEN_ADM_TOOLS_REPAIR_LOG_ENTRY','Saldos CG corrigidos');

define('GL_UTIL_HEADING_TITLE', 'Diário Geral Manutenção, Setup e Utilitários');
define('GL_UTIL_PERIOD_LEGEND','Períodos Contábeis e Anos Fiscais');
define('GL_UTIL_PURGE_ALL','Remover Todos os Lançamentos do Diário (reiniciar)');
define('GL_UTIL_FISCAL_YEAR_TEXT','Datas do calendário fiscal podem ser modificadas aqui. Por favor, note que as datas do ano fiscal não podem ser modificadas para nenhum período até e incluindo o último lançamento no sistema.');
define('GL_UTIL_PURGE_DB','Remover Todos os Lançamentos do Diário (digite \'purge\' na caixa de texto e pressione o botão Remover)<br />');
define('GL_UTIL_PURGE_DB_CONFIRM','Tem certeza de que quer remover todos os Lançamentos do Diário?');
define('GL_UTIL_PURGE_CONFIRM','Removidos todos os Lançamentos do Diário e zeradas todas as tabelas.');
define('GL_UTIL_PURGE_FAIL','Nenhum Lançamento do Diário foi afetado!');
define('GL_CURRENT_PERIOD','Período Contábil corrente é: ');
define('GL_WARN_ADD_FISCAL_YEAR','Tem certeza de que quer inserir um ano fiscal: ');
define('GL_ERROR_FISCAL_YEAR_SEQ','O último período do ano fiscal alterado não condiz com a data inicial do próximo ano fiscal. A data inicial do próximo ano fiscal foi modificada e deve ser revista.');
define('GL_WARN_CHANGE_ACCT_PERIOD','Entre o período contábil a ser tratado como corrente:');
define('GL_ERROR_BAD_ACCT_PERIOD','O período selecionado não foi estabelecido. Ou reveja o período ou insira um ano fiscal para continuar.');
define('GL_ERROR_NO_BALANCE','Não é possível atualizar saldos iniciais porque débito e crédito não batem!');
define('GL_ERROR_UPDATE_COA_HISTORY','Erro atualizando Histórico de Plano de Contas depois de estabelecer os saldos iniciais!');
define('GL_BEG_BAL_ERROR_0',' encontrado na linha ');
define('GL_BEG_BAL_ERROR_1','ID Plano Contas inválido foi encontrado na linha ');
define('GL_BEG_BAL_ERROR_2','Não foi encontrado número fatura na linha %d. Marcado como aguardando pagamento!');
define('GL_BEG_BAL_ERROR_3','Importação não realizada. Não foi encontrado número fatura na linha ');
define('GL_BEG_BAL_ERROR_4','Script não executado. Formato de data errado encontrado na linha %d. Formato esperado: ');
define('GL_BEG_BAL_ERROR_5','Pulando linha. Valor total zerado encontrado na linha ');
define('GL_BEG_BAL_ERROR_6','ID Plano Contas inválido foi encontrado na linha ');
define('GL_BEG_BAL_ERROR_7','Pulndo item inventário. Quantidae zero encontrada na linha ');
define('GL_BEG_BAL_ERROR_8','Falha atualização sku %s, o processo foi terminado.');
define('GL_BEG_BAL_ERROR_9','Falha atualização conta %s, o processo foi terminado.');
define('GEN_ADM_TOOLS_POST_SEQ_SUCCESS','Alterações no número de ordem corrente realizadas com sucesso.');
define('GEN_ADM_TOOLS_AUDIT_LOG_SEQ','Status Corrente Ordem - Atualização');
define('GEN_ADM_TOOLS_TITLE','Ferramentas Administrativas e Utilitários');

define('NEXT_AR_QUOTE_NUM_DESC','Próximo Número Cotação Cliente');
define('NEXT_AP_QUOTE_NUM_DESC','Próximo Número Cotação Fornecedor');
define('NEXT_DEPOSIT_NUM_DESC','Próximo Número Depósito');
define('NEXT_SO_NUM_DESC','Próximo Número Ordem Venda');
define('NEXT_PO_NUM_DESC','Próximo Número Ordem Compra');
define('NEXT_CHECK_NUM_DESC','Próximo Número Cheque');
define('NEXT_INV_NUM_DESC','Próximo Número Venda/Fatura');
define('NEXT_CM_NUM_DESC','Próximo Número Memo Crédito Cliente');
define('NEXT_VCM_NUM_DESC','Próximo Número Memo Crédito Vendor');
/************************** (General Defaults) ***********************************************/
define('CD_13_01_DESC', 'Altera automaticamente o período contábil corrente baseado na data do servidor e calendário fiscal corrente. Se desabilitado, o período contábil corrente deve ser alterado manualmente no menu Contabilidade => Utilitários.');
define('CD_13_05_DESC', 'Determina como mostrar as contas  em menus pull-down .<br />Número - somente número da conta.<br />Descrição - somente descrição conta.<br />Ambos - número e descrição serão mostrados.');
define('CD_01_50_DESC', 'Esta função insere dois campos adicionais nas telas de ordens para entrar um nível de desconto por ordem ou percentual. Se desabilitado, os campos não serão mostrados nas telas de ordens.');
define('CD_01_52_DESC', 'Habilitar esta função fará a Contabilidade arredondar taxas calculadas por autoridade antes de lançar em cada autoridade. Para taxas com somente uma autoridade, apenas evitará erros de precisão no diário. Para taxas multi-autoridade, pode trazer diferenças muito grandes ou muito pequenas. Se não tiver certeza, desabilite.');
define('CD_01_55_DESC', 'Se verdadeiro, esta opção permitirá entrada nas ordens via USB e leitores de código de barras suportados.');
define('CD_01_75_DESC', 'Se verdadeiro, esta opção utiliza uma tela de ordem de linha simples sem mostrar campos para preço cheio e desconto. A tela de linha simples utiliza números de contas da CG ao invés de permitir números / descrições em duas linhas.');
define('ALLOW_NEGATIVE_INVENTORY_DESC','Permitir vendas de itens de inventário resultarem saldo negativo? PhreeBooks permite isto e o lançamento no diário que resultou em estoque negativo é reenviado quando o inventário é recebido para calcular os custos adequadamente.');
/************************** (Customer Defaults) ***********************************************/
define('CD_02_01_DESC', 'Conta padrão para utilizar para Contas a Receber. Tipicamente uma conta do tipo Contas a Receber.');
define('CD_02_02_DESC', 'Conta padrão para utilizar para transações de vendas. Tipicamente uma conta do tipo Receitas.');
define('CD_02_03_DESC', 'Conta padrão para utilizar para recibos quando clientes pagam faturas. Tipicamente uma conta do tipo Caixa.');
define('CD_02_04_DESC', 'Conta padrão para utilizar para descontos quando clientes pagam antecipadamente com descontos aplicados. Tipicamente uma conta do tipo Receitas.');
define('CD_02_05_DESC', 'Conta padrão para utilizar para tarifas de frete. Tipicamente uma conta do tipo Receitas.');
define('CD_02_06_DESC', 'Conta padrão para utilizar para recebimento em espécie em depósitos de clientes. Tipicamente uma conta do tipo Caixa.');
define('CD_02_07_DESC', 'Conta padrão para utilizar para crédito aguardando depósito de cliente. Tipicamente uma conta do tipo Outros Passivos Correntes.');

define('CD_02_10_DESC', 'Descontos para pagamento antecipado. Deixe percentual zero ou dias antecipados zero para desabilitar Descontos para pagamento antecipado.');
define('CD_02_11_DESC', 'Verificar limite  crédito do cliente quando processar ordens.');
define('CD_02_12_DESC', 'Valor padrão para limite de crédito de cliente. (%s)');
define('CD_02_13_DESC', 'Percentual (%) desconto se pago em ');
define('CD_02_14_DESC', 'dias. Total até');
define('CD_02_15_DESC', 'dias.');
define('APPLY_CUSTOMER_CREDIT_LIMIT_DESC','Exigir aprovação administração para ordens acima do limite de crédito.');

define('CD_02_16_DESC', 'Estabelece a data inicial para atraso contas.');
define('CD_02_17_DESC', 'Determina o número de dias para o primeiro aviso da atraso de pagamento. O período inicia a partir da data inicial para atraso contas.');
define('CD_02_18_DESC', 'Determina o número de dias para o segundo aviso da atraso de pagamento. O período inicia a partir da data inicial para atraso contas.');
define('CD_02_19_DESC', 'Determina o número de dias para o terceiro aviso da atraso de pagamento. O período inicia a partir da data inicial para atraso contas.');
define('CD_02_20_DESC', 'Texto utilizado nos relatórios para mostrar atraso a partir da data número 1.');
define('CD_02_21_DESC', 'Texto utilizado nos relatórios para mostrar atraso a partir da data número 2.');
define('CD_02_22_DESC', 'Texto utilizado nos relatórios para mostrar atraso a partir da data número 3.');
define('CD_02_23_DESC', 'Texto utilizado nos relatórios para mostrar atraso a partir da data número 4.');

define('CD_02_24_DESC', 'Determina o cálculo ou não de encargos financeiros sobre atraso de pagamentos.');
define('CD_02_30_DESC', 'Se habilitado, despesas de transporte serão adicionadas ao cálculo da taxa de vendas.');
define('CD_02_35_DESC', 'Se verdadeiro, esta opção vai designar um ID automático aos clientes/fornecedores quando forem criados..');
define('CD_02_40_DESC', 'Esta função mostra o status do fornecedor em uma janela popup na tela de ordens quando o cliente é selecionadodo popup de busca de contatos. Mostra saldos, data conta e status da conta..');
define('CD_02_50_DESC', 'Se nível de descontos por ordem está habilitado, este switch determinase a taxa de vendas é calculada antes ou depois da aplicação do desconto na Ordens de Venda, Vendas/Faturas e Cotações de Clientes.');
/************************** (Vendor Defaults) ***********************************************/
define('CD_03_01_DESC', 'Conta padrão para utilizar para itens recebidos. Esta conta pode ser substituida pela conta individual do item. Tipicamente uma conta do tipo Inventário ou Despesas.');
define('CD_03_02_DESC', 'Conta padrão para utilizar para todas as compras. Esta conta pode ser substituida pela conta individual do fornecedor. Tipicamente uma conta do tipo Contas Pagar.');
define('CD_03_03_DESC', 'Conta padrão para utilizar para pagamentos quando as faturas de fornecedores são pagas. Tipicamente uma conta do tipo Caixa.');
define('CD_03_04_DESC', 'Conta padrão para utilizar para freight charges for shipments from vendors. Tipicamente uma conta do tipo Despesas.');
define('CD_03_05_DESC', 'Conta padrão para utilizar para purchase discounts paid with early discount payment terms.  Tipicamente uma conta do tipo Contas Pagar.');
define('CD_03_06_DESC', 'Conta padrão para utilizar para cash paid to vendors for deposits. Tipicamente uma conta do tipo Caixa.');
define('CD_03_07_DESC', 'Conta padrão para utilizar para vendor deposits. Tipicamente uma conta do tipo Outros Passivos Correntes.');
define('CD_03_11_DESC', 'Condições pagamento padrão');
define('CD_03_12_DESC', 'Valor padrão para limite de crédito de fornecedor. (%s)');
define('CD_03_30_DESC', 'Se habilitado, tarifas de frete serão adicionadas no cálculo das taxas de vendas.');
define('CD_03_35_DESC', 'Se verdadeiro, esta opção vai designar um ID automático aos fornecedores quando forem criados.');
define('CD_03_40_DESC', 'Esta função mostra o status do fornecedor em uma janela popup na tela de ordens quando o fornecedor é selecionadodo popup de busca de contatos. Mostra saldos, data conta e status da conta.');
define('CD_03_50_DESC', 'Se nível de descontos por ordem está habilitado, este switch determinase a taxa de vendas é calculada antes ou depois da aplicação do desconto na Ordem de Compra, Compras e Cotações de fornecedores.');
/************************** (Chart of Accounts) ***********************************************/
define('GL_SELECT_STD_CHART','Selecione o Plano de Contas Padrão: ');
define('GL_CHART_REPLACE','Substituir o Plano de Contas corrente');
define('GL_CHART_IMPORT_DESC','ou Plano de Contas customizado para importar: ');
define('GL_CHART_DELETE_WARNING','ATENÇÃO: Plano de Contas corrente não pode ser removido se há lançamentos no diário!');
define('GL_JOURNAL_NOT_EMTPY','O Diário Geral não está vazio, o Plano de Contas corrente não pode ser excluído!');
define('GL_ACCOUNT_DUPLICATE','A conta: %s já existe!. A conta não será inserida.');
define('GL_INFO_HEADING_ONLY', 'Esta conta é de resumo e não pode aceitar lançamentos?');
define('GL_INFO_PRIMARY_ACCT_ID', 'Esta conta é uma sub-conta, selecione uma conta primária:');
define('ERROR_ACCT_TYPE_REQ','O Tipo de Conta CG é obrigatório!');
define('GL_ERROR_CANT_MAKE_HEADING','Esta conta tem saldo. Não pode ser convertida em conta resumo.');
define('GL_POPUP_WINDOW_TITLE','Plano de Contas');
define('GL_HEADING_ACCOUNT_NAME', 'ID Conta');
define('GL_HEADING_SUBACCOUNT', 'Subconta');
define('GL_EDIT_INTRO', 'Por favor, faça as alterações necessárias');
define('GL_INFO_ACCOUNT_TYPE', 'Tipo Conta (Obrigatório)');
define('GL_INFO_ACCOUNT_INACTIVE', 'Conta inativa');
define('GL_INFO_INSERT_INTRO', 'Por favor, entre a nova Conta CG e suas propriedades');
define('GL_INFO_NEW_ACCOUNT', 'Nova Conta');
define('GL_INFO_EDIT_ACCOUNT', 'Alterar Conta');
define('GL_INFO_DELETE_INTRO', 'Tem certeza de que quer remover esta conta?\nContas não podem ser removidas se houver lançamentos.');
define('GL_DISPLAY_NUMBER_OF_COA', TEXT_DISPLAY_NUMBER . 'contas');
define('GL_ERROR_CANT_DELETE','Esta conta não pode ser removida porque há lançamentos no diário.');
define('GL_LOG_CHART_OF_ACCOUNTS','Plano de Contas - ');
/************************** (Sales/Purchase Authorities) ***********************************************/
define('SETUP_TITLE_TAX_AUTHS_VEND', 'Autoridades Fiscais Compras');
define('SETUP_TITLE_TAX_AUTHS', 'Autoridades Fiscais Vendas');
define('SETUP_TAX_DESC_SHORT', 'Nome Curto');
define('SETUP_TAX_GL_ACCT', 'ID COnta CG');
define('SETUP_TAX_RATE', 'Taxa (percentual)');
define('SETUP_TAX_AUTH_EDIT_INTRO', 'Por favor, faça as alterações necessárias');
define('SETUP_INFO_DESC_SHORT', 'Nome Curto (15 carac max)');
define('SETUP_INFO_DESC_LONG', 'Nome Longo (64 carac max)');
define('SETUP_INFO_GL_ACCOUNT', 'Conta CG para registras a taxa:');
define('SETUP_INFO_VENDOR_ID', 'Vendor to submit funds to:');
define('SETUP_INFO_TAX_RATE', 'Taxa (percentual)');
define('SETUP_TAX_AUTH_INSERT_INTRO', 'Por favor, entre a nova Autoridade Fiscal com suas propriedades');
define('SETUP_TAX_AUTH_DELETE_INTRO', 'Tem certeza de que quer remover esta Autoridade Fiscal?');
define('SETUP_TAX_AUTHS_DELETE_ERROR','Não é possível remover esta Autoridade Fiscal, esta em uso em lançamentos do diário.');
define('SETUP_INFO_HEADING_NEW_TAX_AUTH', 'Nova Autoridade Fiscal');
define('SETUP_INFO_HEADING_EDIT_TAX_AUTH', 'Alterar Autoridade Fiscal');
define('SETUP_TAX_AUTHS_LOG','Autoridades Fiscais - ');
define('SETUP_DISPLAY_NUMBER_OF_TAX_AUTH', TEXT_DISPLAY_NUMBER . 'Autoridades Fiscais');
/************************** (Sales/Purchase Tax Rates) ***********************************************/
define('SETUP_TITLE_TAX_RATES', 'Impostos Vendas');
define('SETUP_TITLE_TAX_RATES_VEND', 'Impostos Compras');
define('SETUP_HEADING_TAX_FREIGHT', 'Frete');
define('SETUP_HEADING_TOTAL_TAX', 'Taxa Total (percentual)');
define('SETUP_TAX_EDIT_INTRO', 'Por favor, faça as alterações necessárias');
define('SETUP_INFO_TAX_AUTHORITIES', 'Autoridades Fiscais');
define('SETUP_INFO_TAX_AUTH_ADD', 'Selecione uma autoridade fiscal para inserir');
define('SETUP_INFO_TAX_AUTH_DELETE', 'Selecione uma autoridade fiscal para remover');
define('SETUP_INFO_FREIGHT_TAXABLE', 'Imposto Frete ');
define('SETUP_TAX_INSERT_INTRO', 'Por favor, entre a nova taxa e suas propriedades');
define('SETUP_TAX_DELETE_INTRO', 'Tem certeza dse que quer remover esta taxa?');
define('SETUP_HEADING_NEW_TAX_RATE', 'Nova Taxa');
define('SETUP_HEADING_EDIT_TAX_RATE', 'Alterar Taxa');
define('SETUP_DISPLAY_NUMBER_OF_TAX_RATES', TEXT_DISPLAY_NUMBER . 'taxas');
define('SETUP_TAX_RATES_LOG','Taxas - ');

?>