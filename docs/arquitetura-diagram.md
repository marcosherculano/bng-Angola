# Figura 1 - Arquitectura geral da plataforma BNG Angola

```mermaid
graph TD
    %% Camada Apresentação
    subgraph "Camada Apresentação"
        A1[Blade Templates]
        A2[HTML5 + CSS3]
        A3[Bootstrap 5]
        A4[Flutter Mobile App]
        A5[Material Design]
        A6[flutter_map]
    end
    
    %% Camada Controlo
    subgraph "Camada Controlo"
        B1[Controladores Cliente]
        B2[Controladores Farmácia]
        B3[Controladores Admin]
        B4[Controladores API]
        B5[Controladores Webhooks]
    end
    
    %% Camada Negócio
    subgraph "Camada Negócio"
        C1[Modelos Eloquent<br/>(19 tabelas)]
        C2[NotificationService]
        C3[ActivityLogger]
        C4[DeliveryPartnerFactory]
        C5[Jobs Assíncronas]
    end
    
    %% Camada Dados
    subgraph "Camada Dados"
        D1[MySQL 8.0]
        D2[19 Tabelas]
        D3[Migrações Laravel]
        D4[Armazenamento Local<br/>storage/app]
    end
    
    %% Fluxo de comunicação vertical
    A1 --> B1
    A2 --> B1
    A3 --> B1
    A4 --> B4
    A5 --> B4
    A6 --> B4
    
    B1 --> C1
    B2 --> C1
    B3 --> C1
    B4 --> C1
    B5 --> C4
    
    C1 --> D1
    C1 --> D2
    C1 --> D3
    C2 --> D4
    C3 --> D1
    C4 --> D4
    C5 --> D1
    
    %% Estilos
    classDef apresentacao fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef controlo fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    classDef negocio fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef dados fill:#fff3e0,stroke:#e65100,stroke-width:2px
    
    class A1,A2,A3,A4,A5,A6 apresentacao
    class B1,B2,B3,B4,B5 controlo
    class C1,C2,C3,C4,C5 negocio
    class D1,D2,D3,D4 dados
```

## Descrição das Camadas

### Camada Apresentação
- **Blade Templates**: Motor de templates do Laravel para renderização HTML
- **HTML5 + CSS3**: Estrutura e estilo das páginas web
- **Bootstrap 5**: Framework CSS para design responsivo
- **Flutter Mobile App**: Aplicação móvel multiplataforma
- **Material Design**: Sistema de design da Flutter
- **flutter_map**: Componente para visualização de mapas

### Camada Controlo
- **Controladores Cliente**: Gestão de funcionalidades para clientes
- **Controladores Farmácia**: Gestão de funcionalidades para farmácias
- **Controladores Admin**: Gestão administrativa do sistema
- **Controladores API**: Endpoints RESTful para comunicação
- **Controladores Webhooks**: Processamento de notificações externas

### Camada Negócio
- **Modelos Eloquent**: 19 modelos de dados com regras de negócio
- **NotificationService**: Serviço de notificações
- **ActivityLogger**: Registo de auditoria
- **DeliveryPartnerFactory**: Fábrica para parceiros de transporte
- **Jobs Assíncronas**: Processamento em background

### Camada Dados
- **MySQL 8.0**: Base de dados relacional
- **19 Tabelas**: Estrutura de dados do sistema
- **Migrações Laravel**: Controlo de versão do esquema
- **Armazenamento Local**: Ficheiros e documentos (storage/app)

## Fluxo de Comunicação
As setas indicam o fluxo de dados vertical entre camadas:
1. **Apresentação** solicita operações aos **Controladores**
2. **Controladores** invocam a lógica de **Negócio**
3. **Camada Negócio** persiste/recupera dados na **Camada Dados**
