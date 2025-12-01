Arquitetura Fiscal Inteligente e Automatizada para TMS SaaS

1. Visão Geral e Princípios Fundamentais

O fluxo fiscal em uma transportadora é o coração da operação legal e logística. A emissão de CT-e (Conhecimento de Transporte Eletrônico) e MDF-e (Manifesto Eletrônico de Documentos Fiscais) não pode ser apenas uma tarefa; deve ser um processo fluido, automatizado e à prova de falhas.

Esta arquitetura foi desenhada sobre quatro pilares:

Proatividade (Inteligência Antecipada): O sistema deve prever e prevenir erros antes que eles aconteçam, em vez de apenas reagir a eles.

Assincronia (Velocidade e Resiliência): A interface do usuário nunca deve travar aguardando uma resposta de um serviço externo (Mitt/Sefaz). As operações fiscais rodam em segundo plano, e o sistema é notificado quando estão completas.

Automação (Menos Cliques, Mais Ação): Reduzir a intervenção manual ao mínimo, automatizando a emissão de documentos com base em gatilhos operacionais.

User-Centric (Foco no Usuário): Traduzir a complexidade fiscal em uma interface clara, com status visuais e mensagens de erro compreensíveis que guiam o usuário à solução.

2. O Fluxo Fiscal: Da Coleta à Entrega

Para desenhar o sistema, primeiro entendemos o fluxo de documentos:

Nota Fiscal (NF-e): O cliente (embarcador) emite uma NF-e para a mercadoria. O XML ou a chave desta nota é o ponto de partida. Nosso sistema não emite a NF-e, ele a consome.

Conhecimento de Transporte (CT-e): Para cada transporte que a transportadora realiza, ela DEVE emitir um CT-e. Este documento vincula a NF-e da mercadoria ao serviço de frete. Um CT-e é para um remetente e um destinatário.

Manifesto de Carga (MDF-e): Quando um veículo vai para a estrada, ele pode carregar mercadorias de vários CT-es diferentes. O MDF-e é o documento que agrupa todos os CT-es que estão naquele veículo para aquela viagem específica. Ele é obrigatório para a fiscalização em trânsito.

3. Arquitetura Proposta: O Ecossistema Fiscal

Propomos uma arquitetura orientada a eventos, com um "Núcleo Fiscal" central que orquestra todas as operações.

Componentes Chave:

A. O Núcleo Fiscal (Fiscal Core)

O que é: Uma classe de serviço (FiscalService.php) no Laravel. É o único ponto de entrada para qualquer ação fiscal. Nenhum controller ou componente fala diretamente com a Mitt.

Responsabilidades:

Pré-validação de Dados: Antes de enviar qualquer coisa para a Mitt, este serviço realiza um "check-up" completo nos dados (validação de CNPJ/CPF, CEP, dados da NFe, CFOP, etc.).

Orquestração de Eventos: Dispara eventos como CteIssuanceRequested ou MdfeIssuanceRequested.

Gerenciamento de Estado: Atualiza o status do documento fiscal no banco de dados (ex: pending, processing, authorized, error).

B. Sistema de Eventos e Filas do Laravel

O que é: Usaremos o sistema nativo do Laravel de Events, Listeners e Queues.

Como funciona:

A interface do usuário (Livewire) chama um método no FiscalService.

O FiscalService valida os dados e dispara um evento (ex: CteIssuanceRequested).

Um Listener (ex: ProcessCteIssuance) "ouve" este evento e despacha um Job (ex: SendCteToMittJob) para uma fila.

Benefício: A interface do usuário recebe uma resposta instantânea ("Seu CT-e está sendo processado.") e o trabalho pesado é feito em segundo plano, sem travar a tela.

C. O "Wrapper" da API Mitt (MittApiService.php)

O que é: Uma classe dedicada exclusivamente a se comunicar com a API da Mitt.

Responsabilidades:

Formatar os dados do nosso sistema (objetos Shipment, Route) para o formato JSON/XML que a Mitt espera.

Lidar com a autenticação da API.

Enviar a requisição HTTP.

Tratar as respostas HTTP iniciais (ex: 200 OK, 401 Unauthorized).

D. O Controlador de Webhooks (WebhookController.php)

O que é: Um endpoint seguro (/webhooks/mitt) que a Mitt chamará para nos notificar sobre o resultado final de uma emissão.

Como funciona:

Quando enviamos um pedido para a Mitt, informamos a URL deste webhook.

A Mitt processa o documento com a Sefaz e, quando termina, envia uma requisição POST para nossa URL com o resultado (autorizado, cancelado, erro, etc.) e os links para o PDF/XML.

Nosso controller recebe esses dados, valida a autenticidade da requisição e chama o FiscalService para atualizar o status final do documento no banco de dados.

4. Experiência do Usuário (UX): O Painel Fiscal

O usuário não interagirá com a complexidade acima. Ele verá uma interface limpa e responsiva:

Página de Detalhes da Carga (/shipments/{id}):

Um componente visual "Linha do Tempo Fiscal" mostrará o status em tempo real:

[✓] Dados Validados

[...] Processando Emissão com a Mitt... (ícone de carregamento)

[✓] CT-e Autorizado pela Sefaz (ícone de sucesso, com links para PDF/XML)

[X] Erro na Emissão (ícone de erro)

Ao clicar no erro, um modal se abre com uma mensagem clara e acionável: "Falha na emissão: O CNPJ do destinatário está inválido. [Clique aqui para corrigir o cadastro]".

Página de Rota (/routes/{id}):

Lista os CT-es vinculados.

Um botão "Fechar Rota e Emitir MDF-e" só fica habilitado quando todos os CT-es da rota estiverem autorizados.

A mesma "Linha do Tempo Fiscal" existirá para o status do MDF-e.

Automação em Ação:

Entrada da Carga: O usuário cadastra uma nova carga. No formulário, um campo permite importar o XML da NF-e do cliente, preenchendo 80% dos campos automaticamente.

Gatilho de Emissão (CT-e): Ao salvar a carga e movê-la para o status operacional "Pronto para Roteirizar", o sistema automaticamente dispara o evento CteIssuanceRequested. O usuário não precisa clicar em "Emitir CT-e".

Criação da Rota: O time de logística arrasta as cargas "Prontas para Roteirizar" para uma nova Rota.

Gatilho de Emissão (MDF-e): Quando a rota é marcada com o status "Pronta para Sair", o sistema verifica se todos os CT-es estão OK e dispara o evento MdfeIssuanceRequested automaticamente.

Esta arquitetura transforma o processo fiscal de uma tarefa manual e reativa para um fluxo de trabalho automatizado, inteligente e integrado à operação da transportadora, gerando enorme economia de tempo e redução de erros.