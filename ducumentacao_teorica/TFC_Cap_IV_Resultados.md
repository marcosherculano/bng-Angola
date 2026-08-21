# CAPÍTULO IV — APRESENTAÇÃO E ANÁLISE DOS RESULTADOS

## 4.1 Arquitectura do Sistema

A plataforma BNG Angola foi concebida segundo uma arquitectura em camadas, combinando um *backend* web monolítico (Laravel) com uma aplicação móvel (Flutter) que comunica via API RESTful. A arquitectura geral pode ser descrita em quatro camadas:

**Camada de Apresentação:**
- **Web:** Interfaces renderizadas pelo servidor utilizando o motor de *templates* Blade, com HTML5, CSS3 (Bootstrap 5) e JavaScript. As *views* estão organizadas por papel do utilizador (`resources/views/cliente/`, `resources/views/pharmacy/`, `resources/views/admin/`);
- **Móvel:** Aplicação Flutter com ecrãs nativos para Android e iOS, utilizando *widgets* Material Design e a biblioteca `flutter_map` para geolocalização.

**Camada de Controlo (Controladores):**
Os controladores estão organizados por domínio funcional:
- `Cliente\PedidosClienteController` — gestão de pedidos do cliente;
- `Cliente\BuscaMedicamentosController` — motor de busca;
- `Pharmacy\MedicinesController` — CRUD de medicamentos;
- `Pharmacy\MedicineTransfersController` — transferências de stock;
- `Pharmacy\PedidosFarmaciaController` — gestão de pedidos (farmácia);
- `Pharmacy\PaymentSettingsController` — configurações de pagamento;
- `Pharmacy\MensalidadesFarmaciaController` — mensalidades;
- `Admin\UsuariosAdminController` — gestão de utilizadores;
- `Admin\FarmaciasAdminController` — gestão de farmácias;
- `Admin\MensalidadesAdminController` — gestão de mensalidades;
- `Admin\DatabaseBackupsController` — backups;
- `Admin\ConfiguracoesAdminController` — configurações do sistema;
- `Api\AuthController` — autenticação da API;
- `Webhooks\YangoWebhookController` — integração Yango.

**Camada de Negócio (Modelos e Serviços):**
- 19 modelos Eloquent com relacionamentos, *casts* e regras de negócio;
- Serviço `NotificationService` para notificações *in-app*;
- Serviço `ActivityLogger` para auditoria;
- `DeliveryPartnerFactory` para abstracção de parceiros de transporte;
- Jobs assíncronos (`GenerateDatabaseBackupJob`, `RestoreDatabaseBackupJob`).

**Camada de Dados:**
- Base de dados MySQL 8.0 com 19 tabelas;
- Migrações Laravel para controlo de versão da estrutura;
- Armazenamento de ficheiros local (`storage/app/`) para comprovativos, documentos e *backups*.

**Tabela 5 — Estrutura das tabelas da base de dados**

| Modelo | Tabela | Função Principal |
|---|---|---|
| `User` | `users` | Utilizadores de todos os papéis |
| `Pharmacy` | `pharmacies` | Farmácias (normal e matriz) |
| `PharmacyBranch` | `pharmacy_branches` | Filiais de farmácias matriz |
| `Medicine` | `medicines` | Catálogo de medicamentos |
| `MedicineInventory` | `medicine_inventories` | Inventário polimórfico (farmácia ou filial) |
| `MedicineTransfer` | `medicine_transfers` | Registo de transferências matriz→filial |
| `Order` | `orders` | Pedidos de clientes |
| `OrderItem` | `order_items` | Itens de cada pedido |
| `OrderPayment` | `order_payments` | Comprovativos de pagamento |
| `OrderDelivery` | `order_deliveries` | Dados de entrega externa |
| `MonthlyFee` | `monthly_fees` | Mensalidades das farmácias |
| `PharmacyPaymentSetting` | `pharmacy_payment_settings` | Config. pagamento (farmácia) |
| `PharmacyBranchPaymentSetting` | `pharmacy_branch_payment_settings` | Config. pagamento (filial) |
| `DadosBancario` | `dados_bancarios` | Contas bancárias da plataforma |
| `Notification` | `notifications` | Notificações in-app |
| `ActivityLog` | `activity_logs` | Auditoria de acções |
| `DatabaseBackup` | `database_backups` | Registos de backups |
| `HomepageVideo` | `homepage_videos` | Vídeos da landing page |
| `SystemSetting` | `system_settings` | Configurações gerais |

## 4.2 Modelo de Dados

O modelo de dados foi projectado para suportar a complexidade dos relacionamentos entre entidades, utilizando chaves estrangeiras (*foreign keys*), relacionamentos polimórficos e índices para optimização de consultas.

**Relacionamentos principais:**

- Um `User` pode ter uma `Pharmacy` (1:1) ou ser responsável por uma `PharmacyBranch` (1:1);
- Uma `Pharmacy` do tipo *matrix* pode ter várias `PharmacyBranch` (1:N);
- Uma `Pharmacy` possui vários `Medicine` (1:N);
- Um `Medicine` pode ter vários `MedicineInventory` — relação polimórfica: o *owner* pode ser `Pharmacy` ou `PharmacyBranch` (`owner_type` + `owner_id`);
- Uma `Order` pertence a um `User` (cliente) e a uma `Pharmacy`, opcionalmente a uma `PharmacyBranch`;
- Uma `Order` possui um `OrderPayment` (1:1), uma `OrderDelivery` (1:1) e vários `OrderItem` (1:N);
- Uma `Pharmacy` possui vários `MonthlyFee` (1:N).

O uso de **relacionamentos polimórficos** no `MedicineInventory` permite que tanto farmácias quanto filiais mantenham inventários independentes do mesmo medicamento, com preços e disponibilidades distintas — funcionalidade essencial para o motor de busca e para as transferências de stock.

## 4.3 Funcionalidades Implementadas

### 4.3.1 Módulo Cliente

O módulo do cliente permite ao utilizador com papel `client` realizar as seguintes operações:

**a) Busca de medicamentos:**
O motor de busca (`BuscaMedicamentosController`) consulta a tabela `medicine_inventories` com *eager loading* das relações `medicine` e `owner`. Os filtros disponíveis incluem: pesquisa textual (nome, código de barras, categoria), província, categoria, disponibilidade e stock. Os resultados são ordenáveis por preço (ascendente/descendente), stock ou nome, e paginados em lotes de 20. O sistema filtra automaticamente apenas inventários de farmácias activas e filiais aprovadas.

**b) Criação de pedidos:**
O cliente selecciona um medicamento a partir dos resultados da busca, define a quantidade e escolhe o método de levantamento: presencial (`pickup`) ou transporte externo (`external_transport`). O sistema verifica em transacção atómica: a disponibilidade do medicamento, o estado da farmácia/filial, e o stock suficiente. O preço total é calculado como `quantidade × preço unitário`. Opcionalmente, o cliente pode agendar o levantamento com data e notas.

**c) Submissão de pagamento:**
Após a criação do pedido, o cliente submete um comprovativo de pagamento (PDF ou imagem, até 5 MB) com o método utilizado (IBAN, Express ou outro) e uma referência opcional. O sistema armazena o ficheiro de forma segura e notifica a farmácia.

**d) Acompanhamento de pedidos:**
O cliente pode visualizar todos os seus pedidos, consultar o estado actual (pendente, agendamento solicitado/confirmado, pago, pronto para levantamento, entrega solicitada/em curso, entregue, cancelado) e os detalhes da entrega externa. Um *endpoint* JSON com *polling* permite a actualização do estado em tempo real.

**e) Facturação:**
Para pedidos com pagamento confirmado, o cliente pode visualizar a factura online ou descarregá-la em formato PDF, gerada dinamicamente com o DomPDF.

**f) Cancelamento:**
O cliente pode cancelar pedidos nos estados `pending` ou `schedule_requested`, notificando automaticamente a farmácia.

### 4.3.2 Módulo Farmácia

O módulo da farmácia suporta três papéis: `pharmacy_normal` (farmácia independente), `pharmacy_matrix` (farmácia com filiais) e `pharmacy_branch` (filial).

**a) Gestão de medicamentos (CRUD):**
O controlador `MedicinesController` permite criar, listar, editar e remover medicamentos. A listagem suporta filtros por pesquisa, categoria, disponibilidade e stock baixo (≤ 5). Ao criar ou actualizar um medicamento, o sistema sincroniza automaticamente o `MedicineInventory` correspondente. A remoção é bloqueada se existirem pedidos associados.

**b) Transferência de stock (apenas matriz):**
O controlador `MedicineTransfersController` permite à farmácia matriz transferir quantidades de medicamentos para as suas filiais. A operação utiliza **transacções atómicas com bloqueio pessimista** (`lockForUpdate`) para garantir a integridade: o stock é deduzido da matriz e somado na filial numa única transacção. Se a filial não possuir o medicamento no seu inventário, o registo é criado automaticamente. Notificações de stock baixo são disparadas quando o stock fica ≤ 5.

**c) Gestão de pedidos:**
O controlador `PedidosFarmaciaController` implementa o fluxo completo:
- **Confirmar agendamento** — aceitar a data proposta pelo cliente;
- **Confirmar pagamento** — validar o comprovativo submetido (muda estado para `paid`);
- **Rejeitar pagamento** — recusar com motivo (permite nova submissão);
- **Marcar como pronto** — para pedidos presenciais após pagamento confirmado;
- **Solicitar entrega externa** — registar dados do parceiro de transporte;
- **Iniciar entrega** — transição para `delivery_in_progress`;
- **Actualizar dados da entrega** — motorista, telefone, ID externo;
- **Cancelar entrega** — reverter para `paid` com motivo;
- **Marcar como entregue** — com dedução automática de stock em transacção atómica;
- **Cancelar pedido** — em estados permitidos.

Cada acção gera notificações ao cliente (in-app e por e-mail) e registo de auditoria.

**d) Configurações de pagamento:**
Cada farmácia ou filial pode configurar os seus dados bancários (nome do banco, titular, IBAN, nº Express) e instruções de pagamento, que são apresentados ao cliente no momento da submissão do comprovativo.

**e) Mensalidades:**
Após o período de *trial* (30 dias), a farmácia deve pagar uma mensalidade calculada automaticamente:
- Farmácia Normal: 2.000 Kz (base);
- Farmácia Matriz: 2.700 Kz (base) + soma das mensalidades das filiais activas.
O sistema gera ciclos de 30 dias, permite a submissão de comprovativos e notifica os administradores.

**f) Gestão de filiais (apenas matriz):**
A farmácia matriz pode criar filiais com dados completos (nome, morada, coordenadas, horário, documento), associar um utilizador responsável e gerir o estado de cada filial.

### 4.3.3 Módulo Administrador

O módulo do administrador (`admin`) fornece visibilidade e controlo total sobre a plataforma:

**a) Painel (Dashboard):**
Apresenta KPIs em tempo real: utilizadores aprovados/pendentes/suspensos, farmácias activas/em trial, filiais pendentes, mensalidades por estado, e os 10 logs de actividade mais recentes.

**b) Gestão de utilizadores:**
Listagem com pesquisa e filtros (papel, estado). Acções: aprovar, suspender, bloquear, reactivar, eliminar. A eliminação implementa cascata completa — ao eliminar uma farmácia, são removidos os medicamentos, inventários, transferências, pedidos, filiais e utilizadores das filiais. Protecções: o admin não pode ser suspenso, bloqueado ou eliminado; não pode eliminar a si próprio.

**c) Gestão de farmácias:**
Activar/desactivar farmácias, ajustar mensalidade base personalizada, gerir documentos do alvará (upload/download).

**d) Gestão de filiais:**
Aprovar filiais pendentes, editar dados e utilizador, eliminar com cascata, descarregar alvará.

**e) Gestão de mensalidades:**
Listar por estado, descarregar comprovativos, aprovar (gera automaticamente o próximo ciclo e desbloqueia utilizador se bloqueado) ou rejeitar com motivo.

**f) Dados bancários da plataforma:**
Gerir as contas bancárias exibidas às farmácias para pagamento de mensalidades.

**g) Logs de actividade:**
Visualização completa dos logs de auditoria com paginação.

**h) Configurações do sistema:**
Nome do site, cores (primária/secundária), modo (claro/escuro), email e telefone de suporte. Gestão de vídeos da homepage: upload (MP4, até 200 MB), activar, eliminar.

**i) Backups da base de dados:**
Gerar backup (SQL ou ZIP, em background via Job), gerar backup completo (modo migração), descarregar backups, restaurar a partir de ficheiro (SQL/ZIP, em background).

### 4.3.4 Aplicação Móvel (Flutter)

A aplicação móvel complementar, desenvolvida em Flutter, disponibiliza as seguintes funcionalidades:

- **Autenticação:** Login e registo de utilizadores, comunicando com os *endpoints* `POST /api/auth/login` e `POST /api/auth/register`;
- **Listagem de farmácias:** Consulta do *endpoint* `GET /api/pharmacies` para apresentar as farmácias disponíveis;
- **Selecção de localização:** Ecrã com mapa interactivo (`flutter_map` + OpenStreetMap) para o utilizador definir a sua localização (latitude/longitude e província);
- **Perfil do utilizador:** Consulta dos dados do utilizador autenticado via `GET /api/me`.

A aplicação armazena o token de autenticação localmente e inclui-o no *header* `Authorization` de todos os pedidos à API.

### 4.3.5 Integração com Transporte Externo

A integração com o Yango (serviço de transporte sob demanda do grupo Yandex) implementa dois mecanismos:

**Registo manual:** A farmácia preenche os dados do transporte (parceiro, motorista, telefone, ID externo, preço estimado, notas) através de formulários na interface web. O sistema utiliza o padrão *Factory* (`DeliveryPartnerFactory`) para delegar a lógica ao *driver* adequado.

**Webhook automático:** O *endpoint* `POST /webhooks/yango` recebe notificações do Yango quando ocorrem eventos (motorista atribuído, recolha efectuada, entrega concluída). O *endpoint* é protegido por:
- Autenticação via *header* secreto (`X-Yango-Secret`, validado contra a variável de ambiente `YANGO_WEBHOOK_SECRET`);
- Limitação de taxa (30 pedidos por minuto);
- Exclusão cirúrgica da protecção CSRF (apenas esta rota);
- Logging seguro (apenas metadados, sem payload completo).

**Tabela 9 — Estados do pedido e transições permitidas**

| Estado Actual | Acção | Estado Seguinte | Actor |
|---|---|---|---|
| `pending` | Agendar levantamento | `schedule_requested` | Cliente |
| `schedule_requested` | Confirmar agendamento | `schedule_confirmed` | Farmácia |
| `pending` / `schedule_*` | Confirmar pagamento | `paid` | Farmácia |
| `paid` | Marcar pronto (presencial) | `ready_for_pickup` | Farmácia |
| `paid` | Solicitar entrega | `delivery_requested` | Farmácia |
| `delivery_requested` | Iniciar entrega | `delivery_in_progress` | Farmácia |
| `ready_for_pickup` / `delivery_in_progress` | Marcar entregue | `delivered` | Farmácia |
| `delivery_*` | Cancelar entrega | `paid` | Farmácia |
| `pending` / `schedule_requested` | Cancelar | `cancelled` | Cliente |
| Vários estados | Cancelar | `cancelled` | Farmácia |

## 4.4 Interfaces do Sistema

As interfaces do sistema foram desenvolvidas com foco na usabilidade, responsividade e clareza visual. As principais interfaces incluem:

- **Página inicial (landing page):** Apresentação da plataforma com vídeo promocional, informações sobre funcionalidades e acesso ao registo/login;
- **Busca de medicamentos:** Interface de pesquisa com campo de texto, filtros laterais (província, categoria, disponibilidade, stock), ordenação e paginação;
- **Detalhes do pedido (cliente):** Visualização do estado, itens, pagamento, dados de entrega e acções disponíveis;
- **Gestão de pedidos (farmácia):** Lista de pedidos recebidos com filtro por estado, acesso a detalhes e acções contextuais;
- **Dashboard administrativo:** Painel com cartões de KPIs, logs recentes e navegação para módulos de gestão;
- **Aplicação móvel:** Ecrãs de login, registo, lista de farmácias e mapa de selecção de localização.

> **Nota:** As capturas de ecrã das interfaces são apresentadas nas Figuras 8 a 16 e no Apêndice B.

## 4.5 Testes e Validação

A validação da plataforma foi realizada através de testes funcionais manuais, cobrindo os cenários críticos de cada módulo:

**Tabela 8 — Resultados dos testes de funcionalidade**

| ID | Cenário de Teste | Módulo | Resultado |
|---|---|---|---|
| T01 | Registo de novo cliente | Auth | Aprovado |
| T02 | Registo de nova farmácia (normal) | Auth | Aprovado |
| T03 | Login com credenciais válidas (web) | Auth | Aprovado |
| T04 | Login com credenciais inválidas (rate limit) | Auth | Aprovado (bloqueio após 5 tentativas) |
| T05 | Login via API (token Sanctum) | API | Aprovado |
| T06 | Busca de medicamento por nome | Cliente | Aprovado |
| T07 | Busca com filtro por província | Cliente | Aprovado |
| T08 | Criação de pedido (presencial) | Cliente | Aprovado |
| T09 | Criação de pedido (transporte externo) | Cliente | Aprovado |
| T10 | Submissão de comprovativo de pagamento | Cliente | Aprovado |
| T11 | Cancelamento de pedido pelo cliente | Cliente | Aprovado |
| T12 | Download de factura em PDF | Cliente | Aprovado |
| T13 | Criação de medicamento | Farmácia | Aprovado |
| T14 | Transferência de stock matriz→filial | Farmácia | Aprovado |
| T15 | Confirmação de pagamento (farmácia) | Farmácia | Aprovado |
| T16 | Rejeição de pagamento com motivo | Farmácia | Aprovado |
| T17 | Marcar pedido como entregue (dedução de stock) | Farmácia | Aprovado |
| T18 | Solicitar e iniciar entrega externa | Farmácia | Aprovado |
| T19 | Cancelar entrega externa | Farmácia | Aprovado |
| T20 | Aprovação de utilizador (admin) | Admin | Aprovado |
| T21 | Suspensão e reactivação de utilizador | Admin | Aprovado |
| T22 | Eliminação de farmácia com cascata | Admin | Aprovado |
| T23 | Aprovação de mensalidade e geração de ciclo | Admin | Aprovado |
| T24 | Geração e download de backup | Admin | Aprovado |
| T25 | Restauro de backup | Admin | Aprovado |
| T26 | Recepção de webhook Yango (válido) | Webhook | Aprovado |
| T27 | Recepção de webhook Yango (secreto inválido) | Webhook | Aprovado (rejeitado com 403) |
| T28 | Notificação in-app ao cliente | Transversal | Aprovado |
| T29 | Registo de actividade (audit log) | Transversal | Aprovado |
| T30 | Acesso não autorizado (role diferente) | Segurança | Aprovado (redireccionamento) |

Todos os 30 cenários de teste foram executados com sucesso, confirmando que as funcionalidades implementadas operam de acordo com os requisitos definidos.

## 4.6 Segurança Implementada

### 4.6.1 Mitigação de Vulnerabilidades OWASP

O sistema implementa medidas de segurança para mitigar as vulnerabilidades críticas identificadas no OWASP Top 10 (2021):

**A01:2021 — Broken Access Control:**
- Implementação de controlo de acesso baseado em papéis (*RBAC*) através do *middleware* `CheckRole`;
- Cinco papéis definidos: `admin`, `client`, `pharmacy_normal`, `pharmacy_matrix`, `pharmacy_branch`;
- Verificação de autorização em cada controlador antes de executar acções sensíveis.

**A02:2021 — Cryptographic Failures:**
- Palavras-passe armazenadas com hashing Bcrypt (via `Hash::make`);
- Tokens de API gerados pelo Laravel Sanctum com entropia criptográfica adequada;
- Configuração preparada para HTTPS (variável `SESSION_SECURE_COOKIE`).

**A03:2021 — Injection:**
- Uso consistente do Eloquent ORM que utiliza *prepared statements* automaticamente;
- Validação rigorosa de todos os dados de entrada através das regras de validação do Laravel;
- Nenhuma query SQL directa concatenada com dados de utilizador.

**A05:2021 — Security Misconfiguration:**
- Configuração de CORS restritiva (origens permitidas configuráveis via `CORS_ALLOWED_ORIGINS`);
- Protecção CSRF activa em todas as rotas web via `VerifyCsrfToken`;
- Exclusão cirúrgica apenas para o *endpoint* do *webhook* Yango.

**A07:2021 — Identification and Authentication Failures:**
- Limitação de taxa (*rate limiting*) nos *endpoints* de autenticação (5 tentativas por 8 minutos);
- Política de palavras-passe exige mínimo de 6 caracteres sem espaços;
- Tokens de acesso pessoais com expiração configurável.

**A09:2021 — Security Logging and Monitoring Failures:**
- Serviço de auditoria (`ActivityLogger`) que regista acções sensíveis;
- Registo de identificação do utilizador, IP e *timestamp* para cada acção auditada.

### 4.6.2 Protecção CSRF e XSS

**CSRF (Cross-Site Request Forgery):**
- O Laravel gera automaticamente tokens CSRF para cada sessão;
- Os formulários HTML incluem o campo `@csrf` que injecta o token;
- O *middleware* `VerifyCsrfToken` valida o token em todos os pedidos POST/PUT/DELETE;
- Exclusão apenas para o *endpoint* do *webhook* Yango, que utiliza autenticação via *header* secreto.

**XSS (Cross-Site Scripting):**
- O motor de *templates* Blade escapa automaticamente todo o conteúdo por defeito (`{{ $var }}`);
- Para conteúdo HTML seguro, utiliza-se a directiva `{!! $var !!}` de forma controlada;
- Validação de *uploads* de ficheiros (comprovativos, documentos) para garantir que são ficheiros válidos.

### 4.6.3 Protecção contra SQL Injection

A protecção contra SQL Injection é garantida através de múltiplas camadas:

1. **Eloquent ORM:** Todas as queries são construídas através do Eloquent, que utiliza *prepared statements* automaticamente;

2. **Query Builder:** Quando utilizado, o Query Builder também utiliza *prepared statements*;

3. **Validação de entrada:** Todos os dados de entrada são validados antes de serem utilizados em queries;

4. **Tipagem forte:** O Laravel aplica tipagem automática aos dados baseada na definição das colunas na base de dados.

Não foram identificadas vulnerabilidades de SQL Injection nos testes de segurança realizados.

## 4.7 Geração de PDF com DomPDF

O sistema utiliza a biblioteca **DomPDF** (versão 2.0, integrada via pacote `barryvdh/laravel-dompdf`) para a geração dinâmica de facturas em formato PDF. A implementação inclui:

- **Geração on-demand:** As facturas são geradas no momento do pedido, sem armazenamento persistente;
- **Template Blade:** O layout da factura é definido num template Blade (`resources/views/orders/invoice.blade.php`);
- **Inserção de dados dinâmicos:** O template recebe os dados do pedido, cliente, farmácia e itens;
- **Configuração de papel:** Formato A4 com orientação retrato;
- **Download:** O PDF é gerado e enviado directamente para o navegador do utilizador.

A biblioteca DomPDF foi escolhida pela sua integração nativa com o Laravel, suporte para CSS e facilidade de utilização comparativamente a alternativas como TCPDF ou FPDF.

## 4.8 Estrutura para Testes de Desempenho

### 4.8.1 Metodologia Proposta

Para avaliar a escalabilidade do motor de pesquisa em cenários de produção, foi definida uma estrutura de testes de desempenho com três volumes de dados:

**Tabela 12 — Estrutura de testes de desempenho propostos**

| Cenário | Volume de Registos | Medicamentos | Inventários | Farmácias | Filiais | Métricas a Medir |
|---|---|---|---|---|---|---|
| **C1 - Pequeno** | 10.000 | 500 | 10.000 | 50 | 20 | Tempo de resposta (ms), Uso de CPU, Uso de memória |
| **C2 - Médio** | 100.000 | 5.000 | 100.000 | 200 | 100 | Tempo de resposta (ms), Uso de CPU, Uso de memória |
| **C3 - Grande** | 1.000.000 | 50.000 | 1.000.000 | 1.000 | 500 | Tempo de resposta (ms), Uso de CPU, Uso de memória |

### 4.8.2 Resultados Esperados (A Medir)

> **Nota:** Os testes de carga não foram executados no âmbito deste trabalho. Os valores abaixo representam a estrutura de medição a ser preenchida quando os testes forem realizados.

**Tabela 13 — Resultados de desempenho (a medir)**

| Cenário | Tempo de Resposta Médio (ms) | Tempo de Resposta P95 (ms) | Uso de CPU Médio (%) | Uso de Memória Médio (MB) | Observações |
|---|---:|---:|---:|---:|---|
| C1 - Pequeno (10k) | [A MEDIR] | [A MEDIR] | [A MEDIR] | [A MEDIR] | - |
| C2 - Médio (100k) | [A MEDIR] | [A MEDIR] | [A MEDIR] | [A MEDIR] | - |
| C3 - Grande (1M) | [A MEDIR] | [A MEDIR] | [A MEDIR] | [A MEDIR] | - |

**Métricas adicionais a medir:**
- Taxa de *throughput* (pedidos por segundo);
- Latência sob carga concorrente (10, 50, 100 utilizadores simultâneos);
- Degradação de desempenho com o aumento do volume de dados;
- Impacto da adição de índices FULLTEXT (quando implementado).

### 4.8.3 Ferramentas Recomendadas

Para a execução dos testes de desempenho, recomendam-se as seguintes ferramentas:

- **Apache JMeter:** Ferramenta open-source para testes de carga e desempenho;
- **Laravel Telescope:** Ferramenta de depuração e monitorização do Laravel;
- **MySQL Slow Query Log:** Identificação de queries lentas na base de dados;
- **Blackfire:** Profiling de performance de aplicações PHP.

## 4.9 Trabalhos Futuros Identificados

Com base na análise da implementação actual, foram identificados os seguintes trabalhos futuros:

### 4.9.1 Implementação de Índices FULLTEXT

**Estado:** Não implementado
**Justificativa:** O motor de pesquisa actual utiliza o operador `LIKE`, que não é optimizado para grandes volumes de dados. A implementação de índices FULLTEXT nos campos `name`, `barcode` e `category` da tabela `medicines` permitiria:
- Pesquisa textual com relevância;
- Suporte a operadores booleanos (AND, OR, NOT);
- Melhor desempenho em grandes volumes de dados;
- Pesquisa de sinónimos e correcção de erros (com configuração adicional).

**Impacto:** Alto — melhoria directa da experiência do utilizador e escalabilidade do sistema.

### 4.9.2 Implementação de Cache

**Estado:** Não implementado
**Justificativa:** O sistema não utiliza mecanismos de cache (Redis, Memcached). A implementação de cache permitiria:
- Redução da carga na base de dados para dados frequentemente acedidos (listas de províncias, categorias, configurações);
- Melhoria do tempo de resposta para operações de leitura;
- Escalabilidade horizontal através de cache distribuído.

**Recomendação:** Implementar Redis para cache de:
- Listas de províncias e categorias (TTL: 1 hora);
- Dados de farmácias activas (TTL: 5 minutos);
- Configurações do sistema (TTL: 30 minutos).

**Impacto:** Médio — melhoria de desempenho, mas não crítica para funcionamento básico.

### 4.9.3 Expansão da API Móvel

**Estado:** Parcialmente implementado
**Justificativa:** A aplicação Flutter actual implementa apenas autenticação, listagem de farmácias e selecção de localização. A expansão da API móvel permitiria:
- Busca completa de medicamentos na aplicação móvel;
- Criação de pedidos directamente no dispositivo móvel;
- Submissão de comprovativos de pagamento via câmara;
- Acompanhamento em tempo real do estado dos pedidos;
- Notificações *push* para actualizações de pedidos.

**Endpoints a implementar:**
- `GET /api/medicines/search` — Busca de medicamentos;
- `POST /api/orders` — Criação de pedidos;
- `POST /api/orders/{id}/payment` — Submissão de pagamento;
- `GET /api/orders` — Listagem de pedidos do utilizador;
- `GET /api/orders/{id}/invoice` — Download de factura.

**Impacto:** Alto — expansão significativa da funcionalidade móvel.

---
<div style="page-break-after: always;"></div>

## 4.10 Diagramas Técnicos e Interfaces Funcionais

### 4.10.1 Diagrama de Entidade-Relacionamento (DER) Detalhado

> **Figura 7 — Diagrama Entidade-Relacionamento (DER) detalhado do sistema**

[INSERIR DIAGRAMA DER DETALHADO AQUI]

**Legenda:** O diagrama DER detalhado mostra todas as 19 tabelas do sistema, incluindo tabelas de suporte (notifications, activity_logs, database_backups, system_settings, monthly_fees, dados_bancarios, pharmacy_payment_settings, pharmacy_branch_payment_settings). Os relacionamentos polimórficos estão representados com linhas tracejadas. As chaves estrangeiras estão indicadas com o símbolo de chave (FK).

---
<div style="page-break-after: always;"></div>

### 4.10.2 Diagrama de Fluxo de Pedido

> **Figura 8 — Diagrama de fluxo do processo de criação e entrega de pedido**

[INSERIR DIAGRAMA DE FLUXO AQUI]

**Legenda:** O diagrama de fluxo ilustra o processo completo desde a busca de medicamentos pelo cliente até à entrega final, incluindo as decisões (diamantes), acções (rectângulos) e actores (cliente, farmácia, sistema). Os caminhos alternativos (cancelamento, rejeição de pagamento) estão representados.

---
<div style="page-break-after: always;"></div>

### 4.10.3 Interface de Busca de Medicamentos

> **Figura 9 — Interface de busca de medicamentos com filtros activos**

[INSERIR CAPTURA DE ECRÃ AQUI]

**Legenda:** A interface de busca apresenta o campo de pesquisa textual, filtros laterais (província, categoria, disponibilidade, stock), opções de ordenação (preço, stock, nome) e paginação. Os resultados são apresentados em cards com informações do medicamento, farmácia, preço e disponibilidade.

---
<div style="page-break-after: always;"></div>

### 4.10.4 Interface de Gestão de Pedidos (Farmácia)

> **Figura 10 — Interface de gestão de pedidos da farmácia**

[INSERIR CAPTURA DE ECRÃ AQUI]

**Legenda:** A interface de gestão de pedidos da farmácia apresenta a lista de pedidos recebidos com filtro por estado, acesso a detalhes de cada pedido e acções contextuais (confirmar pagamento, marcar pronto, solicitar entrega, etc.). O estado actual de cada pedido é visualmente destacado.

---
<div style="page-break-after: always;"></div>

### 4.10.5 Interface do Painel Administrativo

> **Figura 11 — Painel administrativo com KPIs**

[INSERIR CAPTURA DE ECRÃ AQUI]

**Legenda:** O painel administrativo apresenta indicadores-chave de desempenho (KPIs) em tempo real: utilizadores aprovados/pendentes/suspensos, farmácias activas/em trial, filiais pendentes, mensalidades por estado, e os 10 logs de actividade mais recentes. A navegação para os módulos de gestão é feita através do menu lateral.
