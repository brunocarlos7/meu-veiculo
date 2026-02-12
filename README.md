# Meu Veículo em Dia 🚗

Aplicação moderna para gestão completa de veículos, controle de abastecimentos, manutenções e despesas financeiras. Construído com **Laravel 12 (API)** e **Vue 3 (Composition API)** seguindo os princípios do **Material Design 3**.

## 🚀 Tecnologias

- **Backend**: Laravel 12, Sanctum (Auth), SQLite/MySQL.
- **Frontend**: Vue 3, Pinia (State Management), Vue Router.
- **Design**: CSS Nativo com variáveis (Custom Properties) implementando Material Design 3.
- **Ícones**: Material Symbols Rounded.

## ✨ Funcionalidades

- **Autenticação**: Login e Registro de usuários seguro.
- **Gestão de Veículos**: Cadastro completo (Marca, Modelo, Ano, Placa, Tipo de Combustível).
- **Controle de Abastecimento**: Registro detalhado com cálculo automático de autonomia e custos.
- **Manutenções**: Histórico de serviços realizados (Troca de óleo, Pneus, Revisões).
- **Despesas Extras**: Controle de IPVA, Seguro, Multas e outros gastos.
- **Lembretes**: Notificações para próximas manutenções e pagamentos.
- **Relatórios**: Visão gráfica e detalhada dos custos por mês e por categoria.
- **Dashboard**: Visão geral rápida da frota e status atual.

## 🛠️ Instalação e Configuração

### Pré-requisitos
- PHP 8.2+
- Composer
- Node.js & NPM

### Passo a Passo

1.  **Clone o repositório**:
    ```bash
    git clone https://github.com/SEU_USUARIO/meu-veiculo.git
    cd meu-veiculo
    ```

2.  **Instale as dependências do Backend**:
    ```bash
    composer install
    ```

3.  **Instale as dependências do Frontend**:
    ```bash
    npm install
    ```

4.  **Configure o ambiente**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    touch database/database.sqlite
    ```

5.  **Execute as migrações e o Seeder (Dados de teste)**:
    ```bash
    php artisan migrate --seed
    ```

## ▶️ Executando o Projeto

Você precisará de dois ternimais rodando simultaneamente:

**Terminal 1 (Backend):**
```bash
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
npm run dev
```

Acesse a aplicação em: `http://localhost:8000`

## 📚 Estrutura do Projeto

- `app/Models`: Modelos Eloquent (Vehicle, FuelEntry, Maintenance, Expense, etc).
- `app/Http/Controllers`: Lógica de negócios da API.
- `resources/js/components`: Componentes Vue reutilizáveis (ModalDialog, ConfirmModal, etc).
- `resources/js/views`: Páginas da aplicação (Dashboard, VehicleList, Reports).
- `resources/js/stores`: Gerenciamento de estado com Pinia.

## 🎨 Design System

O projeto utiliza um sistema de design próprio baseado em variáveis CSS para fácil customização e suporte a temas (Claro/Escuro).
- Cores semânticas (`--md-primary`, `--md-error`, etc).
- Tipografia responsiva.
- Componentes com animações fluídas (Modais, Cards).
