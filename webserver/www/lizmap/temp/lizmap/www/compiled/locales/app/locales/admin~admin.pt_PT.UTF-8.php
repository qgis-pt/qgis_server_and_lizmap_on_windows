<?php $_loaded= array (
  'header.admin' => 'Administração',
  'menu.configuration.main.label' => 'Configuração do Lizmap',
  'menu.lizmap.repositories.label' => 'Gestão de mapas',
  'menu.lizmap.landingPageContent.label' => 'Conteúdo da página inicial',
  'menu.lizmap.logs.label' => 'Registos do Lizmap',
  'menu.lizmap.logs.view.label' => 'Ver registos',
  'generic.h2' => 'Geral',
  'generic.version.number.label' => 'Versão',
  'configuration.h1' => 'Configuração do Lizmap',
  'configuration.repository.label' => 'Repositório',
  'configuration.button.back.label' => 'Voltar',
  'configuration.services.label' => 'Serviços',
  'configuration.services.section.interface.label' => 'Interface',
  'configuration.services.section.emails.label' => 'Emails',
  'configuration.services.section.projects.label' => 'Projetos',
  'configuration.services.section.cache.label' => 'Cache',
  'configuration.services.section.qgis.label' => 'Qgis server',
  'configuration.services.section.system.label' => 'Sistema',
  'configuration.services.qgisServerVersion.label' => 'Versão do QGIS Server',
  'configuration.services.wmsServerURL.label' => 'URL do serviço WMS',
  'configuration.services.wmsPublicUrlList.label' => 'Lista dos suddomínios URLs WMS (opcional)',
  'configuration.services.wmsMaxWidth.label' => 'Largura Máxima para o pedido GetMap',
  'configuration.services.wmsMaxHeight.label' => 'Altura Máxima para o pedido GetMap',
  'configuration.services.onlyMaps.label' => 'Só mapas',
  'configuration.services.defaultRepository.label' => 'Repositório padrão',
  'configuration.services.defaultProject.label' => 'Projeto padrão',
  'configuration.services.cacheStorageType.label' => 'Tipo de armazenamento da cache no servidor',
  'configuration.services.cacheRootDirectory.label' => 'Diretório raiz da cache',
  'configuration.services.cacheExpiration.label' => 'Tempo(s) de expiração da cache no servidor',
  'configuration.services.rootRepositories.label' => 'Pasta root dos repositórios',
  'configuration.services.debugMode.label' => 'Modalidade de debug',
  'configuration.services.allowUserAccountRequests.label' => 'Permitir que os visitantes solicitem uma conta',
  'configuration.services.allowUserAccountRequests.help.deactivated' => 'Esta opção está desativada devido à utilização de autenticação LDAP',
  'configuration.services.adminContactEmail.label' => 'E-mail do administrador',
  'config.services.allowUserAccountRequest.noemail' => ' Esta opção não pode ser ativada enquanto o e-mail do remetente não estiver definido. Entrar em contacto com o administrador do sistema.',
  'configuration.button.modify.service.label' => 'Modificar',
  'configuration.button.view.repository.label' => 'Ver',
  'configuration.button.modify.repository.label' => 'Modificar',
  'configuration.button.remove.repository.label' => 'Eliminar',
  'configuration.button.remove.repository.confirm.label' => 'Eliminar este repositório?',
  'configuration.button.add.repository.label' => 'Criar um repositório',
  'form.admin_services.h1' => 'Modificar a configuração genérica do Lizmap',
  'form.admin_services.on.label' => 'Ligado',
  'form.admin_services.off.label' => 'Desligado',
  'form.admin_services.yes.label' => 'Sim',
  'form.admin_services.no.label' => 'Não',
  'form.admin_services.appName.label' => 'Nome da Aplicação',
  'form.admin_services.qgisServerVersion.label' => 'Versão do QGIS Server',
  'form.admin_services.qgisServerVersion.214.label' => '≤ 2.14',
  'form.admin_services.qgisServerVersion.218.label' => '2.18',
  'form.admin_services.qgisServerVersion.300.label' => '≥ 3.0',
  'form.admin_services.wmsServerURL.label' => 'URL do servidor WMS',
  'form.admin_services.wmsPublicUrlList.label' => 'Lista dos suddomínios URLs WMS (opcional)',
  'form.admin_services.message.wmsPublicUrlList.wrong' => 'A estrutura de URLs WMS não é válida',
  'form.admin_services.wmsMaxWidth.label' => 'Largura max. padrão para o pedido GetMap',
  'form.admin_services.wmsMaxHeight.label' => 'Altura max. padrão para o pedido GetMap',
  'form.admin_services.cacheStorageType.label' => 'Tipo de armazenamento da cache do servidor',
  'form.admin_services.cacheStorageType.sqlite.label' => 'Base de dados SQLite',
  'form.admin_services.cacheStorageType.file.label' => 'Arquivos',
  'form.admin_services.cacheStorageType.redis.label' => 'Redis',
  'form.admin_services.cacheRedisHost.label' => 'Servidor Redis',
  'form.admin_services.cacheRedisPort.label' => 'Porta Redis',
  'form.admin_services.cacheRedisDb.label' => 'Índice da base de dados Redis',
  'form.admin_services.cacheRedisKeyPrefix.label' => 'Prefixo da chave Redis',
  'form.admin_services.cacheRedisHost.help' => 'Servidor Redis (será utilizado se o Redis for escolhido para armazenar a cache)',
  'form.admin_services.cacheRedisPort.help' => 'Porta Redis (será utilizado se o Redis for escolhido para armazenar a cache)',
  'form.admin_services.cacheRedisDb.help' => 'Índice da base de dados Redis: inteiro>=0 (será utilizado se o Redis for escolhido para armazenar a cache)',
  'form.admin_services.cacheRedisKeyPrefix.help' => 'Prefixo da chave Redis: string, opcional (será utilizado se o Redis for escolhido para armazenar a cache)',
  'form.admin_services.cacheRootDirectory.label' => 'Diretório raiz da cache',
  'form.admin_services.cacheRootDirectory.help' => 'Escolher um diretório com permissões de escrita para armazenar os arquivos de cache',
  'form.admin_services.message.cacheRootDirectory.wrong' => 'O diretório não existe ou não tem permissões de escrita. É possível utilizar: %s',
  'form.admin_services.cacheExpiration.label' => 'Tempo(s) de expiração da cache no servidor',
  'form.admin_services.cacheExpiration.help' => '0 significa sem expiração, caso contrário dê um número inteiro em segundos (máx: 2592000 s = 30 días)',
  'form.admin_services.message.cacheExpiration.wrong' => 'O tempo de expiração da cache no servidor deve ser um número inteiro entre 0 e 2592000 segundos.',
  'form.admin_services.rootRepositories.label' => 'Pasta root dos repositórios',
  'form.admin_services.rootRepositories.help' => 'A pasta root dos repositórios pode limitar o diretório do Lizmap a 1 pasta. Se o caminho for definido aqui, não será possível definir o novo caminho para o diretório Lizmap.',
  'form.admin_services.relativeWMSPath.label' => 'O servidor utiliza caminhos relativos a partir pasta raiz?',
  'form.admin_services.requestProxy.label' => 'Utilizar um servidor proxy para fazer pedidos a serviços externos',
  'form.admin_services.requestProxy.enabled' => 'Utilizar um servidor proxy',
  'form.admin_services.requestProxy.disabled' => 'Sem proxy',
  'form.admin_services.requestProxyHost.label' => 'Anfitrião do servidor proxy',
  'form.admin_services.requestProxyPort.label' => 'Porta do servidor proxy',
  'form.admin_services.requestProxyType.label' => 'Tipo de proxy',
  'form.admin_services.requestProxyUser.label' => 'Login de acesso ao servidor proxy',
  'form.admin_services.requestProxyPassword.label' => 'Palavra-passe de acesso ao servidor proxy',
  'form.admin_services.requestProxyNotForDomain.label' => 'Domínios para os quais o proxy não será utilizado',
  'form.admin_services.debugMode.label' => 'Modo debug',
  'form.admin_services.debugMode.0.label' => 'Desativado',
  'form.admin_services.debugMode.1.label' => 'Registo',
  'form.admin_services.onlyMaps.label' => 'Só mapas',
  'form.admin_services.projectSwitcher.label' => 'Mostrar menu de projetos',
  'form.admin_services.defaultRepository.label' => 'Repositório padrão',
  'form.admin_services.defaultProject.label' => 'Projeto padrão',
  'form.admin_services.allowUserAccountRequests.label' => 'Permitir que os visitantes solicitem uma conta?',
  'form.admin_services.allowUserAccountRequests.help' => 'Se definido como sim, os visitantes podem preencher um formulário para solicitar uma conta. O endereço do remetente para e-mails deve ser definido.',
  'form.admin_services.adminContactEmail.label' => 'E-mail para onde enviar notificações',
  'form.admin_services.adminContactEmail.help' => 'Endereço de e-mail para o qual as notificações serão enviadas',
  'form.admin_services.adminSenderEmail.label' => 'Endereço do remetente dos e-mails',
  'form.admin_services.adminSenderEmail.help' => 'Endereço de e-mail utilizado para enviar e-mails. Se vazio, nenhum e-mail será enviado pelo Lizmap. É necessário confirmar se o servidor tem permissão para enviar e-mails com esse endereço.',
  'form.admin_services.adminSenderEmail.error.required' => 'É necessário um endereço de e-mail do remetente para enviar notificações ou pedidos de criação de conta',
  'form.admin_services.adminSenderName.label' => 'Nome do remetente dos e-mails',
  'form.admin_services.adminSenderName.help' => 'Nome do remetente utilizado para enviar e-mails.',
  'form.admin_services.googleAnalyticsID.label' => 'ID Google Analytics',
  'form.admin_services.googleAnalyticsID.help' => 'O ID Google Analytics é na forma \'UA-XXXX-Y\'. É chamado também "tracking ID".',
  'form.admin_services.submit.label' => 'Guardar',
  'form.admin_services.message.data.saved' => 'Os dados foram guardados.',
  'form.admin_section.h1.create' => 'Criar um repositório',
  'form.admin_section.h1.modify' => 'Modificar o repositório',
  'form.admin_section.repository.label' => 'ID',
  'form.admin_section.repository.label.label' => 'Etiqueta',
  'form.admin_section.repository.path.label' => 'Caminho da pasta local',
  'form.admin_section.repository.allowUserDefinedThemes.label' => 'Permitir temas/código javascript para este repositório',
  'form.admin_section.submit.label' => 'Guardar',
  'form.admin_section.data.label' => 'Configuração dos dados:',
  'form.admin_section.groups.label' => 'Permissões e grupos autorizados:',
  'form.admin_section.grouplist.label' => 'Lista de grupos',
  'form.admin_section.message.data.saved' => 'Os dados do repositório foram guardados.',
  'form.admin_section.message.configure.rights' => 'Agora é possível configurar as permissões para este repositório.',
  'form.admin_section.message.data.removed' => 'O repositório foi removido',
  'form.admin_section.message.data.removed.groups.concerned' => '(%s grupo(s) afetado(s))',
  'form.admin_section.message.data.removed.failed' => 'O repositório foi removido',
  'form.admin_section.message.repository.wrong' => 'O ID do repositório fornecido não existe.',
  'form.admin_section.message.path.wrong' => 'O caminho fornecido para o repositório não existe no servidor.',
  'form.admin_section.message.path.not_authorized' => 'O caminho fornecido para o repositório não está autorizado.',
  'menu.lizmap.theme.label' => 'Tema',
  'configuration.button.modify.theme.label' => 'Modificar',
  'form.admin_theme.h1' => 'Tema',
  'form.admin_theme.headerLogo.label' => 'Cabeçalho - logotipo',
  'form.admin_theme.headerLogo.help' => 'A imagem a ser utilizada para o logotipo do cabeçalho (máximo 200kb).',
  'form.admin_theme.headerLogoWidth.label' => 'Cabeçalho - largura do logotipo',
  'form.admin_theme.headerLogoWidth.help' => 'A largura ocupada pelo logotipo antes do título (em píxeis).',
  'form.admin_theme.headerBackgroundImage.label' => 'Cabeçalho - imagem de fundo',
  'form.admin_theme.headerBackgroundImage.help' => 'A imagem a ser utilizada para o fundo do cabeçalho (máximo 400kb).',
  'form.admin_theme.headerBackgroundColor.label' => 'Cabeçalho - cor de fundo',
  'form.admin_theme.headerBackgroundColor.help' => 'A cor de fundo do cabeçalho.',
  'form.admin_theme.headerTitleColor.label' => 'Cabeçalho - cor do título',
  'form.admin_theme.headerTitleColor.help' => 'A cor do título do cabeçalho.',
  'form.admin_theme.headerSubtitleColor.label' => 'Cabeçalho - cor do subtítulo',
  'form.admin_theme.headerSubtitleColor.help' => 'A cor do subtítulo do cabeçalho.',
  'form.admin_theme.menuBackgroundColor.label' => 'Barra de menu - cor de fundo',
  'form.admin_theme.menuBackgroundColor.help' => 'A cor do fundo da barra de menu esquerda.',
  'form.admin_theme.dockBackgroundColor.label' => 'Painéis - cor de fundo',
  'form.admin_theme.dockBackgroundColor.help' => 'A cor do fundo dos painéis.',
  'form.admin_theme.navbarColor.label' => 'Barra de navegação - cor de fundo',
  'form.admin_theme.navbarColor.help' => 'A cor de fundo da barra de navegação (ferramentas de zoom).',
  'form.admin_theme.additionalCss.label' => 'Propriedades CSS adicionais',
  'form.admin_theme.additionalCss.help' => 'É possível escrever quaisquer sintaxes CSS para substituir estilos do Lizmap (utilizadores avançados)',
  'theme.detail.title' => 'Configuração do Tema',
  'theme.button.remove.logo.label' => 'Eliminar',
  'theme.button.remove.logo.confirm.label' => 'Remover esta imagem (e utilizar uma padrão)?',
  'cache.repository.removed' => 'A cache do repositório %s foi esvaziada com sucesso.',
  'cache.layer.removed' => 'A cache da camada %s foi esvaziada com sucesso.',
  'cache.button.remove.repository.cache.confirm.label' => 'Esvaziar a cache deste repositório?',
  'cache.button.remove.repository.cache.label' => 'Esvaziar cache',
  'logs.counter.title' => 'Contagem de registos',
  'logs.detail.title' => 'Detalhe dos registos',
  'logs.counter.number.sentence' => 'linhas na tabela de contagem global de registos',
  'logs.detail.number.sentence' => 'linhas na tabela de detalhe dos registos',
  'logs.view.button' => 'Ver',
  'logs.export.button' => 'Exportar',
  'logs.empty.button' => 'Esvaziar',
  'logs.empty.confirm' => 'Esvaziar mesmo o conteúdo dos registos?',
  'logs.empty.ok' => 'A tabela de registos %s foi esvaziada com sucesso.',
  'logs.key' => 'Chave',
  'logs.counter' => 'Contagem',
  'logs.timestamp' => 'Data/hora',
  'logs.user' => 'Utilizador',
  'logs.content' => 'Conteúdo',
  'logs.repository' => 'Repositório',
  'logs.project' => 'Projeto',
  'logs.ip' => 'IP',
  'logs.first_page' => 'Início',
  'logs.previous_page' => 'Anterior',
  'logs.next_page' => 'Seguinte',
  'logs.email.subject' => 'Foi registado um novo item.',
  'logs.email.login.body' => 'Um utilizador iniciou sessão.',
  'logs.email.viewmap.body' => 'Um utilizador acedeu a um mapa.',
  'logs.email.print.body' => 'Um utilizador utilizou a ferramenta de impressão.',
  'logs.email.popup.body' => 'Um utilizador utilizou a ferramenta popup.',
  'logs.email.editionSaveFeature.body' => 'Um utilizador criou ou modificou um elemento.',
  'logs.email.editionDeleteFeature.body' => 'Um utilizador eliminou um elemento.',
  'logs.error.title' => 'Registo de erros',
  'logs.error.sentence' => 'Últimas linhas do ficheiro de registo de erros da aplicação Lizmap Web Client',
  'logs.error.file.too.big' => 'O ficheiro de registo de erros é demasiado grande. Considerar a utilização de rotação de registos para o Lizmap Web Client.',
  'logs.error.file.erase' => 'Apagar o ficheiro de registo de erros',
  'logs.error.file.erase.confirm' => 'Apagar mesmo o ficheiro de registo de erros?',
  'logs.error.file.erase.ok' => 'O ficheiro de registo de erros foi apagado com sucesso.',
  'upload.image.error.file.missing' => 'Ficheiro em falta',
  'upload.image.error.file.bigger' => 'O ficheiro de upload excede o limite máximo permitido',
  'upload.image.error.file.partially' => 'O ficheiro de upload só foi parcialmente carregado',
  'upload.image.error.file.none' => 'Não foi carregado nenhum ficheiro',
  'upload.image.error.missing.temp' => 'Pasta temporária em falta',
  'upload.image.error.file.onDisk' => 'Falha ao gravar o ficheiro no disco',
  'upload.image.error.file.invalid' => 'Ficheiro inválido',
  'upload.image.error.file.wrongType' => 'Tipo de ficheiro incorreto. Apenas são permitidas imagens',
  'upload.image.error.file.save' => 'Ocorreu um erro durante o processo de gravação',
) ?>