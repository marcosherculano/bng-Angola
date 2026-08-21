# Regras de Negócio

## Farmácias
- Tipos:
  - **Normal**
  - **Matriz**
  - **Filial** (associada a uma Matriz)

## Documentos (Alvará)
- A farmácia pode ter um documento de alvará associado em `alvara_document_path`.
- No Admin, deve ser possível:
  - baixar o documento
  - re-enviar/substituir o documento quando o ficheiro está ausente no servidor

## Mensalidades
- A mensalidade de uma farmácia pode ser dinâmica.
- Para Matriz:
  - total = mensalidade base da matriz + soma das mensalidades das filiais ativas/aprovadas.
