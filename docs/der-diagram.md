# Figura 6 - Diagrama Entidade-Relacionamento (DER) completo da base de dados

```mermaid
erDiagram
    %% Grupo Utilizadores
    users {
        bigint id PK
        string name
        string email UK
        string password
        string phone
        enum role
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }
    
    pharmacies {
        bigint id PK
        bigint user_id FK
        string name
        string nif UK
        string license
        string phone
        string email
        enum type
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    pharmacy_branches {
        bigint id PK
        bigint pharmacy_id FK
        string name
        string phone
        string address
        string province
        string municipality
        decimal lat
        decimal lng
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    %% Grupo Produtos
    medicine_categories {
        int id PK
        string name UK
        string description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    medicines {
        bigint id PK
        string name
        string description
        string barcode UK
        int category_id FK
        string manufacturer
        string active_substance
        decimal price
        boolean requires_prescription
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    medicine_inventory {
        bigint id PK
        bigint medicine_id FK
        bigint pharmacy_id FK
        bigint branch_id FK
        int quantity
        decimal cost_price
        decimal selling_price
        date expiry_date
        string batch_number
        timestamp created_at
        timestamp updated_at
    }
    
    %% Grupo Pedidos
    orders {
        bigint id PK
        bigint client_id FK
        bigint pharmacy_id FK
        bigint branch_id FK
        string order_number UK
        enum status
        decimal total_amount
        text notes
        timestamp created_at
        timestamp updated_at
    }
    
    order_items {
        bigint id PK
        bigint order_id FK
        bigint medicine_id FK
        int quantity
        decimal unit_price
        decimal subtotal
        timestamp created_at
        timestamp updated_at
    }
    
    order_payments {
        bigint id PK
        bigint order_id FK
        enum payment_method
        enum status
        decimal amount
        string transaction_reference
        string proof_file_path
        timestamp payment_date
        timestamp created_at
        timestamp updated_at
    }
    
    %% Grupo Entregas
    delivery_partners {
        int id PK
        string name
        string api_endpoint
        string webhook_secret
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    order_deliveries {
        bigint id PK
        bigint order_id FK
        bigint partner_id FK
        enum status
        string driver_name
        string driver_phone
        decimal delivery_fee
        decimal lat
        decimal lng
        text delivery_address
        timestamp requested_at
        timestamp assigned_at
        timestamp picked_up_at
        timestamp delivered_at
        timestamp created_at
        timestamp updated_at
    }
    
    %% Grupo Sistema
    notifications {
        bigint id PK
        bigint user_id FK
        string title
        text message
        enum type
        enum status
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }
    
    activity_logs {
        bigint id PK
        bigint user_id FK
        string action
        text description
        string ip_address
        string user_agent
        timestamp created_at
    }
    
    subscriptions {
        bigint id PK
        bigint pharmacy_id FK
        enum plan_type
        enum status
        date start_date
        date end_date
        decimal monthly_fee
        decimal discount_percentage
        boolean trial_used
        timestamp created_at
        timestamp updated_at
    }
    
    invoices {
        bigint id PK
        bigint pharmacy_id FK
        string invoice_number UK
        enum type
        decimal amount
        decimal tax
        decimal total
        date issue_date
        date due_date
        enum status
        string pdf_path
        timestamp created_at
        timestamp updated_at
    }
    
    %% Grupo Configuração
    provinces {
        int id PK
        string name UK
        string code
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    municipalities {
        int id PK
        int province_id FK
        string name UK
        string code
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    addresses {
        bigint id PK
        bigint user_id FK
        string street
        string building_number
        string neighborhood
        int province_id FK
        int municipality_id FK
        decimal lat
        decimal lng
        boolean is_default
        timestamp created_at
        timestamp updated_at
    }
    
    %% Relacionamentos
    users ||--o{ pharmacies : "1:N (user_id)"
    users ||--o{ orders : "1:N (client_id)"
    users ||--o{ notifications : "1:N (user_id)"
    users ||--o{ activity_logs : "1:N (user_id)"
    users ||--o{ addresses : "1:N (user_id)"
    
    pharmacies ||--o{ pharmacy_branches : "1:N (pharmacy_id)"
    pharmacies ||--o{ medicine_inventory : "1:N (pharmacy_id)"
    pharmacies ||--o{ orders : "1:N (pharmacy_id)"
    pharmacies ||--o{ subscriptions : "1:N (pharmacy_id)"
    pharmacies ||--o{ invoices : "1:N (pharmacy_id)"
    
    pharmacy_branches ||--o{ medicine_inventory : "1:N (branch_id)"
    pharmacy_branches ||--o{ orders : "1:N (branch_id)"
    
    medicine_categories ||--o{ medicines : "1:N (category_id)"
    medicines ||--o{ medicine_inventory : "1:N (medicine_id)"
    medicines ||--o{ order_items : "1:N (medicine_id)"
    
    orders ||--o{ order_items : "1:N (order_id)"
    orders ||--o{ order_payments : "1:N (order_id)"
    orders ||--o{ order_deliveries : "1:1 (order_id)"
    
    delivery_partners ||--o{ order_deliveries : "1:N (partner_id)"
    
    provinces ||--o{ municipalities : "1:N (province_id)"
    provinces ||--o{ addresses : "1:N (province_id)"
    
    municipalities ||--o{ addresses : "1:N (municipality_id)"
    
    %% Estilos
    classDef entity fill:#f8f9fa,stroke:#007bff,stroke-width:2px
    classDef primary fill:#fff3cd,stroke:#856404,stroke-width:2px
    classDef foreign fill:#d1ecf1,stroke:#0c5460,stroke-width:2px
    classDef unique fill:#f8d7da,stroke:#721c24,stroke-width:1px,dashed
    
    class users,pharmacies,pharmacy_branches,medicine_categories,medicines,medicine_inventory,orders,order_items,order_payments,delivery_partners,order_deliveries,notifications,activity_logs,subscriptions,invoices,provinces,municipalities,addresses entity
    class id,id,id,id,id,id,id,id,id,id,id,id,id,id,id,id,id,id primary
    class user_id,user_id,pharmacy_id,pharmacy_id,branch_id,category_id,medicine_id,order_id,order_id,order_id,partner_id,user_id,pharmacy_id,pharmacy_id,province_id,province_id,municipality_id,user_id foreign
    class email,email,nif,barcode,order_number,invoice_number,name,name,name name unique
```

## Legenda

**PK** = Chave Primária  
**FK** = Chave Estrangeira  
**UK** = Chave Única  
**1:N** = Um-para-muitos  
**1:1** = Um-para-um

## Descrição dos Grupos de Entidades

### Grupo Utilizadores
- **users**: Utilizadores do sistema (clientes, farmácias, administradores)
- **pharmacies**: Dados das farmácias (matriz e filiais)
- **pharmacy_branches**: Filiais das farmácias com localização

### Grupo Produtos
- **medicine_categories**: Categorias de medicamentos
- **medicines**: Catálogo de medicamentos
- **medicine_inventory**: Stock disponível por farmácia/filial

### Grupo Pedidos
- **orders**: Pedidos dos clientes
- **order_items**: Items de cada pedido
- **order_payments**: Registo de pagamentos

### Grupo Entregas
- **delivery_partners**: Parceiros de transporte (Yango, etc.)
- **order_deliveries**: Detalhes da entrega de cada pedido

### Grupo Sistema
- **notifications**: Notificações para utilizadores
- **activity_logs**: Registo de auditoria
- **subscriptions**: Subscrições/mensalidades das farmácias
- **invoices**: Facturas emitidas

### Grupo Configuração
- **provinces**: Províncias de Angola
- **municipalities**: Municípios por província
- **addresses**: Endereços dos utilizadores

## Total de Tabelas: 19
