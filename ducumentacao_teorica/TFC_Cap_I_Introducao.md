# CAPÍTULO I — INTRODUÇÃO

## 1.1 Contextualização do Tema

O acesso a medicamentos constitui um dos pilares fundamentais de qualquer sistema de saúde funcional. A Organização Mundial da Saúde (OMS, 2020) estima que cerca de dois mil milhões de pessoas em todo o mundo carecem de acesso adequado a medicamentos essenciais, sendo esta realidade particularmente aguda em países em desenvolvimento do continente africano. Em Angola, apesar dos avanços registados no sector farmacêutico nas últimas décadas, persistem desafios estruturais significativos que comprometem o acesso da população a medicamentos de forma atempada e eficiente (Ministério da Saúde de Angola, 2021).

O contexto angolano apresenta particularidades que tornam este desafio mais complexo. A dispersão geográfica das farmácias comunitárias, concentradas maioritariamente nas áreas urbanas de Luanda e das capitais provinciais, dificulta o acesso por parte das populações periurbanas e rurais (Instituto Nacional de Estatística [INE], 2020). Acresce a isto a ausência generalizada de sistemas digitais que permitam aos cidadãos verificar a disponibilidade de medicamentos antes de se deslocarem a uma farmácia, resultando em deslocações desnecessárias, perda de tempo e frustração.

A transformação digital tem vindo a revolucionar o sector da saúde a nível global, com plataformas digitais a emergirem como ferramentas essenciais para aproximar os serviços de saúde dos cidadãos (Agarwal et al., 2020). No sector farmacêutico, esta transformação manifesta-se através de plataformas de busca de medicamentos, sistemas de gestão de inventário partilhado, mecanismos de pedido *online* e soluções de entrega ao domicílio integradas com parceiros logísticos (Bates et al., 2019).

É neste contexto que se insere o presente trabalho, que propõe o desenvolvimento de uma plataforma web denominada **BNG Angola** — uma solução tecnológica integrada para a busca, gestão de pedidos e entrega de medicamentos em farmácias comunitárias angolanas, com integração de serviços de transporte externo.

## 1.2 Definição do Problema

A realidade do acesso a medicamentos em Angola é marcada por um conjunto de problemas interligados que afectam tanto os cidadãos quanto as farmácias comunitárias:

**Do lado do cidadão:**

- Inexistência de uma plataforma centralizada que permita pesquisar medicamentos disponíveis nas farmácias da sua região, obrigando a deslocações físicas para verificar disponibilidade;
- Dificuldade em comparar preços entre diferentes farmácias;
- Ausência de mecanismos eficientes de entrega ao domicílio, particularmente relevante para pessoas com mobilidade reduzida, idosos ou residentes em áreas distantes das farmácias;
- Falta de transparência no processo de compra e acompanhamento de pedidos.

**Do lado da farmácia:**

- Gestão de inventário predominantemente manual ou com sistemas desconectados, dificultando o controlo de stock em tempo real;
- Ausência de canais digitais para receber e processar pedidos de clientes;
- Dificuldade na gestão de múltiplas filiais, com transferências de stock realizadas de forma artesanal e sem rastreabilidade;
- Inexistência de integração com serviços de transporte para viabilizar entregas ao domicílio.

Face a este cenário, a pergunta de investigação que orienta este trabalho é: **como pode uma plataforma web integrada contribuir para a melhoria do acesso a medicamentos em Angola, proporcionando mecanismos de busca, gestão de pedidos e entrega com integração de transporte externo?**

## 1.3 Justificativa

A relevância deste trabalho justifica-se em múltiplas dimensões:

**Relevância social:** A melhoria do acesso a medicamentos tem impacto directo na saúde e no bem-estar da população. Uma plataforma que permita localizar medicamentos disponíveis e solicitar a sua entrega ao domicílio pode reduzir significativamente as barreiras de acesso, especialmente para populações vulneráveis (OMS, 2020).

**Relevância económica:** A digitalização dos processos farmacêuticos pode contribuir para a optimização da gestão de inventário, redução de desperdícios por expiração de medicamentos e melhoria da eficiência operacional das farmácias comunitárias (Bates et al., 2019). Adicionalmente, a integração com serviços de transporte cria oportunidades de emprego e estimula a economia digital local.

**Relevância tecnológica:** O desenvolvimento de soluções tecnológicas adaptadas ao contexto angolano — considerando as infraestruturas disponíveis, as particularidades do mercado e as necessidades específicas dos utilizadores — representa um contributo significativo para o ecossistema tecnológico nacional (Schwab, 2019).

**Relevância académica:** O trabalho integra conhecimentos de múltiplas áreas da Engenharia Informática — desenvolvimento web, desenvolvimento móvel, arquitectura de *software*, segurança, integração de sistemas e experiência do utilizador — representando uma aplicação prática dos conhecimentos adquiridos ao longo da formação académica.

## 1.4 Objectivos

### 1.4.1 Objectivo Geral

Desenvolver uma plataforma web para busca e entrega de medicamentos em farmácias comunitárias angolanas, com integração de transporte externo, visando melhorar o acesso da população a medicamentos e optimizar a gestão das farmácias.

### 1.4.2 Objectivos Específicos

1. Analisar o estado actual do acesso a medicamentos e da digitalização do sector farmacêutico em Angola;
2. Conceber a arquitectura de um sistema web multi-actor (cliente, farmácia, administrador) com controlo de acesso baseado em papéis (*roles*);
3. Implementar um módulo de busca de medicamentos com filtragem por nome, categoria, província e disponibilidade, integrando inventários de farmácias e filiais;
4. Desenvolver um fluxo completo de gestão de pedidos — desde a criação até à entrega — incluindo submissão e validação de comprovativos de pagamento;
5. Implementar um sistema de gestão de inventário com suporte a transferências de stock entre farmácia matriz e filiais;
6. Integrar um serviço de transporte externo (Yango) para viabilizar a entrega de medicamentos ao domicílio, utilizando *webhooks* para comunicação assíncrona;
7. Desenvolver uma aplicação móvel complementar em Flutter, comunicando com o *backend* através de uma API RESTful;
8. Avaliar a funcionalidade e a usabilidade da plataforma desenvolvida.

## 1.5 Hipóteses

**Hipótese 1 (H1):** A implementação de uma plataforma web de busca de medicamentos com filtragem por localização geográfica melhora significativamente a capacidade dos cidadãos angolanos de localizar medicamentos disponíveis nas farmácias da sua região.

**Hipótese 2 (H2):** A integração de um sistema de pedidos *online* com gestão de comprovativos de pagamento e parceiros de transporte externo viabiliza a entrega de medicamentos ao domicílio de forma segura e rastreável.

**Hipótese 3 (H3):** A digitalização da gestão de inventário, com suporte a transferências entre matriz e filiais, optimiza o controlo de stock e reduz situações de ruptura de medicamentos.

## 1.6 Delimitação do Estudo

O presente estudo delimita-se nos seguintes termos:

- **Geográfico:** A plataforma é concebida para o contexto angolano, com foco inicial nas farmácias comunitárias de Luanda, embora a arquitectura suporte expansão para todas as províncias;
- **Temporal:** O desenvolvimento e a avaliação foram realizados no período de 2024–2025;
- **Funcional:** O sistema abrange a busca de medicamentos, gestão de pedidos, pagamento (comprovativo manual), entrega com transporte externo e gestão administrativa. Não abrange prescrição electrónica, integração com seguros de saúde ou regulação de medicamentos controlados;
- **Tecnológico:** O *backend* web utiliza Laravel (PHP 8.2), a aplicação móvel utiliza Flutter (Dart), e a integração de transporte é feita com a API do Yango. O sistema não implementa pagamento electrónico directo (*online payment gateway*), utilizando comprovativos manuais (transferência bancária/IBAN, Express).

## 1.7 Estrutura do Trabalho

O presente trabalho está organizado em seis capítulos:

O **Capítulo I — Introdução** apresenta a contextualização do tema, a definição do problema, a justificativa, os objectivos, as hipóteses e a delimitação do estudo.

O **Capítulo II — Fundamentação Teórica** revisa a literatura relevante sobre saúde digital, farmácias comunitárias, transformação digital no sector farmacêutico, tecnologias de desenvolvimento web e móvel, segurança em aplicações web e trabalhos relacionados.

O **Capítulo III — Metodologia** descreve o tipo de pesquisa, a abordagem metodológica, as técnicas e instrumentos de recolha de dados, a população e amostra, as ferramentas e tecnologias utilizadas e o ciclo de desenvolvimento.

O **Capítulo IV — Apresentação e Análise dos Resultados** detalha a arquitectura do sistema, o modelo de dados, as funcionalidades implementadas, as interfaces e os testes realizados.

O **Capítulo V — Discussão dos Resultados** apresenta a análise crítica dos resultados, a comparação com trabalhos relacionados, as limitações e as contribuições do trabalho.

O **Capítulo VI — Conclusões e Recomendações** sintetiza as principais conclusões e apresenta recomendações para trabalhos futuros.
