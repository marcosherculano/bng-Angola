# CAPÍTULO V — DISCUSSÃO DOS RESULTADOS

## 5.1 Análise Crítica dos Resultados

A análise crítica apresentada nesta secção distingue-se da apresentação de resultados (Capítulo IV) ao focar na interpretação, avaliação e contextualização dos achados, em vez de descrever meramente o que foi implementado.

### 5.1.1 Avaliação dos Objectivos

**Objectivo Geral:** O desenvolvimento de uma plataforma web para busca e entrega de medicamentos com integração de transporte externo foi plenamente atingido. A plataforma implementa um ecossistema completo que abrange desde a busca de medicamentos até à entrega ao domicílio, passando pela gestão de pedidos, pagamento e administração. O sistema encontra-se funcional e operacional num ambiente de desenvolvimento, com todas as funcionalidades previstas implementadas e testadas.

**Objectivos Específicos:**

- **OE1 (Análise do sector):** Cumprido através da revisão bibliográfica (Capítulo II), que identificou lacunas no acesso a medicamentos em Angola e fundamentou a necessidade da plataforma.

- **OE2 a OE6 (Arquitectura, busca, pedidos, inventário, transporte):** Integralmente implementados. A arquitectura multi-actor com cinco papéis (*roles*) provou ser adequada para representar a complexidade dos actores envolvidos no ecossistema farmacêutico.

- **OE7 (Aplicação móvel Flutter):** Parcialmente atingido. A aplicação implementa autenticação, listagem de farmácias e selecção de localização, mas não inclui ainda a totalidade das funcionalidades disponíveis na versão web. Esta limitação é discutida na secção 5.2.

- **OE8 (Avaliação):** Cumprido através dos testes funcionais (secção 4.5), com uma taxa de sucesso de 100% nos 30 cenários testados.

### 5.1.2 Avaliação das Hipóteses

**Hipótese 1 (Melhoria da capacidade de localizar medicamentos):** Suportada pela implementação do motor de busca, que permite filtrar medicamentos por nome, código de barras, categoria, província, disponibilidade e stock, agregando inventários de múltiplas farmácias e filiais numa única interface. A possibilidade de comparar preços entre diferentes fornecedores representa uma funcionalidade sem precedentes no contexto angolano.

**Hipótese 2 (Viabilização de entrega ao domicílio):** Suportada pela implementação do fluxo completo de entrega externa, incluindo o registo de dados do transporte, o acompanhamento de estados e a integração via *webhooks* com o Yango. O uso do padrão *Factory* garante a extensibilidade para outros parceiros de transporte.

**Hipótese 3 (Optimização do controlo de stock):** Suportada pela implementação do sistema de inventário com transferências atómicas entre matriz e filiais, notificações automáticas de stock baixo e dedução automática de stock na entrega. A utilização de *locks* pessimistas (`lockForUpdate`) garante a integridade dos dados mesmo em cenários de concorrência.

## 5.2 Comparação com Trabalhos Relacionados

A comparação da plataforma BNG Angola com os trabalhos relacionados identificados na secção 2.10 revela diferenciações significativas:

**Face ao mPharma:** Enquanto o mPharma opera num modelo B2B (gestão de inventário para farmácias), o BNG Angola adopta um modelo B2B2C que inclui o consumidor final como actor central. Adicionalmente, o BNG Angola implementa a gestão de pedidos completa com pagamento e entrega, funcionalidades ausentes no mPharma no que toca à relação directa com o paciente (Drilvon, 2021).

**Face ao MedFinder:** O BNG Angola ultrapassa significativamente o escopo do MedFinder ao complementar a busca de medicamentos com gestão de pedidos, pagamento, entrega externa e gestão administrativa. O MedFinder limita-se à localização de medicamentos sem possibilidade de transacção (Adeyemi et al., 2020).

**Face ao Amazon Pharmacy:** Embora o Amazon Pharmacy ofereça um serviço completo, opera num contexto radicalmente diferente (mercado norte-americano com infraestruturas maduras, pagamento electrónico universal, logística própria). O BNG Angola distingue-se pela adaptação ao contexto angolano — pagamento por comprovativo manual, integração com parceiros de transporte locais e modelo de mensalidade acessível para farmácias comunitárias (Aitken & Kleinrock, 2019).

A principal contribuição diferenciadora do BNG Angola reside na **combinação integrada** de funcionalidades (busca + pedido + pagamento manual + entrega externa + gestão de inventário com transferências + mensalidades) num **contexto angolano** — algo que nenhuma das plataformas analisadas oferece.

## 5.3 Limitações do Estudo

O presente trabalho apresenta as seguintes limitações, que devem ser consideradas na interpretação dos resultados:

1. **Ausência de índices FULLTEXT:** O motor de pesquisa utiliza o operador `LIKE`, que não é optimizado para grandes volumes de dados. A implementação de índices FULLTEXT nos campos `name`, `barcode` e `category` da tabela `medicines` foi identificada como trabalho futuro, mas não foi implementada devido a priorização de funcionalidades críticas. Esta limitação pode resultar em degradação de desempenho em cenários de produção com grandes volumes de dados.

2. **Cache não implementado:** O sistema não utiliza mecanismos de cache (Redis, Memcached). Esta limitação pode resultar em carga desnecessária na base de dados para dados frequentemente acedidos (listas de províncias, categorias, configurações) e tempos de resposta subóptimos para operações de leitura.

3. **Testes de carga não executados:** A estrutura de testes de desempenho foi definida (secção 4.8), mas os testes não foram executados. Os valores de tempo de resposta, uso de CPU e uso de memória para os cenários de 10k, 100k e 1M registos permanecem por medir ([A MEDIR]). Consequentemente, a escalabilidade do sistema sob carga real não foi validada.

4. **Aplicação móvel parcial:** A aplicação Flutter implementa apenas as funcionalidades essenciais (autenticação, listagem de farmácias, geolocalização), ficando pendente a implementação completa da busca de medicamentos, criação de pedidos e pagamento no ambiente móvel. Esta limitação resulta da priorização do *backend* web como núcleo funcional completo.

5. **Ausência de pagamento electrónico:** O sistema utiliza comprovativos manuais (IBAN, Express) em vez de *gateways* de pagamento electrónico. Esta decisão foi deliberada, reflectindo a realidade angolana onde a adopção de pagamentos digitais ainda é limitada e as transferências bancárias manuais permanecem o método dominante.

6. **Testes limitados ao ambiente de desenvolvimento:** A validação foi realizada num ambiente local (XAMPP), sem testes em ambiente de produção com utilizadores reais. A escalabilidade e o desempenho sob carga real não foram avaliados.

7. **Amostra de validação restrita:** Os testes funcionais foram realizados pelo desenvolvedor, sem envolvimento de utilizadores finais reais (pacientes, farmacêuticos). Um estudo de usabilidade com utilizadores reais proporcionaria dados mais robustos.

8. **Dependência de conectividade:** A plataforma requer acesso à Internet para todas as funcionalidades, não oferecendo modo *offline*. Em regiões com conectividade limitada, esta dependência pode representar uma barreira.

9. **Integração Yango parcial:** A integração com o Yango inclui o *webhook* e o registo manual, mas não implementa a criação automática de pedidos de transporte via API do Yango, que exigiria um contrato comercial activo com a empresa.

## 5.4 Trabalhos Futuros

Com base nas limitações identificadas e na análise crítica realizada, propõem-se os seguintes trabalhos futuros:

### 5.4.1 Implementação de Índices FULLTEXT

**Prioridade:** Alta
**Descrição:** Implementar índices FULLTEXT nos campos `name`, `barcode` e `category` da tabela `medicines` para permitir pesquisa textual avançada com relevância e melhor desempenho em grandes volumes de dados.

**Benefícios esperados:**
- Melhoria significativa do desempenho do motor de pesquisa em cenários de produção;
- Suporte a operadores booleanos (AND, OR, NOT);
- Pesquisa de sinónimos e correcção de erros (com configuração adicional);
- Maior relevância dos resultados face ao termo de pesquisa.

### 5.4.2 Implementação de Cache com Redis

**Prioridade:** Média
**Descrição:** Implementar Redis como mecanismo de cache para dados frequentemente acedidos, incluindo listas de províncias, categorias, dados de farmácias activas e configurações do sistema.

**Benefícios esperados:**
- Redução da carga na base de dados;
- Melhoria do tempo de resposta para operações de leitura;
- Escalabilidade horizontal através de cache distribuído;
- Melhoria da experiência do utilizador.

### 5.4.3 Execução de Testes de Carga

**Prioridade:** Alta
**Descrição:** Executar os testes de desempenho definidos na secção 4.8 para os cenários de 10k, 100k e 1M registos, preenchendo os campos [A MEDIR] com valores reais de tempo de resposta, uso de CPU e uso de memória.

**Benefícios esperados:**
- Validação da escalabilidade do sistema;
- Identificação de *bottlenecks* de performance;
- Fundamentação de decisões de optimização;
- Previsão de comportamento em cenários de produção.

### 5.4.4 Expansão da API Móvel

**Prioridade:** Alta
**Descrição:** Expandir a aplicação Flutter para incluir todas as funcionalidades disponíveis na versão web: busca completa de medicamentos, criação de pedidos, submissão de comprovativos de pagamento via câmara, acompanhamento em tempo real do estado dos pedidos e notificações *push*.

**Benefícios esperados:**
- Experiência completa no dispositivo móvel;
- Maior acessibilidade para utilizadores sem computador;
- Aumento da base de utilizadores;
- Melhoria da competitividade da plataforma.

### 5.4.5 Integração com Gateway de Pagamento

**Prioridade:** Média
**Descrição:** Integrar um gateway de pagamento electrónico (ex.: PayPal, Stripe, ou solução local angolana) para permitir pagamentos automáticos, reduzindo a dependência de comprovativos manuais.

**Benefícios esperados:**
- Simplificação do processo de pagamento;
- Redução do tempo de processamento de pedidos;
- Melhoria da experiência do utilizador;
- Maior automatização do fluxo de pedidos.

### 5.4.6 Estudo de Usabilidade com Utilizadores Reais

**Prioridade:** Média
**Descrição:** Realizar um estudo de usabilidade com utilizadores finais reais (pacientes, farmacêuticos) para identificar pontos de fricção na interface e oportunidades de melhoria.

**Benefícios esperados:**
- Validação da usabilidade com o público-alvo;
- Identificação de problemas não detectados nos testes internos;
- Melhoria da experiência do utilizador;
- Fundamentação científica para optimizações de interface.

### 5.4.7 Implementação de Modo Offline

**Prioridade:** Baixa
**Descrição:** Implementar funcionalidade de modo offline para permitir que os utilizadores acedam a funcionalidades básicas (ex.: catálogo de medicamentos) sem conectividade à Internet.

**Benefícios esperados:**
- Acessibilidade em regiões com conectividade limitada;
- Melhoria da resiliência da plataforma;
- Experiência do utilizador mais robusta.

## 5.5 Contribuições do Trabalho

Apesar das limitações identificadas, o presente trabalho oferece contribuições relevantes em múltiplas dimensões:

**Contribuição tecnológica:**
- Arquitectura completa de uma plataforma multi-actor para o sector farmacêutico, passível de replicação e adaptação;
- Implementação de transferências de stock atómicas com bloqueio pessimista, garantindo integridade em cenários de concorrência;
- Padrão *Factory* para abstracção de parceiros de transporte, promovendo extensibilidade;
- Integração web + API + mobile com autenticação unificada (Sanctum).

**Contribuição social:**
- Prova de conceito de que é viável melhorar o acesso a medicamentos em Angola através de tecnologia;
- Modelo de negócio sustentável baseado em mensalidades acessíveis, com período de *trial* para reduzir barreiras de adopção.

**Contribuição académica:**
- Aplicação prática integrada de conceitos de Engenharia Informática: arquitectura de *software*, bases de dados, segurança, desenvolvimento web e móvel, integração de sistemas;
- Documentação detalhada que pode servir de referência para trabalhos futuros no domínio da saúde digital em Angola.
