<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/install/language/pt_br/language.php
//

define('LANGUAGE','Português (BR)');
define('HTML_PARAMS','lang="pt-BR" xml:lang="pt-BR"');
define('CHARSET', 'UTF-8');
// template_welcome
define('LANGUAGE_TEXT','Idiomas Disponíveis para Instalação: ');
define('TITLE_WELCOME','Bemvindo - PhreeBooks Contabilidade');
define('TEXT_AGREE','Concordo');
define('TEXT_DISAGREE','Discordo');
define('INTRO_WELCOME', '<h2>Bemvindo ao PhreeBooks Contabilidade</h2>
<p>Este script vai auxiliá-lo na instalação da aplicação e verificar se seu sistema atende aos requisitos mínimos. Você vai precisar das informações a seguir para continuar:</p>
<ul>
  <li>Uma tabela mysql existente com informação de acesso</li>
  <li>Direito de escrita no servidor Web (777) para as pastas: /includes e /my_files</li>
  <li>Um nome de usuário, endereço de email e senha para o administrador</li>
  <li>Informação de caminho de servidor SSL (isto é recomendado e pode ser modificado se necessário)</li>
  <li>O mês e ano fiscal parea início de armazenamento de lançamentos no diário</li>
</ul>
<p>Por favor, confirme sua aceitação dos termos de licença e tecle Continuar.</p>');
define('DESC_AGREE', 'Eu li e concordo com os termos de Licença como descritos acima.');
define('DESC_DISAGREE', 'Eu li e não concordo com os termos de Licença como descritos acima.');
// template_inspect
define('TITLE_INSPECT','Verificar - PhreeBooks Contabilidade');
define('MSG_INSPECT_ERRORS','Os seguintes erros de instalação foram encontrados.
<ul>
  <li>Erros (em vermelho) devem ser corrigidos antes que o script de instalação possa continuar.</li>
  <li>Avisos (em amarelo) não impedirão a instalação mas podem impedir a operação apropriada de alguns módulos.</li>
</ul>');
define('INSTALL_ERROR_PHP_VERSION','Sua versão de php deve ser 5.2 ou posterior.');
define('INSTALL_ERROR_REGISER_GLOBALS','Registro globais deve ser desabilitado.');
define('INSTALL_ERROR_SAFE_MODE','Sua configuração php está habilitada para operar em modo seguro. Modo seguro deve ser desabilitado para instalar o PhreeBooks.');
define('INSTALL_ERROR_SESSION_SUPPORT','Sua configuração php não tem o suporte de sessão instalao. Suporte de sessão é necessário para executar o PhreeBooks.');
define('INSTALL_ERROR_OPENSSL','Seu servidor deve ter openssl instalado.');
define('INSTALL_ERROR_CURL','Seu servidor de aplicação php foi instalado sem suporte a cURL. Suporte a cURL é necessário para comunicação segura no envio/recebimento de informação para aplicações remotas.');
define('INSTALL_ERROR_UPLOADS','Seu servidor de aplicação php foi instalado sem suporte a upload de arquivos. Suporte a upload é necessário para importar arquivos para o PhreeBooks.');
define('INSTALL_ERROR_UPLOAD_DIR','Não foi possível encontrar uma pasta para upload temporário de arquivos neste servidor.');
define('INSTALL_ERROR_XML','Seu servidor de aplicação php foi instalado sem suporte a XML. Alguns módulos podem não operar se este suporte não estiver disponível.');
define('INSTALL_ERROR_FTP','Seu servidor de aplicação php foi instalado sem suporte a FTP. Alguns módulos podem não operar se este suporte não estiver disponível.');
define('INSTALL_ERROR_INCLUDES_DIR','A pasta /includes não está acessível para escrita. O instalador precisa de acesso a esta pasta para criar o arquivo de configuração.');
define('INSTALL_ERROR_MY_FILES_DIR','A pasta /my_files não está acessível para escrita. O instalador precisa de acesso a esta pasta para armazenar os arquivos de sua empresa.');
define('TEXT_RECHECK','Re-verificar');
define('TEXT_INSTALL','Instalar');
// template_install
define('TITLE_INSTALL','Instalar - PhreeBooks Contabilidade');
define('MSG_INSTALL_INTRO','Por favor entre alguma informação sobre sua empresa, administrador, servidor web e banco de dados.');
define('TEXT_COMPANY_INFO','Informação Empresa');
define('TEXT_ADMIN_INFO','Informação Administrador');
define('TEXT_SRVR_INFO','Informação Servidor');
define('TEXT_DB_INFO','Informação Base de Dados');
define('TEXT_FISCAL_INFO','Informação Fiscal');
define('TEXT_COMPANY_NAME','Entre um nome curto para sua empresa. Ele será mostrado no menu pulldown para acesso ao sistema.');
define('TEXT_INSTALL_DEMO_DATA','Você quer instalar dados demo para cada módulo se disponpivel? ATENÇÃO: Se optar por sim, os arquivos serão limpos antes que os dados demo sejam escritos.');
define('TEXT_USER_NAME','Entre um nome de usuário para o administrador');
define('TEXT_USER_PASSWORD','Entre uma senha para o administrador do site');
define('TEXT_UER_PW_CONFIRM','Digite novamente a senha para o administrador do site');
define('TEXT_USER_EMAIL','Entre um endereço de email para o administrador do site');
define('TEXT_HTTP_SRVR','Entre a URL http do servidor para a pasta raiz (normalmente o valor padrão é suficiente)');
define('TEXT_USE_SSL','Utilizar SSL para acessar sua empresa (Atenção: um certificado  SSL deve ser instalado no servidor). Isto pode ser modificado posteriormente se SSL não for encessário neste momento.');
define('TEXT_HTTPS_SRVR','Entre a URL https do servidor para a pasta raiz para transações seguras (normalmente o valor padrão é suficiente)');
define('TEXT_DB_HOST','Entre o nome da base de dados no servidor');
define('TEXT_DB_NAME','Entre o nome da base de dados (a base de dados deve existir no servidor)');
define('TEXT_DB_PREFIX','Se esta base de dados é compartilhada com outra aplicação, entre um prefixo para utilizar nesta instalação (utilize somente caracteres alfabéticos e underscore):');
define('TEXT_DB_USER','Entre o nome de usuário da base de dados');
define('TEXT_DB_PASSWORD','Entre a senha da base de dados');
define('TEXT_FY_MONTH_INFO', 'Selecione um mês de início para seu primeiro período contábil. PhreeBooks vai inicialmente setar o início no primeiro dia do mês selecionado como período 1.');
define('TEXT_FY_YEAR_INFO', 'Selecione o ano de início para seu primeiro ano fiscal. O mês selecionado acima e esta seleção de ano serão a menor data em que lançamentos de diário poderão ser feitas.');

define('ERROR_TEXT_ADMIN_COMPANY_ISEMPTY','O nome da empresa está vazio');
define('ERROR_TEXT_ADMIN_USERNAME_ISEMPTY', 'O nome de usuário do administrador não pode ser vazio');
define('ERROR_TEXT_ADMIN_EMAIL_ISEMPTY', 'O endereço de email do administrador não pode ser vazio');
define('ERROR_TEXT_LOGIN_PASS_ISEMPTY', 'A senha do administrador não pode ser vazia');
define('ERROR_TEXT_LOGIN_PASS_NOTEQUAL', 'As duas senhas não são iguais');
define('ERROR_TEXT_DB_PREFIX_NODOTS','O prefixo de tabelas da base de dados só pode conter os caracteres a-z e _ (underscore)');
define('ERROR_TEXT_DB_HOST_ISEMPTY', 'O nome do host da base de dados está vazio');
define('ERROR_TEXT_DB_NAME_ISEMPTY', 'O nome da base de dados está vazio');
define('ERROR_TEXT_DB_USERNAME_ISEMPTY', 'O nome de usuário da base de dados está vazio');
define('ERROR_TEXT_DB_PASSWORD_ISEMPTY', 'A senha da base de dados não pode estar vazia');
define('MSG_ERROR_MODULE_INSTALL','Houve erros na instalação do módulo: %s. Veja as mensagens acima para mais detalhes.');
define('MSG_ERROR_CANNOT_CONNECT_DB','Não foi possível conectar com a base de dados, por favor verifique as especificações. Erro retornado: ');
define('MSG_ERROR_INNODB_NOT_ENABLED','A máquina MYSQL InnoDB não está instalada!. PhreeBooks exige a capacidade de transação da máquina MySQL InnoDB para operar apropriadamente.');
define('MSG_ERROR_CONFIGURE_EXISTS','O arquivo includes/configure.php já existe, isto pode indicar que o PhreeBooks já foi instalado ou está sendo reinstalado. Este arquivo deve ser removido para que a instalação seja completada com sucesso!');
// template_finish
define('TITLE_FINISH','Fim - PhreeBooks&trade; Contabilidade');
define('TEXT_GO_TO_COMPANY','Vá para sua Empresa');
define('INTRO_FINISHED','<h2>Parabéns!</h2>
<h3>Você instalou com sucesso PhreeBooks&trade; Contabilidade em seu sistema!</h3>
<h2>Próximos Passos</h2>
<p>Uma lista de Coisas a Fazer foi gerada identificando as ações necessárias para operar os módulos instalados. Esta lista vai aparecer no painel da página principal do administrador. Configuração adicional de mpódulos, preferências e especificações podem ser feitas através do Módulo Empresa Menu Admin.</p>
<p>Por razões de segurança, você deve alterar as permissões para seu arquivo de configuração para somente leitura. Este arquivo pode ser encontrado na pasta <strong>/includes/configure.php</strong>. Se você habilitou acesso total a pasta includes para instalação, isto pode ser modificado para a especificação anterior. Também é necessário que a pasta <strong>/install</strong> seja removida ou renomeada para prevenir a reinstalação da aplicação.</p>
<h2>Documentação e suporte</h2>
<p>PhreeBooks inclui um sistema de auxílio sensível a contexto. Como acontece com a maioria das aplicações de código aberto, este é um trabalho em processo contínuo mas provê uma orientação e suporte gerais.</p>
<p>Existe também uma  documentação online no site PhreeBooks (<a target="_blank" href="http://www.phreebooks.com">www.PhreeBooks.com</a>). O modulo de documentação mais atual está disponpivel bem como outras FAQs e suporte a customização/desenvolvimento.</p>
<p>Finalmente, o fórum de usuários localizado no site PhreeBooks provê suporte comunitário e permite postar dúvidas, problemas e sugestões. Se você estiver travado, sinta-se a vontade para postar uma dúvida! Nós temos uma comunidade ativa, amigável e com conhecimento que vai recebê-lo muito bem.</p>');

?>