# CAPÍTULO II — FUNDAMENTAÇÃO TEÓRICA

## 2.1 Saúde Digital e Acesso a Medicamentos em Países em Desenvolvimento

A saúde digital, definida pela OMS (2021) como o uso de tecnologias digitais para melhorar a saúde, abrange um amplo espectro de aplicações que vão desde registos electrónicos de saúde até plataformas de telemedicina e sistemas de gestão da cadeia de abastecimento de medicamentos.

Nos países em desenvolvimento, a saúde digital apresenta-se como uma oportunidade para superar barreiras estruturais que historicamente têm limitado o acesso da população a serviços de saúde de qualidade (Agarwal et al., 2020). A penetração crescente de dispositivos móveis e o alargamento do acesso à Internet — mesmo que de forma desigual — criam condições para a implementação de soluções digitais que podem transformar a forma como os cidadãos acedem a medicamentos.

Segundo a OMS (2020), o acesso a medicamentos essenciais continua a ser um desafio global, com aproximadamente dois mil milhões de pessoas sem acesso adequado. Este problema é particularmente grave na África Subsariana, onde factores como a fragilidade das infraestruturas logísticas, a escassez de farmácias em áreas rurais e a fragmentação da cadeia de abastecimento agravam a situação (Wirtz et al., 2022).

Cameron et al. (2022) identificaram quatro dimensões fundamentais do acesso a medicamentos: disponibilidade física, acessibilidade financeira, aceitabilidade e qualidade. As soluções digitais podem actuar positivamente em todas estas dimensões, particularmente na disponibilidade — ao permitir a localização de medicamentos em tempo real — e na acessibilidade — ao facilitar a comparação de preços e reduzir custos de deslocação.

## 2.2 Farmácias Comunitárias e o Contexto Angolano

As farmácias comunitárias desempenham um papel central nos sistemas de saúde, funcionando como o ponto de contacto mais acessível entre a população e os serviços farmacêuticos (Moullin et al., 2020). Em Angola, as farmácias comunitárias são reguladas pelo Decreto Presidencial n.º 191/18, de 8 de Agosto, que estabelece o regime jurídico do exercício da actividade farmacêutica no país.

De acordo com dados do Ministério da Saúde de Angola (2021), o país contava com aproximadamente 1.200 farmácias registadas, das quais a maioria se concentrava na província de Luanda. Esta concentração reflecte as assimetrias regionais do país e representa um desafio para as populações residentes fora dos grandes centros urbanos.

O sector farmacêutico angolano enfrenta desafios específicos que incluem: a dependência de importações para a maioria dos medicamentos consumidos; a volatilidade cambial que afecta os preços; a existência de um mercado informal paralelo; e a escassa adopção de tecnologias de informação para a gestão das farmácias (Oliveira & Santos, 2020). A maior parte das farmácias comunitárias ainda opera com sistemas de gestão rudimentares — frequentemente baseados em registos em papel ou folhas de cálculo — sem interconexão entre estabelecimentos ou com os cidadãos.

Este cenário cria um terreno fértil para a introdução de soluções tecnológicas que possam, simultaneamente, profissionalizar a gestão das farmácias e melhorar a experiência dos cidadãos no acesso a medicamentos.

## 2.3 Transformação Digital no Sector Farmacêutico

A transformação digital no sector farmacêutico é um fenómeno global que tem vindo a acelerar, impulsionado pela pandemia de COVID-19 e pela crescente adopção de tecnologias digitais por parte dos consumidores (McKinsey & Company, 2020).

Segundo Schwab (2019), a chamada Quarta Revolução Industrial caracteriza-se pela fusão de tecnologias que esbate as fronteiras entre os domínios físico, digital e biológico. No sector farmacêutico, esta revolução manifesta-se através de múltiplas inovações:

- **Plataformas de *e-pharmacy*:** Sistemas que permitem a pesquisa, encomenda e entrega de medicamentos por via digital. Exemplos internacionais incluem o PillPack (Amazon), o Netmeds (Índia) e o mPharma (África) (Aitken & Kleinrock, 2019);
- **Gestão digital de inventário:** Sistemas que permitem o rastreamento em tempo real do stock de medicamentos, com alertas automáticos para situações de ruptura ou excesso (Rossetti et al., 2021);
- **Integração logística:** Soluções que conectam farmácias a parceiros de transporte para viabilizar a entrega ao domicílio, utilizando APIs e *webhooks* para comunicação em tempo real (Mangiaracina et al., 2019);
- **Aplicações móveis:** Apps que colocam os serviços farmacêuticos na palma da mão do utilizador, tirando partido da penetração crescente de *smartphones* (Aitken & Kleinrock, 2019).

No contexto africano, iniciativas como o mPharma (Gana/Nigéria/Quénia) demonstraram a viabilidade de plataformas digitais farmacêuticas em mercados com desafios semelhantes aos de Angola, incluindo infraestruturas limitadas, pagamentos predominantemente manuais e cadeias logísticas fragmentadas (Drilvon, 2021).

## 2.4 Plataformas Web e Arquitectura MVC

Uma plataforma web é um sistema de *software* acessível através de um navegador (*browser*) que permite a interacção entre utilizadores e serviços disponibilizados na Internet ou numa rede local (Sommerville, 2019). As plataformas web modernas são tipicamente construídas sobre arquitecturas que promovem a separação de responsabilidades, a reutilização de código e a manutenibilidade.

O padrão arquitectural **MVC (*Model-View-Controller*)**, proposto originalmente por Trygve Reenskaug em 1979 e amplamente adoptado no desenvolvimento web, organiza a aplicação em três componentes interligados (Garcia & Nguyen, 2023):

- **Model (Modelo):** Representa a lógica de negócio e os dados da aplicação. No contexto do projecto BNG Angola, os modelos incluem entidades como `User`, `Pharmacy`, `PharmacyBranch`, `Medicine`, `MedicineInventory`, `Order`, `OrderPayment` e `OrderDelivery`, cada uma com as suas regras de validação e relacionamentos;
- **View (Visão):** Responsável pela apresentação dos dados ao utilizador. No projecto, as *views* são implementadas com o motor de *templates* Blade do Laravel, utilizando HTML, CSS e JavaScript;
- **Controller (Controlador):** Actua como intermediário entre o Model e a View, processando os pedidos do utilizador, invocando a lógica de negócio e devolvendo a resposta apropriada. O projecto implementa controladores separados por domínio funcional e papel do utilizador (ex.: `PedidosClienteController`, `PedidosFarmaciaController`, `UsuariosAdminController`).

A principal vantagem do MVC reside na separação de responsabilidades (*Separation of Concerns*), que facilita a manutenção, o teste e a evolução independente de cada componente (Bass et al., 2021).

## 2.5 Framework Laravel e Ecossistema PHP

O **Laravel** é um *framework* de desenvolvimento web em PHP, criado por Taylor Otwell em 2011, que se tornou o *framework* PHP mais utilizado no mundo (Otwell, 2023). A sua popularidade deve-se a uma combinação de elegância sintáctica, funcionalidades robustas e um ecossistema rico de pacotes e ferramentas.

### 2.5.1 Comparação com Alternativas: Laravel vs Django

Para fundamentar cientificamente a escolha do Laravel, procede-se a uma comparação com o **Django** (Python), uma das alternativas mais relevantes no desenvolvimento web moderno.

**Tabela 2 — Comparação técnica entre Laravel e Django**

| Critério | Laravel (PHP) | Django (Python) | Justificação da Escolha |
|---|---|---|---|
| **Curva de aprendizagem** | Moderada — sintaxe expressiva, documentação extensa | Moderada a elevada — conceitos mais abstractos | Laravel oferece uma sintaxe mais próxima do paradigma imperativo, facilitando a adopção rápida |
| **ORM** | Eloquent — sintaxe fluente, relações intuitivas | Django ORM — poderoso mas mais verboso | Eloquent permite definição de relações de forma mais concisa e legível |
| **Ecossistema de pacotes** | Composer — >400.000 pacotes, foco em web | PyPI — >500.000 pacotes, mais generalista | Composer está mais orientado para desenvolvimento web específico |
| **Performance** | PHP 8.2 com JIT — excelente para I/O | Python — interpretado, mais lento em I/O | PHP 8.2 com JIT oferece melhor desempenho para aplicações web I/O-bound |
| **Hosting** | Amplamente disponível, baixo custo | Requer configuração mais específica | Hosting PHP é omnipresente e económico em Angola |
| **Comunidade angolana** | Crescente, muitos recursos em português | Limitada | Facilita suporte e manutenção local |
| **Maturidade do ecossistema** | Laravel 8/10 — muito maduro, estável | Django 4.x — maduro, mas menos focado em APIs modernas | Laravel tem ferramentas nativas para APIs (Sanctum, Passport) |

**Análise crítica:**

A escolha do Laravel sobre Django fundamenta-se em três factores científicos principais:

1. **Adequação ao contexto angolano:** O PHP tem penetração significativa no mercado de trabalho angolano e o hosting PHP é amplamente disponível a baixo custo, factor crítico para a sustentabilidade do projecto (Oliveira & Santos, 2020). O Python, apesar da sua popularidade global, requer infraestruturas de hosting mais especializadas e dispendiosas.

2. **Desempenho em aplicações I/O-bound:** Aplicações web de gestão de inventário e pedidos são predominantemente I/O-bound (acesso à base de dados, geração de PDF, comunicação com APIs externas). O PHP 8.2 com compilação JIT (*Just-In-Time*) oferece melhor desempenho neste tipo de cargas de trabalho comparativamente ao Python, que é interpretado (The PHP Group, 2023).

3. **Ferramentas nativas para APIs REST:** O Laravel inclui ferramentas nativas como o Sanctum para autenticação de APIs, o que elimina a dependência de bibliotecas de terceiros e reduz a superfície de ataque em termos de segurança (Otwell, 2023). O Django requer integração com frameworks adicionais como Django REST Framework e DRF-simplejwt, aumentando a complexidade de manutenção.

**Conclusão:** A escolha do Laravel representa uma decisão cientificamente fundamentada que equilibra desempenho, adequação ao contexto local, facilidade de manutenção e segurança.

As características do Laravel relevantes para o projecto BNG Angola incluem:

- **Eloquent ORM:** Sistema de mapeamento objecto-relacional que permite interagir com a base de dados utilizando objectos PHP em vez de *queries* SQL directas. No projecto, todos os modelos (19 tabelas) utilizam o Eloquent, incluindo relacionamentos polimórficos para o inventário de medicamentos (Otwell, 2023);
- **Blade Templates:** Motor de *templates* que permite a criação de *views* dinâmicas com herança de *layouts*, componentes reutilizáveis e directivas de controlo (Stauffer, 2019);
- **Middleware:** Mecanismo de filtragem de pedidos HTTP que permite a aplicação transversal de funcionalidades como autenticação, autorização, limitação de taxa (*rate limiting*) e protecção CSRF (Stauffer, 2019);
- **Laravel Sanctum:** Pacote de autenticação que fornece um sistema simples de autenticação baseado em tokens para APIs e SPAs (*Single-Page Applications*). No projecto, o Sanctum é utilizado para autenticar a aplicação móvel Flutter (Otwell, 2023);
- **Jobs e Filas:** Sistema de processamento assíncrono que permite a execução de tarefas em segundo plano, como a geração de *backups* da base de dados (Stauffer, 2019);
- **Migrações e *Seeds*:** Sistema de controlo de versão para a estrutura da base de dados, permitindo a criação, alteração e reversão de tabelas de forma programática (Otwell, 2023).

O PHP, na sua versão 8.2, traz melhorias significativas de desempenho, tipos mais rigorosos e funcionalidades como enumerações (*enums*), fibras (*fibers*) e propriedades somente leitura (*readonly*), que contribuem para a qualidade e robustez do código (The PHP Group, 2023).

## 2.6 Desenvolvimento Móvel com Flutter

O **Flutter** é um *toolkit* de desenvolvimento de interfaces de utilizador multiplataforma, criado pela Google em 2017, que permite a construção de aplicações nativas para dispositivos móveis (Android e iOS), web e *desktop* a partir de uma única base de código em Dart (Google, 2023).

### 2.6.1 Comparação com Alternativas: Flutter vs React Native

Para justificar cientificamente a escolha do Flutter, procede-se a uma comparação com o **React Native** (Facebook/Meta), a principal alternativa no desenvolvimento móvel multiplataforma.

**Tabela 3 — Comparação técnica entre Flutter e React Native**

| Critério | Flutter (Dart) | React Native (JavaScript) | Justificação da Escolha |
|---|---|---|---|
| **Arquitectura de renderização** | Própria (Skia) — renderização directa | Bridge nativa — depende de componentes nativos | Renderização própria garante consistência visual entre plataformas |
| **Performance** | Próxima de nativa — compilação AOT | Próxima de nativa — mas com bridge overhead | Flutter elimina a bridge, reduzindo latência |
| **Hot Reload** | Instantâneo — milissegundos | Rápido — mas com bridge | Flutter oferece ciclo de desenvolvimento mais rápido |
| **Tamanho da aplicação** | Moderado (~15-20 MB base) | Menor (~10-15 MB base) | Diferença marginal para o contexto angolano |
| **Ecossistema de mapas** | flutter_map + OSM — gratuito, open-source | react-native-maps — depende de Google Maps (custo) | flutter_map elimina custos de licenciamento |
| **Curva de aprendizagem** | Moderada — Dart é similar a Java/C# | Baixa para desenvolvedores web — JavaScript | Similar, mas Dart oferece tipagem estática (vantagem para manutenção) |
| **Comunidade** | Crescente rapidamente, suporte Google | Madura, maior comunidade | Suporte Google garante longevidade e evolução contínua |

**Análise crítica:**

A escolha do Flutter sobre React Native fundamenta-se em três factores científicos:

1. **Independência de serviços proprietários de mapas:** O React Native depende predominantemente do Google Maps SDK, que incorre em custos de licenciamento significativos após determinado volume de utilização. O Flutter, através da biblioteca `flutter_map` com OpenStreetMap, permite a utilização de mapas gratuitos e open-source, factor crítico para a sustentabilidade económica do projecto em Angola (Windmill & Chitty, 2020).

2. **Consistência visual e performance:** A arquitectura de renderização própria do Flutter (Skia) garante que a aplicação tenha aparência e comportamento idênticos em Android e iOS, eliminando inconsistências que ocorrem frequentemente no React Native devido à dependência de componentes nativos específicos de cada plataforma (Google, 2023).

3. **Tipagem estática e manutenibilidade:** A linguagem Dart, utilizada pelo Flutter, oferece tipagem estática, o que facilita a detecção de erros em tempo de compilação e melhora a manutenibilidade do código a longo prazo. O JavaScript, utilizado pelo React Native, é dinamicamente tipado, o que pode levar a erros em tempo de execução mais difíceis de depurar (Windmill & Chitty, 2020).

**Conclusão:** A escolha do Flutter representa uma decisão cientificamente fundamentada que privilegia a independência de serviços proprietários, a consistência visual e a manutenibilidade do código, factores críticos para o contexto angolano.

As características do Flutter relevantes para o projecto incluem:

- **Desenvolvimento multiplataforma:** Uma única base de código gera aplicações para Android e iOS, reduzindo significativamente o tempo e o custo de desenvolvimento (Google, 2023);
- **Widgets reutilizáveis:** A interface é construída através de composição de *widgets*, promovendo a reutilização e a consistência visual (Windmill & Chitty, 2020);
- **Hot Reload:** Funcionalidade que permite visualizar alterações no código em tempo real, acelerando o ciclo de desenvolvimento (Google, 2023);
- **Integração com APIs REST:** O pacote `http` (ou `dio`) permite a comunicação com *backends* através de pedidos HTTP, suportando autenticação por tokens, *headers* personalizados e tratamento de erros (Windmill & Chitty, 2020).

No projecto BNG Angola, a aplicação Flutter funciona como um cliente da API RESTful fornecida pelo *backend* Laravel, permitindo que os utilizadores acedam às funcionalidades da plataforma a partir dos seus dispositivos móveis. A aplicação utiliza a biblioteca `flutter_map` com OpenStreetMap para a visualização e selecção de localizações geográficas, eliminando a dependência de serviços proprietários como o Google Maps.

## 2.7 API RESTful e Autenticação Baseada em Tokens

Uma **API RESTful** (*Representational State Transfer*) é um estilo arquitectural para sistemas distribuídos que utiliza os métodos do protocolo HTTP (GET, POST, PUT, DELETE) para realizar operações sobre recursos identificados por URIs (*Uniform Resource Identifiers*) (Doglio, 2022).

Os princípios fundamentais de uma API REST incluem (Pastore, 2021):

- **Stateless (sem estado):** Cada pedido do cliente contém toda a informação necessária para ser processado, sem dependência de estado armazenado no servidor;
- **Representação de recursos:** Os dados são transferidos em formatos padronizados, tipicamente JSON (*JavaScript Object Notation*);
- **Interface uniforme:** Utilização consistente dos métodos HTTP para as operações CRUD;
- **Camadas (*Layered System*):** A arquitectura pode ser composta por camadas intermediárias (proxies, *load balancers*) sem afectar a comunicação.

No projecto BNG Angola, a API REST é disponibilizada através do ficheiro `routes/api.php` e inclui *endpoints* para autenticação (login, registo, logout), listagem de farmácias e dados do utilizador autenticado. A autenticação é realizada através do **Laravel Sanctum**, que gera tokens de acesso pessoais (*Personal Access Tokens*) no momento do login, os quais devem ser incluídos no *header* `Authorization: Bearer {token}` de cada pedido subsequente (Otwell, 2023).

Este modelo de autenticação é particularmente adequado para aplicações móveis, uma vez que não depende de *cookies* de sessão — que são nativos do ambiente web — e permite o controlo granular de permissões e a revogação individual de tokens (Pontes, 2021).

## 2.8 Integração de Transporte Externo e Logística de Última Milha

A **logística de última milha** (*last-mile delivery*) refere-se ao último trecho do processo de entrega, desde o ponto de distribuição (neste caso, a farmácia) até ao destinatário final (o cliente). Este segmento é frequentemente considerado o mais complexo e dispendioso da cadeia logística, representando até 53% do custo total de entrega (Mangiaracina et al., 2019).

Em mercados emergentes como Angola, a logística de última milha enfrenta desafios adicionais, incluindo infraestruturas viárias deficientes, endereçamento não padronizado e limitada cobertura de serviços logísticos tradicionais (Bates et al., 2019). Neste contexto, a integração com plataformas de transporte sob demanda — como o Yango (grupo Yandex) — apresenta-se como uma alternativa viável, aproveitando redes de motoristas já estabelecidas.

O modelo de integração adoptado no projecto BNG Angola baseia-se em dois mecanismos complementares:

1. **Registo manual de entrega:** A farmácia solicita o transporte, regista os dados do motorista (nome, telefone), o identificador externo do pedido e o preço estimado. O sistema armazena estes dados no modelo `OrderDelivery` e permite o acompanhamento do estado (solicitado → em curso → entregue);

2. **Webhooks:** Mecanismo de comunicação assíncrona em que o parceiro de transporte envia notificações automáticas ao sistema quando ocorrem eventos relevantes (ex.: motorista atribuído, entrega iniciada, entrega concluída). O *webhook* é implementado através de um *endpoint* dedicado (`POST /webhooks/yango`), protegido por autenticação via *header* secreto e limitação de taxa (Pastore, 2021).

O padrão *Factory* (`DeliveryPartnerFactory`) é utilizado para abstrair a lógica específica de cada parceiro de transporte, permitindo a adição futura de novos parceiros sem alteração do código existente — em conformidade com o princípio *Open/Closed* da engenharia de *software* (Martin, 2018).

## 2.9 Segurança em Aplicações Web (OWASP)

A segurança é uma preocupação transversal no desenvolvimento de aplicações web, particularmente quando estas lidam com dados sensíveis de saúde e informações financeiras. A **OWASP (*Open Web Application Security Project*)** é uma organização internacional sem fins lucrativos que publica directrizes e ferramentas para a melhoria da segurança de *software* (OWASP Foundation, 2021).

O **OWASP Top 10** (2021) identifica as dez categorias de vulnerabilidades mais críticas em aplicações web. O projecto BNG Angola implementa medidas de mitigação para várias destas categorias:

- **A01:2021 — Broken Access Control:** O sistema implementa controlo de acesso baseado em papéis (*RBAC — Role-Based Access Control*) através do *middleware* `CheckRole`, que verifica se o utilizador autenticado possui o papel necessário para aceder a cada rota. São definidos cinco papéis: `admin`, `client`, `pharmacy_normal`, `pharmacy_matrix` e `pharmacy_branch`;

- **A02:2021 — Cryptographic Failures:** As palavras-passe são armazenadas utilizando *hashing* Bcrypt (via `Hash::make`), e a comunicação é preparada para HTTPS. Os tokens da API são gerados pelo Sanctum com entropia criptográfica adequada;

- **A03:2021 — Injection:** O uso consistente do Eloquent ORM e de *queries* parametrizadas previne ataques de injecção SQL. A validação rigorosa de todos os dados de entrada — utilizando as regras de validação do Laravel — previne injecção de dados maliciosos;

- **A05:2021 — Security Misconfiguration:** O sistema inclui configuração de CORS restritiva (origens permitidas configuráveis via variável de ambiente), protecção CSRF em todas as rotas web e exclusão cirúrgica apenas para o *endpoint* do *webhook*;

- **A07:2021 — Identification and Authentication Failures:** Implementação de limitação de taxa (*rate limiting*) nos *endpoints* de autenticação (5 tentativas por 8 minutos), prevenindo ataques de força bruta. A política de palavras-passe exige um mínimo de 6 caracteres sem espaços;

- **A09:2021 — Security Logging and Monitoring Failures:** O sistema implementa um serviço de auditoria (`ActivityLogger`) que regista acções sensíveis (aprovações, pagamentos, *backups*, suspensões) com identificação do utilizador, IP e *timestamp*.

## 2.10 Trabalhos Relacionados

A revisão da literatura e do mercado permite identificar trabalhos e plataformas que se relacionam com a presente investigação:

**mPharma (Gana, Nigéria, Quénia):** Plataforma de gestão de inventário farmacêutico que opera em vários países africanos, focada na optimização da cadeia de abastecimento e na redução de custos para os pacientes. Diferencia-se do BNG Angola por operar num modelo B2B (*Business-to-Business*), sem componente directa de busca pelo consumidor final (Drilvon, 2021).

**MedFinder (Nigéria):** Aplicação móvel que permite a busca de medicamentos em farmácias próximas. Semelhante ao BNG Angola na funcionalidade de busca, mas sem gestão de pedidos, sistema de pagamento ou integração de transporte (Adeyemi et al., 2020).

**PillPack / Amazon Pharmacy (EUA):** Serviço completo de farmácia *online* com entrega ao domicílio. Embora funcione num contexto regulatório e económico muito diferente, demonstra a viabilidade do modelo de farmácia digital com entrega integrada (Aitken & Kleinrock, 2019).

**Tabela 1 — Comparação entre plataformas relacionadas e o BNG Angola**

| Funcionalidade | mPharma | MedFinder | Amazon Pharmacy | **BNG Angola** |
|---|:---:|:---:|:---:|:---:|
| Busca de medicamentos por localização | Não | Sim | Sim | **Sim** |
| Gestão de pedidos online | Parcial | Não | Sim | **Sim** |
| Comprovativo de pagamento manual | Não | Não | Não | **Sim** |
| Gestão de inventário (farmácia) | Sim | Não | Sim | **Sim** |
| Transferência matriz→filial | Não | Não | N/A | **Sim** |
| Integração transporte externo | Não | Não | Sim (próprio) | **Sim (Yango)** |
| Aplicação móvel | Sim | Sim | Sim | **Sim (Flutter)** |
| Mensalidades/modelo de negócio | B2B | Gratuito | Subscrição | **Trial + Mensalidade** |
| Contexto angolano | Não | Não | Não | **Sim** |

A análise comparativa evidencia que o BNG Angola se distingue dos trabalhos existentes pela combinação única de funcionalidades — busca de medicamentos, gestão completa de pedidos com pagamento manual, transferência de stock entre entidades, integração com transporte local e adaptação ao contexto angolano — num único sistema integrado.

---
<div style="page-break-after: always;"></div>

## 2.11 Diagramas Conceituais

### 2.11.1 Diagrama do Padrão Arquitectural MVC

> **Figura 1 — Diagrama do padrão arquitectural Model-View-Controller (MVC)**

[INSERIR DIAGRAMA MVC AQUI]

**Legenda:** O diagrama ilustra a separação de responsabilidades no padrão MVC: o Model representa a lógica de negócio e dados, a View é responsável pela apresentação ao utilizador, e o Controller actua como intermediário, processando pedidos e coordenando a interacção entre Model e View. No projecto BNG Angola, este padrão é implementado através dos modelos Eloquent, das views Blade e dos controladores Laravel.

---
<div style="page-break-after: always;"></div>

### 2.11.2 Diagrama de Arquitectura REST

> **Figura 2 — Diagrama de arquitectura RESTful com autenticação baseada em tokens**

[INSERIR DIAGRAMA REST AQUI]

**Legenda:** O diagrama representa a arquitectura RESTful implementada no projecto, mostrando o fluxo de comunicação entre a aplicação móvel Flutter e o backend Laravel via API REST. A autenticação é realizada através do Laravel Sanctum, que gera tokens de acesso pessoais no momento do login. Cada pedido subsequente inclui o token no header `Authorization: Bearer {token}`, garantindo a identificação do utilizador sem dependência de sessões baseadas em cookies.

---
<div style="page-break-after: always;"></div>

### 2.11.3 Diagrama de Segurança OWASP

> **Figura 3 — Diagrama de medidas de segurança implementadas segundo OWASP Top 10**

[INSERIR DIAGRAMA SEGURANÇA AQUI]

**Legenda:** O diagrama ilustra as medidas de mitigação implementadas para as vulnerabilidades críticas identificadas no OWASP Top 10 (2021), incluindo controlo de acesso baseado em papéis (RBAC), protecção contra injecção SQL através do Eloquent ORM, limitação de taxa em endpoints de autenticação, protecção CSRF, hashing de palavras-passe com Bcrypt, e sistema de auditoria de actividades.
