# CAPÍTULO III — METODOLOGIA

## 3.1 Tipo de Pesquisa

O presente trabalho enquadra-se como uma **pesquisa aplicada**, uma vez que visa a aplicação prática de conhecimentos científicos e tecnológicos na resolução de um problema concreto — o acesso a medicamentos em Angola (Gil, 2022). Quanto à natureza dos dados, trata-se de uma pesquisa predominantemente **qualitativa**, complementada por elementos quantitativos na fase de avaliação.

Quanto aos objectivos, a pesquisa classifica-se como **exploratória-descritiva**: exploratória na medida em que investiga um domínio pouco estudado no contexto angolano (plataformas digitais farmacêuticas); descritiva porque descreve detalhadamente a arquitectura, as funcionalidades e o comportamento do sistema desenvolvido (Marconi & Lakatos, 2021).

Quanto aos procedimentos técnicos, o trabalho combina:

- **Pesquisa bibliográfica:** Revisão da literatura sobre saúde digital, farmácias comunitárias, tecnologias web e móvel, e trabalhos relacionados;
- **Pesquisa documental:** Análise da legislação angolana sobre o sector farmacêutico e das normas técnicas aplicáveis;
- **Desenvolvimento experimental:** Concepção, implementação e avaliação de um protótipo funcional da plataforma.

## 3.2 Abordagem Metodológica

A abordagem metodológica adoptada para o desenvolvimento do *software* segue o modelo de **desenvolvimento ágil**, inspirado nos princípios do Manifesto Ágil (Bass et al., 2021), adaptado ao contexto de um projecto académico individual. As características principais desta abordagem incluem:

- **Desenvolvimento iterativo e incremental:** O sistema foi construído em ciclos curtos (sprints de 2–3 semanas), cada um resultando num incremento funcional testável;
- **Priorização por valor:** As funcionalidades foram priorizadas com base no seu impacto para os utilizadores finais, começando pelo módulo de busca de medicamentos e gestão de pedidos;
- **Refactoring contínuo:** O código foi continuamente melhorado para manter a qualidade, a legibilidade e a manutenibilidade;
- **Testes frequentes:** Cada incremento foi testado manualmente antes de avançar para a funcionalidade seguinte.

O ciclo de desenvolvimento seguiu as seguintes fases:

1. **Levantamento de requisitos:** Identificação das necessidades dos utilizadores (clientes, farmácias, administradores) através da análise do contexto e de entrevistas informais com profissionais do sector farmacêutico;
2. **Análise e projecto:** Definição da arquitectura do sistema, modelação da base de dados (DER), diagramas de casos de uso e fluxos de processo;
3. **Implementação:** Codificação do *backend* (Laravel), do *frontend* web (Blade/CSS/JS), da API REST e da aplicação móvel (Flutter);
4. **Testes:** Verificação funcional, testes de integração e validação de segurança;
5. **Avaliação:** Análise dos resultados face aos objectivos definidos.

## 3.3 Técnicas e Instrumentos de Recolha de Dados

Para a fundamentação e avaliação do trabalho, foram utilizadas as seguintes técnicas:

**a) Revisão bibliográfica:**
Consulta de artigos científicos, livros, documentos técnicos e relatórios de organizações internacionais (OMS, OWASP) indexados em bases de dados como Google Scholar, IEEE Xplore e SciELO. As palavras-chave utilizadas incluíram: *e-pharmacy*, *medication search platform*, *community pharmacy digital*, *last-mile delivery*, *Laravel*, *Flutter*, *REST API*.

**b) Análise documental:**
Análise do Decreto Presidencial n.º 191/18 (regime jurídico da actividade farmacêutica em Angola), de dados estatísticos do INE e do Ministério da Saúde, e da documentação técnica das tecnologias utilizadas (Laravel, Flutter, Sanctum, Yango API).

**c) Observação directa:**
Observação do funcionamento de farmácias comunitárias em Luanda para compreensão dos processos de atendimento, gestão de stock e desafios operacionais.

**d) Testes funcionais:**
Execução sistemática de cenários de teste para validação das funcionalidades implementadas, cobrindo os fluxos críticos de cada módulo (cliente, farmácia, administrador).

## 3.4 População e Amostra

A população-alvo do estudo compreende:

- **Farmácias comunitárias** de Luanda, Angola, que representam o público-alvo primário da plataforma como fornecedores de medicamentos;
- **Cidadãos angolanos** que necessitam de aceder a medicamentos e que possuem acesso à Internet (via computador ou dispositivo móvel).

Para efeitos de validação do protótipo, foi utilizada uma amostra de conveniência composta por:

- Simulações de cenários de uso para os três perfis de utilizador (cliente, farmácia, administrador);
- Dados de teste representativos (medicamentos, farmácias, pedidos) inseridos na plataforma para validação funcional.

## 3.5 Ferramentas e Tecnologias Utilizadas

**Tabela 4 — Tecnologias e ferramentas utilizadas no desenvolvimento**

| Categoria | Tecnologia/Ferramenta | Versão | Finalidade |
|---|---|---|---|
| Linguagem *backend* | PHP | 8.2 | Linguagem de programação do servidor |
| *Framework* *backend* | Laravel | 10.x | Estrutura MVC, ORM, autenticação, *routing* |
| Motor de *templates* | Blade | — | Renderização de *views* HTML dinâmicas |
| Base de dados | MySQL | 8.0 | Armazenamento relacional dos dados |
| Autenticação API | Laravel Sanctum | 3.x | Tokens de acesso para a API REST |
| Geração de PDF | DomPDF (barryvdh) | 2.x | Geração de facturas em formato PDF |
| Linguagem móvel | Dart | 3.x | Linguagem da aplicação Flutter |
| *Framework* móvel | Flutter | 3.x | Interface da aplicação móvel multiplataforma |
| Mapas (Flutter) | flutter_map + OSM | — | Selecção e visualização de localizações |
| Servidor local | XAMPP | 8.2 | Servidor Apache + PHP + MySQL para desenvolvimento |
| Editor de código | Visual Studio Code | — | IDE principal de desenvolvimento |
| Controlo de versão | Git | — | Versionamento do código-fonte |
| Estilização web | CSS3 + Bootstrap | 5.x | Design responsivo das interfaces web |
| Transporte externo | Yango API | — | Integração com parceiro de entregas |
| Testes API | Postman | — | Testes manuais dos *endpoints* da API |

## 3.6 Ciclo de Desenvolvimento

O desenvolvimento do projecto BNG Angola seguiu o ciclo iterativo representado abaixo, com cada iteração produzindo um incremento funcional:

**Iteração 1 — Fundações (Semanas 1–3):**
- Configuração do ambiente de desenvolvimento (XAMPP, Laravel, MySQL);
- Criação da estrutura da base de dados (migrações);
- Implementação do sistema de autenticação (registo, login, recuperação de senha);
- Definição dos papéis (*roles*) e do *middleware* de controlo de acesso.

**Iteração 2 — Módulo Farmácia (Semanas 4–7):**
- CRUD de medicamentos com inventário sincronizado;
- Gestão de filiais (criação, aprovação, activação);
- Transferência de stock entre matriz e filiais com transacções atómicas;
- Configurações de pagamento da farmácia (IBAN, Express);
- Notificações de stock baixo.

**Iteração 3 — Módulo Cliente (Semanas 8–10):**
- Motor de busca de medicamentos com filtros (nome, categoria, província, disponibilidade);
- Criação de pedidos com selecção de inventário e método de levantamento;
- Agendamento de levantamento;
- Submissão de comprovativos de pagamento;
- Acompanhamento de estado do pedido (*polling*);
- Geração e *download* de facturas em PDF.

**Iteração 4 — Módulo Administrador (Semanas 11–13):**
- Painel (*dashboard*) com KPIs;
- Gestão de utilizadores (aprovar, suspender, bloquear, eliminar);
- Gestão de farmácias e filiais (activação, documentos, mensalidades);
- Gestão de mensalidades (aprovar, rejeitar, ciclos automáticos);
- Sistema de *backups* da base de dados (geração, *download*, restauro);
- Configurações do sistema (nome, cores, vídeos, contactos).

**Iteração 5 — Integração e Segurança (Semanas 14–16):**
- Integração com transporte externo (Yango) via *webhooks*;
- Padrão *Factory* para abstracção de parceiros de entrega;
- Implementação de medidas de segurança (CORS, rate limiting, CSRF, auditoria);
- API RESTful para a aplicação móvel (Sanctum);
- Notificações *in-app* e por e-mail.

**Iteração 6 — Aplicação Móvel Flutter (Semanas 17–19):**
- Ecrãs de autenticação (login, registo);
- Listagem de farmácias;
- Selecção de localização com mapa (flutter_map + OpenStreetMap);
- Integração com a API REST do *backend*.

**Iteração 7 — Testes e Refinamento (Semanas 20–22):**
- Testes funcionais de todos os módulos;
- Correcção de defeitos identificados;
- Optimização de desempenho e segurança;
- Documentação técnica e académica.

## 3.7 Motor de Pesquisa de Medicamentos

### 3.7.1 Descrição Funcional

O motor de pesquisa de medicamentos é o componente central da plataforma BNG Angola, responsável por permitir que os utilizadores encontrem medicamentos disponíveis em farmácias e filiais activas. A pesquisa é implementada no controlador `BuscaMedicamentosController` e opera sobre a tabela `medicine_inventories`, que contém os registos de disponibilidade de medicamentos em cada farmácia ou filial.

### 3.7.2 Modelo de Dados Normalizado

O modelo de dados foi projectado seguindo os princípios de normalização da terceira forma normal (3NF), eliminando redundâncias e garantindo integridade referencial. As tabelas principais envolvidas no motor de pesquisa são:

**Tabela 11 — Estrutura normalizada das tabelas do motor de pesquisa**

| Tabela | Descrição | Campos Principais | Relacionamentos |
|---|---|---|---|
| `users` | Utilizadores do sistema | `id`, `email`, `password`, `role` | 1:N com `pharmacies`, 1:N com `orders` (como cliente) |
| `pharmacies` | Farmácias (normal e matriz) | `id`, `user_id`, `business_name`, `province`, `city`, `latitude`, `longitude`, `type`, `is_active` | 1:1 com `users`, 1:N com `medicines`, 1:N com `pharmacy_branches` |
| `pharmacy_branches` | Filiais de farmácias matriz | `id`, `matrix_id`, `branch_name`, `province`, `city`, `latitude`, `longitude`, `is_active`, `status` | N:1 com `pharmacies`, 1:N com `medicine_inventories` (polimórfico) |
| `medicines` | Catálogo de medicamentos | `id`, `pharmacy_id`, `name`, `barcode`, `category`, `description`, `price`, `stock`, `is_available` | N:1 com `pharmacies`, 1:N com `medicine_inventories` |
| `medicine_inventories` | Inventário polimórfico | `id`, `medicine_id`, `owner_type`, `owner_id`, `price`, `stock`, `is_available` | N:1 com `medicines`, polimórfico N:1 com `pharmacies` ou `pharmacy_branches` |
| `orders` | Pedidos de clientes | `id`, `client_id`, `pharmacy_id`, `pharmacy_branch_id`, `medicine_inventory_id`, `status`, `total_price` | N:1 com `users` (cliente), N:1 com `pharmacies`, N:1 com `medicine_inventories` |

A utilização de **relacionamentos polimórficos** na tabela `medicine_inventories` permite que tanto farmácias quanto filiais mantenham inventários independentes do mesmo medicamento, com preços e disponibilidades distintas. O campo `owner_type` indica se o proprietário é uma farmácia (`'pharmacy'`) ou uma filial (`'pharmacy_branch'`), enquanto o `owner_id` contém o identificador correspondente.

### 3.7.3 Queries SQL Otimizadas com LIKE e JOIN

O motor de pesquisa utiliza o operador `LIKE` do SQL para pesquisa textual em múltiplos campos (nome, código de barras, categoria) e cláusulas `JOIN` para agregar dados de tabelas relacionadas. A query principal, gerada pelo Eloquent ORM, tem a seguinte estrutura conceptual:

```sql
SELECT mi.*, m.name, m.barcode, m.category, 
       p.business_name, p.province, p.city, p.latitude, p.longitude,
       b.branch_name, b.province as branch_province, b.city as branch_city
FROM medicine_inventories mi
INNER JOIN medicines m ON mi.medicine_id = m.id
LEFT JOIN pharmacies p ON mi.owner_type = 'pharmacy' AND mi.owner_id = p.id
LEFT JOIN pharmacy_branches b ON mi.owner_type = 'pharmacy_branch' AND mi.owner_id = b.id
WHERE (m.name LIKE '%termo%' 
       OR m.barcode LIKE '%termo%' 
       OR m.category LIKE '%termo%')
  AND (p.is_active = TRUE OR (b.is_active = TRUE AND b.status = 'approved'))
  AND mi.stock > 0
  AND mi.is_available = TRUE
ORDER BY mi.price ASC
LIMIT 20 OFFSET 0;
```

**Análise da optimização:**

1. **Índices compostos:** A tabela `medicine_inventories` possui índices compostos em `(owner_type, owner_id)`, `(medicine_id)` e `(owner_type, owner_id, stock)`, que aceleram as cláusulas `WHERE` e `JOIN`.

2. **Eager loading:** O controlador utiliza o método `with(['medicine', 'owner'])` do Eloquent para carregar as relações em uma única query (N+1 problem), reduzindo o número de acessos à base de dados.

3. **Filtros progressivos:** Os filtros são aplicados de forma progressiva, permitindo que o optimizador de queries do MySQL utilize os índices mais adequados para cada condição.

4. **Paginação:** Os resultados são paginados em lotes de 20 registos, reduzindo a quantidade de dados transferidos e melhorando a experiência do utilizador.

### 3.7.4 Limitação do LIKE e Proposta de FULLTEXT

O operador `LIKE` com wildcards (`%termo%`) realiza uma pesquisa de padrão que não é optimizada para grandes volumes de dados, pois não pode utilizar índices de forma eficiente quando o wildcard está no início do padrão. Em cenários com milhares ou milhões de registos, esta abordagem pode resultar em tempos de resposta degradados.

O MySQL oferece o índice **FULLTEXT**, que permite pesquisa textual avançada com relevância, suporte a operadores booleanos e melhor desempenho em grandes volumes de dados. No entanto, esta funcionalidade **não foi implementada** no projecto BNG Angola por duas razões:

1. **Volume de dados actual:** O protótipo opera com um volume de dados limitado (cenários de teste), onde o desempenho do LIKE é aceitável.

2. **Priorização de funcionalidades críticas:** O tempo de desenvolvimento foi priorizado para funcionalidades críticas como gestão de pedidos, transferências de stock e integração de transporte.

**Trabalho futuro:** A implementação de índices FULLTEXT nos campos `name`, `barcode` e `category` da tabela `medicines` é identificada como trabalho futuro, com impacto directo na escalabilidade do motor de pesquisa.

### 3.7.5 Camada MVC em Laravel

O motor de pesquisa segue estritamente o padrão MVC, com separação clara de responsabilidades:

**Model (Camada de Dados):**
- `MedicineInventory`: Modelo Eloquent que representa o inventário, com métodos para acessar o medicamento relacionado (`medicine()`) e o proprietário polimórfico (`owner()`).
- `Medicine`: Modelo que representa o catálogo de medicamentos.
- `Pharmacy` e `PharmacyBranch`: Modelos que representam as entidades proprietárias do inventário.

**View (Camada de Apresentação):**
- `resources/views/cliente/busca.blade.php`: Template Blade que renderiza a interface de pesquisa, incluindo o campo de busca, filtros laterais, lista de resultados e paginação. A view recebe os dados através de variáveis passadas pelo controlador (`$inventories`, `$provinces`, `$categories`).

**Controller (Camada de Controlo):**
- `BuscaMedicamentosController`: Controlador que implementa o método `__invoke(Request $request)`, que:
  1. Recebe o pedido HTTP com parâmetros de filtro (`q`, `province`, `category`, `available_only`, `in_stock_only`, `sort`);
  2. Constrói a query Eloquent com base nos filtros aplicados;
  3. Executa a query com paginação;
  4. Carrega as listas de províncias e categorias disponíveis;
  5. Retorna a view com os dados.

Esta separação permite que a lógica de negócio seja testada independentemente da interface, e que a interface seja modificada sem afectar a lógica de pesquisa.

### 3.7.6 Critérios de Inclusão/Exclusão

O motor de pesquisa aplica critérios de inclusão e exclusão para garantir que apenas resultados relevantes e válidos sejam apresentados aos utilizadores:

**Critérios de inclusão:**
- Medicamentos com stock disponível (`stock > 0`);
- Inventários marcados como disponíveis (`is_available = TRUE`);
- Farmácias activas (`is_active = TRUE`);
- Filiais activas e aprovadas (`is_active = TRUE` e `status = 'approved'`);
- Medicamentos cujo nome, código de barras ou categoria contenha o termo de pesquisa (se fornecido).

**Critérios de exclusão:**
- Inventários de farmácias desactivadas;
- Inventários de filiais não aprovadas ou desactivadas;
- Medicamentos sem stock (`stock = 0`);
- Inventários marcados como indisponíveis (`is_available = FALSE`);
- Medicamentos eliminados (soft delete, se implementado).

### 3.7.7 Variáveis de Estudo

Para efeitos de avaliação do motor de pesquisa, foram definidas as seguintes variáveis:

**Variáveis independentes (inputs do sistema):**
- `termo_pesquisa`: String contendo o termo de pesquisa fornecido pelo utilizador;
- `filtro_provincia`: String com o nome da província seleccionada (opcional);
- `filtro_categoria`: String com o nome da categoria seleccionada (opcional);
- `filtro_disponibilidade`: Booleano indicando se devem ser incluídos apenas medicamentos disponíveis;
- `filtro_stock`: Booleano indicando se devem ser incluídos apenas medicamentos com stock;
- `ordenacao`: String indicando o critério de ordenação (`price_asc`, `price_desc`, `stock_desc`, `name_asc`);
- `pagina`: Número inteiro indicando a página de resultados a apresentar.

**Variáveis dependentes (outputs do sistema):**
- `numero_resultados`: Número total de inventários que satisfazem os critérios de pesquisa;
- `tempo_resposta`: Tempo (em milissegundos) necessário para executar a query e retornar os resultados;
- `precisao_relevancia`: Medida subjectiva da relevância dos resultados face ao termo de pesquisa (não implementada automaticamente);
- `diversidade_farmacias`: Número de farmácias/filiais distintas representadas nos resultados.

### 3.7.8 Conformidade Ética e Responsabilidade Social

O desenvolvimento do motor de pesquisa considerou aspectos éticos e de responsabilidade social, particularmente no contexto do sector farmacêutico angolano:

**Proteção de dados sensíveis:**
- O sistema não armazena informações de saúde sensíveis dos utilizadores (diagnósticos, histórico médico);
- Os dados de contacto (email, telefone) são utilizados exclusivamente para comunicação relacionada com pedidos;
- As palavras-passe são armazenadas com hashing Bcrypt, impossibilitando a sua recuperação em texto claro;
- O acesso aos dados é controlado através de autenticação e autorização baseada em papéis.

**Responsabilidade social:**
- A plataforma visa melhorar o acesso a medicamentos em Angola, contribuindo para a saúde pública;
- O modelo de negócio baseado em mensalidades acessíveis (2.000–2.700 Kz) visa a inclusão de farmácias de diferentes dimensões;
- O período de *trial* de 30 dias permite que farmácias experimentem a plataforma sem custo inicial;
- A transparência de preços permite que os utilizadores comparem custos entre diferentes farmácias.

**Conformidade com a legislação angolana:**
- O sistema respeita o Decreto Presidencial n.º 191/18, que regula a actividade farmacêutica em Angola;
- As farmácias são obrigadas a fornecer o número de alvará, que é validado pelos administradores;
- O sistema não permite a venda de medicamentos sem prescrição quando esta é obrigatória (campo `requires_prescription`).

---
<div style="page-break-after: always;"></div>

## 3.8 Desenho Metodológico (com representação gráfica)

### 3.8.1 Diagrama de Entidade-Relacionamento (DER)

> **Figura 4 — Diagrama Entidade-Relacionamento (DER) do sistema BNG Angola**

[INSERIR DIAGRAMA DER AQUI]

**Legenda:** O diagrama DER ilustra as entidades principais do sistema (Users, Pharmacies, PharmacyBranches, Medicines, MedicineInventories, Orders, OrderItems, OrderPayments, OrderDeliveries) e os seus relacionamentos. As linhas representam relações 1:1 (linha simples), 1:N (linha com "pés de galinha") e polimórficas (linha tracejada). Os relacionamentos polimórficos são utilizados no MedicineInventory para permitir que tanto Pharmacies quanto PharmacyBranches sejam proprietários de inventários.

---
<div style="page-break-after: always;"></div>

### 3.8.2 Diagrama de Classes (UML)

> **Figura 5 — Diagrama de classes UML dos modelos Eloquent principais**

[INSERIR DIAGRAMA DE CLASSES AQUI]

**Legenda:** O diagrama de classes UML representa a estrutura orientada a objectos dos modelos Eloquent principais, incluindo atributos, métodos e relacionamentos. Cada classe corresponde a um modelo Laravel, com métodos que definem os relacionamentos (ex.: `medicine()`, `owner()`, `pharmacy()`). A herança da classe base `Model` do Laravel está implícita em todos os modelos.

---
<div style="page-break-after: always;"></div>

### 3.8.3 Diagrama de Estados do Pedido

> **Figura 6 — Diagrama de estados do pedido com transições permitidas**

[INSERIR DIAGRAMA DE ESTADOS AQUI]

**Legenda:** O diagrama de estados representa o ciclo de vida de um pedido, desde a criação (`pending`) até à entrega (`delivered`) ou cancelamento (`cancelled`). Cada transição é etiquetada com o actor responsável (Cliente ou Farmácia) e a acção que provoca a mudança de estado. Os estados em verde representam estados finais, enquanto os estados em azul representam estados intermédios.
