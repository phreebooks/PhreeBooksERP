<?php
// +-----------------------------------------------------------------+
// Arquivo Tradução Idioma  Phreedom
// Generated: 2013-10-22 05:28:46
// Module/Method: phreepos
// ISO Language: pt_br
// Version: 3.6
// +-----------------------------------------------------------------+
// Path: /modules/phreepos/language/pt_br/admin.php

define('TEXT_MAX_DISCOUNT','Especificar valor máximo permitido de desconto neste cupom. Isto não inclui os descontos especificados nas listas de preços.');
define('TEXT_RESTRICT_CURRENCY','Restringir caixa a esta moeda');
define('TEXT_OPEN_DRAWER_CODES','abrir códigos gaveta');
define('TEXT_DIF_GL_ACCOUNT','Conta CG para diferenças final dia:');
define('TEXT_GL_ACCOUNT_ROUNDING','Conta CG para arredondamento:');
define('TEXT_ROUNDED_OF','Arredondamento de');
define('TEXT_NEUTRAL','Neutro');
define('TEXT_10_CENTS','10 Centavos');
define('TEXT_INTEGER','Inteiro');
define('PHREEPOS_ROUNDING_DESC','Como você quer que o total final seja arredondado.<br> <b>Não</b>significa que o total final não será arredondado.<br><b>INTEGER</b> means:  to the benefit of the customer everything smaller than a dollar will be ignored.<br><b>10 CENTS</b> means:  to the benefit of the customer everything smaller than 10 cents will be ignored.<br><b>NEUTRAL</b> there will be rounded to the nearest 0, 5 or 10 cents (1,2,6,7 go down 3,4,8,9 go up)');
define('PHREEPOS_DISCOUNT_OF_DESC','Você quer que o desconto seja calculado do total<br> ( se você escolher Não então será calculado do subtotal) ');
define('PHREEPOS_DISPLAY_WITH_TAX_DESC','Você quer que preços na tela com impostos<br> se você escolher Não preços serão mostrados sem impostos)');
define('SETUP_OT_DELETE_INTRO','Você quer remover esta outra transação?');
define('SETUP_TILL_DELETE_INTRO','Você quer remover este cupom?');
define('TEXT_EDIT_OTHER_TRANSACTION','Editar Outra Transação');
define('TEXT_TILLS','Caixas');
define('TEXT_ENTER_NEW_OTHER_TRANSACTION','Nova Outra Transação');
define('TEXT_EDIT_TILL','Editar Caixa');
define('TEXT_ENTER_NEW_TILL','Novo Caixa');
define('PHREEPOS_RECEIPT_PRINTER_OPEN_DRAWER_DESC','Aqui você pode inserir código para abrir a gaveta dependente de pagamento (especifique a opção abrir gaveta no módulo de pagamento).<br> Se um dos pagamentos selecionados tiver a opção \\\'abrir gaveta\' especificada, a gaveta vai abrir.<br>Separe os códigos com um : e linhas com uma ,<br>Os códigos são um número de chr como chr(13)<br><b>Não coloque nenhum texto nos códigos. Isto pode resultar em erro.</b>');
define('PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE_DESC','Aqui você pode inserir código para abrir a gaveta ou cortar o cupom.<br>Separe os códigos com um : e linhas com , . <br>Os códigos são números de chr como chr(13) é 13<br><b>Não coloque nenhum texto nos códigos. Isto pode resultar em erro.</b> ');
define('PHREEPOS_RECEIPT_PRINTER_STARTING_LINE_DESC','Aqui você pode inserir código que deve aparecer no cabeçalho do cupom.<br>Separe os códigos com um : e linhas com , . <br>Os códigos são números de chr como chr(13) é 13<br><b>Não coloque nenhum texto nos códigos. Isto pode resultar em erro.</b> Veja a documentação de sua impressora  para ter os códigos corretos.');
define('PHREEPOS_RECEIPT_PRINTER_NAME_DESC','Especifica o nome da impressora para imprimir cupons conforme especificada nas preferências de impressora de sua estação local.');
define('TEXT_REQUIRE_ADDRESS_INFORMATION_FOR_EVERY_POS_OR_POP_SALE','Solicitar informação de endereço para cada venda PDV?');
define('TEXT_POINT_OF_SALE_ADMINISTRATION','Administração de Ponto de Venda');
define('TEXT_PHREEPOS_SETTINGS','Propriedades Módulo PhreePOS ');
define('MODULE_PHREEPOS_DESCRIPTION','O Módulo PhreePOS fornece uma interface de Ponto De Venda. Este módulo é complementar ao Módulo Phreebooks e não o substitui.');
define('MODULE_PHREEPOS_TITLE','Módulo PhreePOS ');
define('TEXT_PHREEPOS_TRANSACTION_TYPE','Selecione o tipo de transação');
define('TEXT_ALLOW_USE_OF_TAX','Não pode ser utilizado imposto');
define('TEXT_DEFAULT_TAX','imposto padrão');
define('TEXT_OTHER_TRANS','Outras Transações');

?>
