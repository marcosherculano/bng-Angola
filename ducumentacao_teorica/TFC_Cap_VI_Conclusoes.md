# CAPÍTULO VI — CONCLUSÕES E RECOMENDAÇÕES

## 6.1 Conclusões

O presente trabalho teve como objectivo o desenvolvimento de uma plataforma web para busca e entrega de medicamentos em farmácias comunitárias angolanas, com integração de transporte externo. Ao longo do percurso de investigação e desenvolvimento, foi possível chegar às seguintes conclusões:

**Primeira conclusão:** A análise do contexto angolano confirmou a existência de lacunas significativas no acesso a medicamentos, nomeadamente a ausência de plataformas digitais que permitam ao cidadão pesquisar e comparar a disponibilidade e os preços de medicamentos em diferentes farmácias. Esta constatação valida a pertinência e a oportunidade do projecto desenvolvido.

**Segunda conclusão:** A arquitectura multi-actor implementada — com cinco papéis distintos (administrador, cliente, farmácia normal, farmácia matriz e filial) — demonstrou ser capaz de representar adequadamente a complexidade do ecossistema farmacêutico angolano, incluindo a estrutura hierárquica de farmácias com filiais e os diferentes perfis de interacção dos utilizadores.

**Terceira conclusão:** O motor de busca de medicamentos, baseado em inventários polimórficos que agregam dados de farmácias e filiais, provou ser eficaz na localização de medicamentos com filtragem por múltiplos critérios (nome, categoria, província, disponibilidade, stock). Esta funcionalidade responde directamente ao problema identificado de deslocações desnecessárias dos cidadãos.

**Quarta conclusão:** O fluxo completo de gestão de pedidos — desde a criação até à entrega, passando pela submissão e validação de comprovativos de pagamento — é funcional e robusto. A utilização de transacções atómicas com bloqueio pessimista garante a integridade dos dados, mesmo em cenários de acesso concorrente, sendo esta uma decisão técnica fundamental para a fiabilidade do sistema.

**Quinta conclusão:** A integração com o serviço de transporte externo Yango, através de registo manual e *webhooks*, demonstrou a viabilidade de utilizar plataformas de transporte sob demanda para a logística de última milha no contexto farmacêutico angolano. O padrão *Factory* adoptado assegura que a plataforma pode integrar novos parceiros de transporte no futuro sem alterações estruturais.

**Sexta conclusão:** O modelo de negócio baseado em mensalidades com período de *trial*, aliado ao cálculo automatizado que considera o tipo de farmácia e o número de filiais, apresenta-se como uma abordagem sustentável e acessível para o mercado angolano.

**Sétima conclusão:** As medidas de segurança implementadas — controlo de acesso por papéis, limitação de taxa, protecção CSRF, autenticação por tokens, auditoria de actividades e política de palavras-passe — conferem ao sistema um nível de segurança adequado para uma plataforma que lida com dados de saúde e informações financeiras.

Em síntese, a plataforma BNG Angola constitui uma prova de conceito funcional e abrangente de que é possível, através da tecnologia, contribuir para a modernização do sector farmacêutico em Angola e para a melhoria do acesso da população a medicamentos essenciais.

## 6.2 Recomendações para Trabalhos Futuros

Com base na experiência adquirida e nas limitações identificadas, recomendam-se as seguintes linhas de desenvolvimento futuro:

1. **Completar a aplicação móvel Flutter:** Implementar na aplicação móvel todas as funcionalidades disponíveis na versão web, incluindo busca completa de medicamentos, criação de pedidos, submissão de comprovativos de pagamento e acompanhamento de entregas em tempo real;

2. **Integrar *gateways* de pagamento electrónico:** À medida que a adopção de pagamentos digitais cresça em Angola, integrar serviços como o Multicaixa Express (MCX) ou outros *gateways* locais, permitindo pagamentos instantâneos e automatizados;

3. **Implementar notificações *push*:** Adicionar notificações *push* na aplicação móvel para alertar o cliente sobre mudanças de estado dos seus pedidos em tempo real;

4. **Realizar testes com utilizadores reais:** Conduzir um estudo de usabilidade com pacientes e farmacêuticos para recolher *feedback* qualitativo e quantitativo sobre a experiência de utilização;

5. **Implementar modo *offline*:** Desenvolver funcionalidades que permitam a consulta de dados em *cache* quando não há conectividade, com sincronização automática quando a ligação é restabelecida;

6. **Automatizar a integração com o Yango:** Implementar a criação automática de pedidos de transporte via API do Yango, eliminando a necessidade de registo manual dos dados da entrega pela farmácia;

7. **Adicionar sistema de avaliações:** Permitir que os clientes avaliem as farmácias e o serviço de entrega, promovendo a melhoria contínua da qualidade;

8. **Expandir para prescrição electrónica:** Investigar a viabilidade de integrar funcionalidades de prescrição electrónica, em articulação com o sistema de saúde nacional;

9. **Implementar *analytics* e relatórios:** Desenvolver um módulo de relatórios e análise de dados para as farmácias (vendas, medicamentos mais procurados, tendências) e para os administradores (métricas de adopção, crescimento);

10. **Escalar para ambiente de produção:** Migrar a plataforma para um ambiente de produção na nuvem, com configuração adequada de segurança (HTTPS, *headers* de segurança), escalabilidade (filas com Redis, *cache*, *workers*) e monitorização (logs centralizados, alertas).
