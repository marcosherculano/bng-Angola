# ============================================================
# TRABALHO DE FIM DE CURSO — PARTE PRÉ-TEXTUAL
# Norma APA 7ª Edição — UNIBELAS
# ============================================================


---

# ■ CAPA

---

<div align="center">

*(Inserir logótipo da UNIBELAS aqui)*

**REPÚBLICA DE ANGOLA**

**MINISTÉRIO DO ENSINO SUPERIOR, CIÊNCIA, TECNOLOGIA E INOVAÇÃO**

**UNIVERSIDADE DE BELAS — UNIBELAS**

**FACULDADE DE ENGENHARIA E TECNOLOGIAS DE INFORMAÇÃO**

**CURSO DE ENGENHARIA INFORMÁTICA**

<br><br><br>

</div>

<div align="center" style="font-size:16pt;">

**PLATAFORMA WEB PARA BUSCA E ENTREGA DE MEDICAMENTOS EM ANGOLA COM INTEGRAÇÃO DE TRANSPORTE EXTERNO**

</div>

<br><br><br>

<div align="right">

**Zinho Martins Sapalo**

</div>

<br><br>

<div align="center">

**Trabalho de Fim de Curso**

<br><br><br>

**Luanda, 2025**

</div>

---
<div style="page-break-after: always;"></div>

# ■ FOLHA DE ROSTO

---

<div align="center">

**REPÚBLICA DE ANGOLA**

**MINISTÉRIO DO ENSINO SUPERIOR, CIÊNCIA, TECNOLOGIA E INOVAÇÃO**

**UNIVERSIDADE DE BELAS — UNIBELAS**

**FACULDADE DE ENGENHARIA E TECNOLOGIAS DE INFORMAÇÃO**

**DEPARTAMENTO DE ENGENHARIA INFORMÁTICA**

<br><br>

**PLATAFORMA WEB PARA BUSCA E ENTREGA DE MEDICAMENTOS EM ANGOLA COM INTEGRAÇÃO DE TRANSPORTE EXTERNO**

<br><br>

Trabalho de Fim de Curso apresentado à Faculdade de Engenharia e Tecnologias de Informação da Universidade de Belas (UNIBELAS), como requisito parcial para a obtenção do grau de **Licenciado em Engenharia Informática**.

<br>

**Autor:** Zinho Martins Sapalo

**Nº de Estudante:** 48957

**Período:** Manhã

**Curso:** Engenharia Informática

**Orientador:** Prof. ___________________________ *(preencher com o nome do orientador)*

<br><br><br>

**Luanda, 2025**

</div>

---
<div style="page-break-after: always;"></div>

# ■ FICHA CATALOGRÁFICA

---

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FICHA CATALOGRÁFICA                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  SAPALO, Zinho Martins                                                  │
│                                                                         │
│      Plataforma Web para Busca e Entrega de Medicamentos em Angola     │
│  com Integração de Transporte Externo / Zinho Martins Sapalo. —        │
│  Luanda: UNIBELAS, 2025.                                               │
│                                                                         │
│      xx f. : il.                                                        │
│                                                                         │
│      Trabalho de Fim de Curso (Licenciatura em Engenharia              │
│  Informática) — Universidade de Belas, Faculdade de Engenharia e       │
│  Tecnologias de Informação, 2025.                                      │
│                                                                         │
│      Orientador: Prof. ___________________________                      │
│                                                                         │
│      1. Plataforma Web. 2. Farmácia Comunitária. 3. Busca de          │
│  Medicamentos. 4. Gestão de Pedidos. 5. Integração de Transporte.      │
│  6. Laravel. 7. Flutter.                                                │
│  I. Título.                                                             │
│                                                                         │
│                                               CDU: 004.774:615.1        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

> **Nota:** O número total de folhas (xx f.) e a CDU devem ser confirmados pela biblioteca da UNIBELAS antes da encadernação final.

---
<div style="page-break-after: always;"></div>

# ■ EPÍGRAFE

---

<div align="center">

<br><br><br><br><br><br><br><br>

*"A tecnologia é apenas uma ferramenta. No que diz respeito a motivar as crianças e conseguir que trabalhem juntas, o professor é o mais importante."*

— **Bill Gates**

<br><br><br>

*"A saúde é o bem mais precioso e o mais fácil de se perder; porém, o mais mal guardado."*

— **Chauvot de Beauchêne**

</div>

---
<div style="page-break-after: always;"></div>

# ■ DEDICATÓRIA

---

<div align="center">

<br><br><br><br><br><br><br><br><br><br>

Dedico este trabalho à minha família, pilar fundamental da minha formação pessoal e académica, pelo apoio incondicional, pela paciência e pelo incentivo constante ao longo de toda a minha trajectória universitária.

Em especial, aos meus pais, que sempre acreditaram no poder transformador da educação e que, com sacrifício e dedicação, tornaram possível a realização deste sonho.

A todos os angolanos que enfrentam diariamente dificuldades no acesso a medicamentos — que este trabalho possa representar um contributo, ainda que modesto, para a melhoria da saúde e do bem-estar das comunidades.

</div>

---
<div style="page-break-after: always;"></div>

# ■ AGRADECIMENTOS

---

## Agradecimentos

Agradeço, primeiramente, a **Deus**, por me conceder saúde, sabedoria e perseverança ao longo desta caminhada académica.

Ao meu **orientador**, Prof. ___________________________, pela disponibilidade, orientação científica e contributos valiosos que foram essenciais para a concretização deste trabalho.

À **Universidade de Belas (UNIBELAS)** e a todo o corpo docente do curso de Engenharia Informática, pelos conhecimentos transmitidos e pela formação de excelência.

Aos meus **pais e familiares**, pelo apoio emocional e material, pela compreensão nos momentos de ausência e pela motivação incansável.

Aos meus **colegas de curso**, pela partilha de experiências, pelo companheirismo e pelo espírito de entreajuda que marcou toda a nossa jornada académica.

A todas as **farmácias comunitárias** que, directa ou indirectamente, contribuíram com informações e perspectivas que enriqueceram este projecto.

A todos aqueles que, de alguma forma, contribuíram para a realização deste trabalho e que, por lapso, possam não ter sido mencionados — o meu sincero agradecimento.

---
<div style="page-break-after: always;"></div>

# ■ RESUMO

---

## Resumo

**SAPALO, Zinho Martins.** *Plataforma Web para Busca e Entrega de Medicamentos em Angola com Integração de Transporte Externo.* 2025. Trabalho de Fim de Curso (Licenciatura em Engenharia Informática) — Universidade de Belas, Luanda, 2025.

O presente trabalho propõe o desenvolvimento de uma plataforma web denominada **BNG Angola**, destinada a facilitar a busca, a gestão de pedidos e a entrega de medicamentos em farmácias comunitárias angolanas, com integração de transporte externo. A plataforma foi desenvolvida com o *framework* Laravel (PHP), padrão MVC, e Flutter para a aplicação móvel, comunicando via API RESTful com autenticação por tokens (Laravel Sanctum). O sistema implementa quatro níveis de acesso, possibilitando funcionalidades como busca de medicamentos, gestão de pedidos, submissão de comprovativos de pagamento, transferência de stock, gestão de mensalidades, integração com o Yango e geração de facturas em PDF. A metodologia segue uma abordagem qualitativa com pesquisa aplicada e ciclo de desenvolvimento ágil. Os resultados demonstram que a plataforma responde eficazmente às necessidades identificadas, melhorando a acessibilidade aos medicamentos e contribuindo para a modernização do sector farmacêutico em Angola.

**Palavras-chave:** plataforma web; busca de medicamentos; farmácia comunitária; transporte externo; Laravel; Flutter.

---
<div style="page-break-after: always;"></div>

# ■ ABSTRACT

---

## Abstract

**SAPALO, Zinho Martins.** *Web Platform for Medication Search and Delivery in Angola with External Transport Integration.* 2025. Final Course Work (Bachelor's Degree in Computer Engineering) — University of Belas, Luanda, 2025.

This work proposes the development of a web platform named **BNG Angola**, designed to facilitate medication search, order management, and delivery in Angolan community pharmacies, with external transport integration. In Angola, access to medication is hindered by the geographical dispersion of pharmacies, the absence of shared digital inventory systems, and the lack of efficient home delivery mechanisms. The platform was developed using the Laravel framework (PHP) with the MVC pattern, and Flutter for the mobile application, communicating through a RESTful API secured by token-based authentication (Laravel Sanctum). The system implements four access levels — administrator, client, pharmacy, and branch — enabling functionalities such as medication search with province-based filtering, order management with a complete state flow, payment proof submission, stock transfer between matrix pharmacy and branches, monthly fee management, integration with Yango via webhooks, and PDF invoice generation. The methodology follows a qualitative approach with applied research and an agile development cycle. The results demonstrate that the platform effectively addresses the identified needs, improving medication accessibility and contributing to the modernisation of the pharmaceutical sector in Angola.

**Keywords:** web platform; medication search; community pharmacy; external transport; Laravel; Flutter.

---
<div style="page-break-after: always;"></div>

# ■ LISTA DE ABREVIATURAS E SIGLAS

---

## Lista de Abreviaturas e Siglas

| Sigla | Significado |
|-------|-------------|
| **API** | *Application Programming Interface* (Interface de Programação de Aplicações) |
| **APA** | *American Psychological Association* |
| **CORS** | *Cross-Origin Resource Sharing* (Partilha de Recursos entre Origens Cruzadas) |
| **CRUD** | *Create, Read, Update, Delete* (Criar, Ler, Actualizar, Eliminar) |
| **CSRF** | *Cross-Site Request Forgery* (Falsificação de Pedido entre Sites) |
| **CSS** | *Cascading Style Sheets* (Folhas de Estilo em Cascata) |
| **DomPDF** | Biblioteca de geração de PDF para PHP |
| **FK** | *Foreign Key* (Chave Estrangeira) |
| **HTML** | *HyperText Markup Language* (Linguagem de Marcação de Hipertexto) |
| **HTTP** | *HyperText Transfer Protocol* (Protocolo de Transferência de Hipertexto) |
| **HTTPS** | *HyperText Transfer Protocol Secure* (HTTP Seguro) |
| **IBAN** | *International Bank Account Number* (Número de Conta Bancária Internacional) |
| **JSON** | *JavaScript Object Notation* (Notação de Objectos JavaScript) |
| **KPI** | *Key Performance Indicator* (Indicador-Chave de Desempenho) |
| **MVC** | *Model-View-Controller* (Modelo-Visão-Controlador) |
| **NIF** | Número de Identificação Fiscal |
| **OMS** | Organização Mundial da Saúde |
| **OWASP** | *Open Web Application Security Project* |
| **PDF** | *Portable Document Format* (Formato de Documento Portátil) |
| **PHP** | *PHP: Hypertext Preprocessor* |
| **REST** | *Representational State Transfer* (Transferência de Estado Representacional) |
| **SQL** | *Structured Query Language* (Linguagem de Consulta Estruturada) |
| **TFC** | Trabalho de Fim de Curso |
| **TIC** | Tecnologias de Informação e Comunicação |
| **UI** | *User Interface* (Interface do Utilizador) |
| **UNIBELAS** | Universidade de Belas |
| **UX** | *User Experience* (Experiência do Utilizador) |

---
<div style="page-break-after: always;"></div>

# ■ LISTA DE FIGURAS

---

## Lista de Figuras

| Nº | Título | Página |
|----|--------|--------|
| Figura 1 | Arquitectura geral da plataforma BNG Angola | xx |
| Figura 2 | Diagrama de casos de uso — Módulo Cliente | xx |
| Figura 3 | Diagrama de casos de uso — Módulo Farmácia | xx |
| Figura 4 | Diagrama de casos de uso — Módulo Administrador | xx |
| Figura 5 | Diagrama de classes do sistema | xx |
| Figura 6 | Diagrama Entidade-Relacionamento (DER) da base de dados | xx |
| Figura 7 | Fluxo de estados do pedido | xx |
| Figura 8 | Interface da página inicial (landing page) | xx |
| Figura 9 | Interface de busca de medicamentos (cliente) | xx |
| Figura 10 | Interface de criação de pedido (cliente) | xx |
| Figura 11 | Interface de gestão de pedidos (farmácia) | xx |
| Figura 12 | Interface de transferência de stock (farmácia matriz) | xx |
| Figura 13 | Interface do painel administrativo (dashboard) | xx |
| Figura 14 | Interface da aplicação móvel (Flutter) — ecrã de login | xx |
| Figura 15 | Interface da aplicação móvel (Flutter) — ecrã de farmácias | xx |
| Figura 16 | Fluxo de integração com transporte externo (Yango) | xx |

> **Nota:** Os números de página (xx) devem ser preenchidos após a paginação final do documento.

---
<div style="page-break-after: always;"></div>

# ■ LISTA DE TABELAS

---

## Lista de Tabelas

| Nº | Título | Página |
|----|--------|--------|
| Tabela 1 | Comparação entre plataformas de busca de medicamentos existentes | xx |
| Tabela 2 | Requisitos funcionais do sistema | xx |
| Tabela 3 | Requisitos não funcionais do sistema | xx |
| Tabela 4 | Tecnologias e ferramentas utilizadas no desenvolvimento | xx |
| Tabela 5 | Estrutura das tabelas da base de dados (modelos) | xx |
| Tabela 6 | Níveis de acesso e permissões do sistema | xx |
| Tabela 7 | Endpoints da API RESTful | xx |
| Tabela 8 | Resultados dos testes de funcionalidade | xx |
| Tabela 9 | Estados do pedido e transições permitidas | xx |
| Tabela 10 | Estrutura de cálculo de mensalidades | xx |

> **Nota:** Os números de página (xx) devem ser preenchidos após a paginação final do documento.

---
<div style="page-break-after: always;"></div>

# ■ ÍNDICE GERAL (SUMÁRIO)

---

## Índice Geral

| | Página |
|---|--------|
| **ELEMENTOS PRÉ-TEXTUAIS** | |
| Capa | i |
| Folha de Rosto | ii |
| Ficha Catalográfica | iii |
| Epígrafe | iv |
| Dedicatória | v |
| Agradecimentos | vi |
| Resumo | vii |
| Abstract | viii |
| Lista de Abreviaturas e Siglas | ix |
| Lista de Figuras | x |
| Lista de Tabelas | xi |
| Índice Geral | xii |
| | |
| **CAPÍTULO I — INTRODUÇÃO** | |
| 1.1 Contextualização do tema | xx |
| 1.2 Definição do problema | xx |
| 1.3 Justificativa | xx |
| 1.4 Objectivos | xx |
| 1.4.1 Objectivo geral | xx |
| 1.4.2 Objectivos específicos | xx |
| 1.5 Hipóteses | xx |
| 1.6 Delimitação do estudo | xx |
| 1.7 Estrutura do trabalho | xx |
| | |
| **CAPÍTULO II — FUNDAMENTAÇÃO TEÓRICA** | |
| 2.1 Saúde digital e acesso a medicamentos em países em desenvolvimento | xx |
| 2.2 Farmácias comunitárias e o contexto angolano | xx |
| 2.3 Transformação digital no sector farmacêutico | xx |
| 2.4 Plataformas web e arquitectura MVC | xx |
| 2.5 Framework Laravel e ecossistema PHP | xx |
| 2.6 Desenvolvimento móvel com Flutter | xx |
| 2.7 API RESTful e autenticação baseada em tokens | xx |
| 2.8 Integração de transporte externo e logística de última milha | xx |
| 2.9 Segurança em aplicações web (OWASP) | xx |
| 2.10 Trabalhos relacionados | xx |
| | |
| **CAPÍTULO III — METODOLOGIA** | |
| 3.1 Tipo de pesquisa | xx |
| 3.2 Abordagem metodológica | xx |
| 3.3 Técnicas e instrumentos de recolha de dados | xx |
| 3.4 População e amostra | xx |
| 3.5 Ferramentas e tecnologias utilizadas | xx |
| 3.6 Ciclo de desenvolvimento | xx |
| | |
| **CAPÍTULO IV — APRESENTAÇÃO E ANÁLISE DOS RESULTADOS** | |
| 4.1 Arquitectura do sistema | xx |
| 4.2 Modelo de dados | xx |
| 4.3 Funcionalidades implementadas | xx |
| 4.3.1 Módulo Cliente | xx |
| 4.3.2 Módulo Farmácia | xx |
| 4.3.3 Módulo Administrador | xx |
| 4.3.4 Aplicação Móvel (Flutter) | xx |
| 4.3.5 Integração com Transporte Externo | xx |
| 4.4 Interfaces do sistema | xx |
| 4.5 Testes e validação | xx |
| | |
| **CAPÍTULO V — DISCUSSÃO DOS RESULTADOS** | |
| 5.1 Análise crítica dos resultados | xx |
| 5.2 Comparação com trabalhos relacionados | xx |
| 5.3 Limitações do estudo | xx |
| 5.4 Contribuições do trabalho | xx |
| | |
| **CAPÍTULO VI — CONCLUSÕES E RECOMENDAÇÕES** | |
| 6.1 Conclusões | xx |
| 6.2 Recomendações para trabalhos futuros | xx |
| | |
| **REFERÊNCIAS BIBLIOGRÁFICAS** | xx |
| | |
| **APÊNDICES** | |
| Apêndice A — Código-fonte relevante | xx |
| Apêndice B — Capturas de ecrã complementares | xx |
| | |
| **ANEXOS** | |
| Anexo A — Norma APA UNIBELAS 7ª Edição (excerto) | xx |

> **Nota:** Todos os números de página (xx) devem ser preenchidos após a formatação e paginação final do documento no processador de texto (Word/LibreOffice).

---

**FIM DA PARTE PRÉ-TEXTUAL**

---

> ## Instruções para formatação no Word/LibreOffice (Norma APA UNIBELAS):
>
> 1. **Fonte:** Times New Roman ou Arial; 12pt (texto normal); 14pt negrito (títulos de capítulo); 13pt (subtítulos/secções); 10pt (notas de rodapé)
> 2. **Espaçamento:** 1,5 entre linhas (texto principal); espaço simples para notas de rodapé, títulos de tabelas, legendas e transcrições longas
> 3. **Margens:** Superior = 3 cm; Inferior = 2,5 cm; Esquerda = 3 cm; Direita = 2,5 cm
> 4. **Papel:** A4 branco, 80 g/m², impresso apenas numa face (uma lauda)
> 5. **Paginação:** Algarismos arábicos no rodapé; começa na Introdução (Cap. I); pré-textuais não numerados
> 6. **Capa:** Não conta na numeração
> 7. **Nº de páginas:** Licenciatura: mínimo 40, máximo 50 páginas (parte textual)
> 8. **Alinhamento do texto:** Justificado
> 9. **Parágrafos:** Recuo de 1,25 cm na primeira linha
> 10. **Citações directas longas (≥40 palavras):** Recuada 1,27 cm da margem esquerda, sem aspas, mesmo tamanho e fonte do texto
> 11. **Citações directas curtas (<40 palavras):** Entre aspas no corpo do texto, com (Autor, ano, p. xx)
> 12. **Referências:** Espaço simples dentro de cada referência; espaço duplo entre referências; recuo pendente (hanging indent) de 1,27 cm
> 13. **Tabelas:** Número em negrito acima; título em *itálico* na linha abaixo; sem bordas fechadas (apenas linhas horizontais); legenda com *Nota.* em itálico
> 14. **Figuras:** Número em negrito acima; título em *itálico* na linha abaixo; legenda com *Nota.* em itálico
> 15. **Encadernação:** Capa dura, cor azul escura (qualidade Silvertex); entregar 3 exemplares antes da defesa; após defesa: 2 encadernados + 1 PDF