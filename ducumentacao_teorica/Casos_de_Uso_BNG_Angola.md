# CASOS DE USO - SISTEMA BNG ANGOLA

## 1. Introdução

Este documento descreve os casos de uso do sistema BNG Angola, detalhando as interacções entre os diferentes actores (entidades) e o sistema.

## 2. Actores do Sistema

| Actor | Descrição | Permissões |
|---|---|---|
| **Cliente** | Utilizador final que busca medicamentos e faz pedidos | Busca medicamentos, cria pedidos, submete pagamento, acompanha pedidos |
| **Farmácia (Normal)** | Farmácia independente que gerencia o seu inventário e pedidos | Gerencia medicamentos, inventário, pedidos, filiais (se for matriz) |
| **Farmácia Matriz** | Farmácia com filiais que pode transferir stock | Gerencia medicamentos, inventário, pedidos, filiais, transferências de stock |
| **Filial de Farmácia** | Filial de uma farmácia matriz | Gerencia inventário local, pedidos da filial |
| **Administrador** | Gestor do sistema que aprova entidades e monitoriza actividade | Aprova/rejeita farmácias e filiais, aprova mensalidades, gera backups, visualiza logs |

## 3. Casos de Uso Principais

### 3.1 UC01 - Busca de Medicamentos

**Actor:** Cliente

**Descrição:** O cliente busca medicamentos disponíveis em farmácias activas, aplicando filtros para encontrar o produto desejado.

**Pré-condições:**
- Cliente está autenticado no sistema
- Existem farmácias activas na plataforma

**Fluxo Principal:**
1. Cliente acede à página de busca de medicamentos
2. Sistema apresenta campo de busca textual e filtros laterais (província, categoria, disponibilidade, stock)
3. Cliente introduz termo de pesquisa (nome, código de barras ou categoria) ou selecciona filtros
4. Cliente selecciona ordenação (preço, stock, nome)
5. Sistema executa query na tabela `medicine_inventories` com JOIN nas tabelas `medicines`, `pharmacies` e `pharmacy_branches`
6. Sistema aplica filtros: farmácias/filiais activas, stock > 0, disponibilidade = TRUE
7. Sistema apresenta resultados paginados (20 por página) com cards mostrando: medicamento, farmácia/filial, preço, stock, disponibilidade
8. Cliente pode clicar num resultado para ver detalhes

**Fluxo Alternativo:**
- Se não houver resultados, sistema apresenta mensagem "Nenhum medicamento encontrado com os critérios especificados"

**Pós-condições:**
- Cliente visualiza lista de medicamentos disponíveis

---

### 3.2 UC02 - Criação de Pedido

**Actor:** Cliente

**Descrição:** O cliente cria um pedido para um medicamento específico de uma farmácia/filial.

**Pré-condições:**
- Cliente está autenticado
- Cliente seleccionou um medicamento disponível

**Fluxo Principal:**
1. Cliente clica em "Solicitar" num resultado de busca
2. Sistema apresenta formulário de pedido com dados do medicamento e farmácia
3. Cliente confirma quantidade (padrão: 1)
4. Cliente selecciona método de entrega (levantamento na farmácia ou entrega ao domicílio)
5. Se entrega ao domicílio, cliente introduz endereço de entrega
6. Cliente submete pedido
7. Sistema cria registo na tabela `orders` com estado `pending`
8. Sistema cria registo na tabela `order_items` com o medicamento seleccionado
9. Sistema deduz stock do inventário (lock pessimista para evitar concorrência)
10. Sistema envia notificação à farmácia
11. Sistema apresenta confirmação ao cliente com número do pedido

**Fluxo Alternativo:**
- Se stock for insuficiente, sistema apresenta mensagem "Stock insuficiente" e não cria pedido

**Pós-condições:**
- Pedido criado com estado `pending`
- Stock deduzido do inventário
- Farmácia notificada

---

### 3.3 UC03 - Submissão de Comprovativo de Pagamento

**Actor:** Cliente

**Descrição:** O cliente submete comprovativo de pagamento (IBAN ou Express) para o pedido.

**Pré-condições:**
- Pedido existe com estado `pending`
- Cliente é proprietário do pedido

**Fluxo Principal:**
1. Cliente acede página de detalhes do pedido
2. Sistema apresenta opção "Submeter comprovativo de pagamento"
3. Cliente selecciona método de pagamento (IBAN ou Express)
4. Cliente faz upload do comprovativo (imagem ou PDF)
5. Cliente introduz referência de pagamento (opcional)
6. Cliente submete comprovativo
7. Sistema cria registo na tabela `order_payments` com estado `pending_verification`
8. Sistema actualiza estado do pedido para `payment_pending`
9. Sistema envia notificação à farmácia
10. Sistema apresenta confirmação ao cliente

**Pós-condições:**
- Comprovativo submetido
- Estado do pedido actualizado para `payment_pending`

---

### 3.4 UC04 - Confirmação de Pagamento pela Farmácia

**Actor:** Farmácia

**Descrição:** A farmácia verifica e confirma o pagamento do pedido.

**Pré-condições:**
- Pedido existe com estado `payment_pending`
- Farmácia é proprietária do pedido

**Fluxo Principal:**
1. Farmácia acede lista de pedidos pendentes
2. Sistema apresenta pedidos com estado `payment_pending`
3. Farmácia clica em "Ver detalhes" do pedido
4. Sistema apresenta comprovativo de pagamento submetido
5. Farmácia verifica o comprovativo (manualmente via banco)
6. Farmácia selecciona "Confirmar pagamento" ou "Rejeitar pagamento"
7. Se confirmar:
   - Sistema actualiza estado do pagamento para `paid`
   - Sistema actualiza estado do pedido para `confirmed`
   - Sistema envia notificação ao cliente
8. Se rejeitar:
   - Sistema actualiza estado do pagamento para `rejected`
   - Sistema actualiza estado do pedido para `payment_rejected`
   - Sistema envia notificação ao cliente com motivo da rejeição
   - Sistema repõe stock do inventário

**Pós-condições:**
- Pagamento confirmado ou rejeitado
- Estado do pedido actualizado
- Cliente notificado

---

### 3.5 UC05 - Marcar Pedido como Pronto

**Actor:** Farmácia

**Descrição:** A farmácia marca o pedido como pronto para levantamento ou entrega.

**Pré-condições:**
- Pedido existe com estado `confirmed`
- Farmácia é proprietária do pedido

**Fluxo Principal:**
1. Farmácia acede lista de pedidos confirmados
2. Sistema apresenta pedidos com estado `confirmed`
3. Farmácia clica em "Marcar como pronto"
4. Sistema actualiza estado do pedido para `ready`
5. Sistema envia notificação ao cliente
6. Se método de entrega é "entrega ao domicílio", sistema apresenta opção "Solicitar transporte"

**Pós-condições:**
- Pedido marcado como pronto
- Cliente notificado

---

### 3.6 UC06 - Solicitação de Transporte (Yango)

**Actor:** Farmácia

**Descrição:** A farmácia solicita transporte externo para entrega do pedido.

**Pré-condições:**
- Pedido existe com estado `ready`
- Método de entrega é "entrega ao domicílio"
- Farmácia é proprietária do pedido

**Fluxo Principal:**
1. Farmácia clica em "Solicitar transporte" no pedido
2. Sistema apresenta formulário de transporte (parceiro, dados do condutor, placa, contacto)
3. Farmácia selecciona parceiro (Yango ou outro)
4. Farmácia introduz dados do transporte (nome condutor, placa, contacto)
5. Farmácia submete solicitação
6. Sistema cria registo na tabela `order_deliveries` com estado `pending`
7. Sistema actualiza estado do pedido para `out_for_delivery`
8. Sistema envia notificação ao cliente com dados do transporte
9. Sistema envia webhook para o parceiro de transporte (se configurado)

**Fluxo Alternativo:**
- Se parceiro for Yango e webhook estiver configurado, sistema envia dados para API do Yango

**Pós-condições:**
- Transporte solicitado
- Estado do pedido actualizado para `out_for_delivery`
- Cliente notificado

---

### 3.7 UC07 - Actualização de Estado via Webhook (Yango)

**Actor:** Sistema (Webhook)

**Descrição:** O parceiro de transporte (Yango) actualiza o estado da entrega via webhook.

**Pré-condições:**
- Pedido existe com estado `out_for_delivery`
- Webhook do Yango está configurado

**Fluxo Principal:**
1. Yango envia POST para `/webhooks/yango` com dados do pedido e novo estado
2. Sistema valida segredo do webhook (header `X-Yango-Secret`)
3. Se segredo inválido, sistema retorna 403 Forbidden
4. Se segredo válido, sistema processa o payload:
   - Extrai `order_id` e `status` do payload
   - Actualiza estado na tabela `order_deliveries`
   - Se status = `delivered`, actualiza estado do pedido para `delivered`
   - Se status = `cancelled`, actualiza estado do pedido para `delivery_failed`
5. Sistema envia notificação ao cliente
6. Sistema retorna 200 OK

**Pós-condições:**
- Estado da entrega actualizado
- Estado do pedido actualizado
- Cliente notificado

---

### 3.8 UC08 - Gestão de Inventário de Medicamentos

**Actor:** Farmácia

**Descrição:** A farmácia adiciona, edita ou remove medicamentos do seu inventário.

**Pré-condições:**
- Farmácia está autenticada
- Farmácia está activa

**Fluxo Principal:**
1. Farmácia acede secção "Medicamentos"
2. Sistema apresenta lista de medicamentos do inventário
3. Farmácia clica em "Adicionar medicamento"
4. Sistema apresenta formulário com campos: nome, código de barras, categoria, descrição, preço, stock, requer prescrição, imagem
5. Farmácia preenche formulário
6. Farmácia submete
7. Sistema cria registo na tabela `medicines`
8. Sistema cria registo na tabela `medicine_inventories` com `owner_type = 'pharmacy'` e `owner_id = ID da farmácia`
9. Sistema apresenta confirmação

**Fluxo Alternativo (Edição):**
- Farmácia clica em "Editar" num medicamento existente
- Sistema apresenta formulário preenchido
- Farmácia modifica campos
- Sistema actualiza registo na tabela `medicines` e `medicine_inventories`

**Fluxo Alternativo (Remoção):**
- Farmácia clica em "Remover" num medicamento
- Sistema solicita confirmação
- Sistema marca medicamento como indisponível (`is_available = FALSE`)

**Pós-condições:**
- Inventário actualizado

---

### 3.9 UC09 - Transferência de Stock (Matriz → Filial)

**Actor:** Farmácia Matriz

**Descrição:** A farmácia matriz transfere stock de medicamentos para uma filial.

**Pré-condições:**
- Farmácia é do tipo `matrix`
- Filial está aprovada e activa
- Stock suficiente na matriz

**Fluxo Principal:**
1. Farmácia matriz acede secção "Filiais"
2. Sistema apresenta lista de filiais
3. Farmácia selecciona filial
4. Sistema apresenta inventário da filial
5. Farmácia clica em "Transferir stock"
6. Sistema apresenta formulário com lista de medicamentos da matriz
7. Farmácia selecciona medicamento e quantidade a transferir
8. Farmácia submete
9. Sistema inicia transacção de base de dados
10. Sistema deduz stock do inventário da matriz (lock pessimista)
11. Sistema adiciona stock ao inventário da filial
12. Sistema regista transferência em log de actividade
13. Sistema confirma transacção
14. Sistema envia notificação à filial
15. Sistema apresenta confirmação

**Fluxo Alternativo:**
- Se stock insuficiente, sistema apresenta erro "Stock insuficiente na matriz"

**Pós-condições:**
- Stock transferido
- Transacção atómica garantida
- Filial notificada

---

### 3.10 UC10 - Aprovação de Farmácia/Filial

**Actor:** Administrador

**Descrição:** O administrador aprova ou rejeita uma farmácia ou filial que se registou.

**Pré-condições:**
- Administrador está autenticado
- Existem farmácias/filiais pendentes de aprovação

**Fluxo Principal:**
1. Administrador acede painel administrativo
2. Sistema apresenta lista de farmácias/filiais com estado `pending`
3. Administrador clica em "Rever" numa entidade
4. Sistema apresenta dados da entidade (nome, NIF, número de alvará, localização)
5. Administrador verifica documentos (alvará, NIF)
6. Administrador selecciona "Aprovar" ou "Rejeitar"
7. Se aprovar:
   - Sistema actualiza estado para `approved`
   - Sistema regista timestamp de aprovação
   - Sistema envia notificação à farmácia
   - Sistema inicia período de trial de 30 dias
8. Se rejeitar:
   - Sistema actualiza estado para `rejected`
   - Sistema regista motivo da rejeição
   - Sistema envia notificação à farmácia

**Pós-condições:**
- Farmácia/filial aprovada ou rejeitada
- Entidade notificada

---

### 3.11 UC11 - Aprovação de Mensalidade

**Actor:** Administrador

**Descrição:** O administrador aprova o pagamento de uma mensalidade e gera um novo ciclo.

**Pré-condições:**
- Administrador está autenticado
- Existem mensalidades com estado `pending`

**Fluxo Principal:**
1. Administrador acede secção "Mensalidades"
2. Sistema apresenta lista de mensalidades com estado `pending`
3. Administrador clica em "Rever" numa mensalidade
4. Sistema apresenta dados da mensalidade (farmácia, valor, mês, comprovativo)
5. Administrador verifica comprovativo
6. Administrador selecciona "Aprovar" ou "Rejeitar"
7. Se aprovar:
   - Sistema actualiza estado para `paid`
   - Sistema regista timestamp de pagamento
   - Sistema gera novo ciclo de mensalidade para o mês seguinte
   - Sistema envia notificação à farmácia
8. Se rejeitar:
   - Sistema actualiza estado para `rejected`
   - Sistema regista motivo da rejeição
   - Sistema envia notificação à farmácia

**Pós-condições:**
- Mensalidade aprovada ou rejeitada
- Novo ciclo gerado (se aprovado)
- Farmácia notificada

---

### 3.12 UC12 - Geração de Factura PDF

**Actor:** Cliente ou Farmácia

**Descrição:** O cliente ou farmácia gera e faz download da factura em PDF de um pedido.

**Pré-condições:**
- Pedido existe
- Pagamento está confirmado

**Fluxo Principal:**
1. Utilizador acede detalhes do pedido
2. Sistema apresenta botão "Download factura"
3. Utilizador clica no botão
4. Sistema carrega template Blade `orders/invoice.blade.php`
5. Sistema injecta dados do pedido (cliente, farmácia, itens, pagamento, total)
6. Sistema utiliza DomPDF para gerar PDF
7. Sistema envia PDF para download do navegador

**Pós-condições:**
- Factura PDF gerada e descarregada

---

## 4. Diagrama de Casos de Uso (Resumo)

```
┌─────────────────┐
│   Cliente       │
└────────┬────────┘
         │
         ├─ UC01: Busca de Medicamentos
         ├─ UC02: Criação de Pedido
         ├─ UC03: Submissão de Comprovativo de Pagamento
         └─ UC12: Geração de Factura PDF

┌─────────────────┐
│   Farmácia      │
└────────┬────────┘
         │
         ├─ UC04: Confirmação de Pagamento
         ├─ UC05: Marcar Pedido como Pronto
         ├─ UC06: Solicitação de Transporte
         ├─ UC08: Gestão de Inventário
         └─ UC09: Transferência de Stock (Matriz)

┌─────────────────┐
│  Administrador  │
└────────┬────────┘
         │
         ├─ UC10: Aprovação de Farmácia/Filial
         ├─ UC11: Aprovação de Mensalidade
         └─ Backup e Monitorização

┌─────────────────┐
│  Sistema (Webhook)│
└────────┬────────┘
         │
         └─ UC07: Actualização de Estado via Webhook (Yango)
```

## 5. Tabela de Estados do Pedido

| Estado | Descrição | Actor Responsável | Transições Possíveis |
|---|---|---|---|
| `pending` | Pedido criado, aguarda pagamento | Cliente | `payment_pending`, `cancelled` |
| `payment_pending` | Comprovativo submetido, aguarda verificação | Cliente | `confirmed`, `payment_rejected` |
| `payment_rejected` | Pagamento rejeitado, stock reposto | Farmácia | `pending` (novo pagamento), `cancelled` |
| `confirmed` | Pagamento confirmado, aguarda preparação | Farmácia | `ready`, `cancelled` |
| `ready` | Pedido pronto, aguarda levantamento/entrega | Farmácia | `out_for_delivery`, `picked_up`, `cancelled` |
| `out_for_delivery` | Pedido em trânsito | Sistema (Webhook) | `delivered`, `delivery_failed` |
| `picked_up` | Pedido levantado pelo cliente | Cliente | - |
| `delivered` | Pedido entregue ao domicílio | Sistema (Webhook) | - |
| `delivery_failed` | Entrega falhou | Sistema (Webhook) | `ready` (nova tentativa) |
| `cancelled` | Pedido cancelado | Cliente/Farmácia | - |

## 6. Regras de Negócio

1. **RN01:** Uma farmácia só pode criar pedidos para os seus próprios medicamentos
2. **RN02:** O stock só é deduzido quando o pagamento é confirmado
3. **RN03:** Se o pagamento for rejeitado, o stock é reposto automaticamente
4. **RN04:** Apenas farmácias do tipo `matrix` podem ter filiais
5. **RN05:** Apenas farmácias do tipo `matrix` podem transferir stock para filiais
6. **RN06:** Transferências de stock são atómicas (ou tudo ou nada)
7. **RN07:** Farmácias em trial têm 30 dias gratuitos antes de pagar mensalidade
8. **RN08:** O período de trial é contado a partir da data de aprovação
9. **RN09:** Medicamentos sem stock não aparecem nos resultados de busca
10. **RN10:** Apenas farmácias/filiais activas e aprovadas aparecem nos resultados de busca
