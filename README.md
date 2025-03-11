<p align="center">
  <img src="https://github.com/jhonatanjunio/travel-request-api/blob/main/public/logo.png" alt="Travel Request API Logo" width="400">
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+"></a>
  <a href="https://docker.com"><img src="https://img.shields.io/badge/Docker-Powered-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker"></a>
  <a href="https://github.com/jhonatanjunio/travel-request-api/actions"><img src="https://img.shields.io/badge/Tests-Passing-4CAF50?style=flat-square" alt="Tests Passing"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" alt="License: MIT"></a>
</p>

# Travel Request API - Documentação

## Sobre o Projeto

Este projeto é uma API de gerenciamento de requisições de viagens desenvolvida com Laravel 12. A aplicação permite que usuários façam solicitações de viagens, visualizem suas requisições e solicitem cancelamentos. Administradores podem aprovar ou rejeitar requisições de viagens e solicitações de cancelamento.

## Tecnologias Utilizadas

- **PHP 8.2+**
- **Laravel 12**: Framework PHP moderno e robusto
- **Laravel Sanctum**: Para autenticação via tokens
- **Predis**: Cliente PHP para Redis
- **Docker**: Para containerização da aplicação
- **MySQL**: Banco de dados relacional
- **Redis**: Para cache e filas

## Requisitos

- Docker e Docker Compose
- Git
- Postman (para testes de API)

## Configuração do Projeto

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/jhonatanjunio/travel-request-api.git
cd travel-request-api
```

### Passo 2: Configurar o Ambiente

1. Copie os arquivos de ambiente:
```bash
cp .env.example .env
cp .env.example .env.testing
```

2. Configure as variáveis de ambiente no arquivo `.env` conforme necessário

3. Configure o arquivo `.env.testing` para usar um banco de dados de teste separado:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=travel_management_testing
DB_USERNAME=travel_user
DB_PASSWORD=travel_password
```

### Passo 3: Iniciar com Docker

Certifique-se de que o Docker está instalado e em execução no seu sistema.

```bash
# Iniciar os containers
docker compose up -d

# Instalar dependências
docker compose exec travel-request-api composer install

# Gerar chave da aplicação
docker compose exec travel-request-api php artisan key:generate

# Executar migrações e seeders para o banco de dados principal
docker compose exec travel-request-api php artisan migrate --seed

# Criar o banco de dados de testes
docker compose exec db mysql -u root -ptravel_password -e "CREATE DATABASE IF NOT EXISTS travel_management_testing;"

# Executar migrações para o banco de dados de testes
docker compose exec travel-request-api php artisan migrate --env=testing
```

### Passo 4: Acessar a Aplicação

A API estará disponível em: `http://localhost:8000/api/v1`

## Usuários Pré-configurados

O sistema já vem com dois usuários pré-configurados para testes:

1. **Administrador**:
   - Email: admin@travelrequests.com
   - Senha: admin123

2. **Usuário Comum**:
   - Email: user@travelrequests.com
   - Senha: user123

## Arquitetura do Projeto

Este projeto foi desenvolvido seguindo uma arquitetura em camadas bem definidas, aplicando princípios SOLID e padrões de design que promovem a manutenibilidade, testabilidade e escalabilidade do código.

### Estrutura de Camadas

1. **Controllers**: Responsáveis apenas por receber requisições, delegar processamento para camadas inferiores e retornar respostas.
   - Implementados de forma enxuta, seguindo o princípio da Responsabilidade Única (SRP)
   - Utilizam injeção de dependência para acessar serviços

2. **Requests**: Classes dedicadas à validação de dados de entrada
   - Centralizam regras de validação, removendo essa responsabilidade dos controllers
   - Permitem personalização de mensagens de erro
   - Facilitam a reutilização de regras de validação

3. **Resources**: Transformam modelos em respostas JSON estruturadas
   - Padronizam o formato de resposta da API
   - Permitem controle granular sobre quais dados são expostos
   - Facilitam versionamento da API

4. **Services**: Contêm a lógica de negócio da aplicação
   - Implementam regras de negócio complexas
   - Orquestram chamadas a múltiplos repositories quando necessário
   - Garantem transações atômicas

5. **Repositories**: Abstraem o acesso a dados
   - Encapsulam queries e operações de banco de dados
   - Facilitam a troca de fonte de dados sem impactar a lógica de negócio
   - Melhoram a testabilidade através de mocks

6. **DTOs (Data Transfer Objects)**: Objetos para transferência de dados entre camadas
   - Garantem tipagem forte
   - Documentam contratos entre camadas
   - Reduzem acoplamento

7. **Policies**: Centralizam lógica de autorização
   - Separam regras de autorização da lógica de negócio
   - Facilitam testes de autorização
   - Permitem reutilização de regras em diferentes contextos

8. **Providers**: Configuram serviços e bindings
   - Centralizam configuração de dependências
   - Facilitam a substituição de implementações
   - Permitem configuração condicional baseada em ambiente

9. **Exceptions**: Tratamento personalizado de erros
   - Padronizam respostas de erro
   - Melhoram a experiência do desenvolvedor ao consumir a API
   - Facilitam o debugging

### Princípios SOLID Aplicados

1. **Single Responsibility Principle (SRP)**:
   - Cada classe tem uma única responsabilidade
   - Controllers delegam para services, que delegam para repositories

2. **Open/Closed Principle (OCP)**:
   - Uso de interfaces permite extensão sem modificação
   - Novas funcionalidades são adicionadas através de novas classes

3. **Liskov Substitution Principle (LSP)**:
   - Implementações de interfaces são intercambiáveis
   - Uso de type hints para garantir conformidade

4. **Interface Segregation Principle (ISP)**:
   - Interfaces pequenas e focadas
   - Clientes dependem apenas de métodos que realmente utilizam

5. **Dependency Inversion Principle (DIP)**:
   - Dependências são injetadas, não instanciadas internamente
   - Uso de abstrações (interfaces) em vez de implementações concretas

### Vantagens da Arquitetura Adotada

1. **Testabilidade**:
   - Camadas bem definidas facilitam testes unitários
   - Injeção de dependência permite mock de componentes
   - Separação de responsabilidades reduz complexidade dos testes

2. **Manutenibilidade**:
   - Código organizado e previsível
   - Mudanças localizadas em componentes específicos
   - Baixo acoplamento entre componentes

3. **Escalabilidade**:
   - Facilidade para adicionar novas funcionalidades
   - Possibilidade de escalar componentes individualmente
   - Preparado para crescimento da base de código

4. **Reutilização**:
   - Componentes desacoplados podem ser reutilizados
   - Lógica comum centralizada em services e repositories
   - DTOs padronizam transferência de dados

5. **Segurança**:
   - Validação centralizada em requests
   - Autorização centralizada em policies
   - Tratamento consistente de erros

### Possibilidades de Expansão

A arquitetura adotada facilita diversas expansões futuras:

1. **Microserviços**: A separação clara de responsabilidades facilita a extração de funcionalidades em microserviços independentes.

2. **Múltiplas Interfaces**: Adicionar novas interfaces (CLI, webhooks, etc.) é simplificado pela separação entre controllers e lógica de negócio.

3. **Versionamento de API**: A estrutura de resources facilita a criação de múltiplas versões da API.

4. **Caching Inteligente**: A camada de repositories permite implementar estratégias de cache sem afetar a lógica de negócio.

5. **Múltiplos Bancos de Dados**: A abstração de acesso a dados permite utilizar diferentes bancos para diferentes entidades.

6. **Eventos e Filas**: A arquitetura facilita a implementação de processamento assíncrono e comunicação baseada em eventos.

Esta arquitetura representa um equilíbrio entre complexidade e flexibilidade, adequada para aplicações empresariais que precisam evoluir ao longo do tempo, mantendo a qualidade e a manutenibilidade do código.

## Documentação da API

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/v1/auth/login` | Autenticar usuário e obter token |
| POST | `/api/v1/auth/register` | Registrar novo usuário |
| POST | `/api/v1/auth/logout` | Encerrar sessão (requer autenticação) |
| GET | `/api/v1/auth/me` | Obter dados do usuário autenticado |

### Rotas de Usuário (Autenticadas)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/travel-requests` | Listar todas as requisições de viagem do usuário |
| POST | `/api/v1/travel-requests` | Criar nova requisição de viagem |
| GET | `/api/v1/travel-requests/{id}` | Visualizar detalhes de uma requisição específica |
| POST | `/api/v1/travel-requests/{id}/initiate-cancellation` | Iniciar processo de cancelamento de uma requisição |
| GET | `/api/v1/travel-requests/{id}/confirm-cancellation` | Confirmar cancelamento de requisição (via link) |

### Rotas de Administrador (Autenticadas)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| PUT | `/api/v1/travel-requests/{id}` | Atualizar status de uma requisição pendente |
| GET | `/api/v1/admin/travel-requests/pending-cancellations` | Listar todas as solicitações de cancelamento pendentes |
| GET | `/api/v1/admin/travel-requests/{id}/cancellation/review` | Revisar uma solicitação de cancelamento específica |
| POST | `/api/v1/admin/travel-requests/{id}/approve-cancellation` | Aprovar solicitação de cancelamento |
| POST | `/api/v1/admin/travel-requests/{id}/reject-cancellation` | Rejeitar solicitação de cancelamento |

## Fluxo de Uso

### Para Usuários Comuns:

1. **Autenticação**: Faça login usando suas credenciais para obter um token de acesso.
2. **Criar Requisição**: Crie uma nova requisição de viagem fornecendo os detalhes necessários.
3. **Visualizar Requisições**: Consulte suas requisições existentes.
4. **Cancelar Requisição**:
   - Se a requisição estiver com status "requested" e a data de partida for futura, o cancelamento é automático.
   - Se a requisição já estiver aprovada, será necessário iniciar um processo de cancelamento e confirmar através do link fornecido.

### Para Administradores:

1. **Autenticação**: Faça login com credenciais de administrador.
2. **Gerenciar Requisições**: Aprove ou rejeite requisições de viagem pendentes.
3. **Gerenciar Cancelamentos**: Visualize, aprove ou rejeite solicitações de cancelamento.

## Coleção do Postman

Uma coleção do Postman está disponível para facilitar os testes da API:
[Travel Request API Collection](https://documenter.getpostman.com/view/2620805/2sAYk7S4V9)

A coleção está configurada para gerenciar automaticamente os tokens de autenticação. Após fazer login, o token será aplicado automaticamente às requisições subsequentes dentro da mesma pasta.

### Como usar a coleção:

1. Importe a coleção no Postman
2. Execute a requisição de login com as credenciais fornecidas
3. Explore as demais rotas que estarão automaticamente autenticadas

## Funcionalidades Implementadas

- **Sistema de Autenticação**: Login, registro e gerenciamento de tokens
- **Gerenciamento de Requisições de Viagem**: Criação, visualização e atualização
- **Sistema de Cancelamento**: 
  - Cancelamento automático para requisições pendentes
  - Processo de solicitação de cancelamento para requisições aprovadas
- **Painel Administrativo**: Gerenciamento de requisições e solicitações de cancelamento

## Testes Automatizados

A aplicação inclui uma suíte abrangente de testes automatizados para garantir a qualidade e a confiabilidade do código. Os testes foram implementados seguindo as melhores práticas de desenvolvimento orientado a testes (TDD) e utilizam o framework PHPUnit em conjunto com a biblioteca Mockery para mocking.

### Configuração do Ambiente de Testes

O projeto utiliza um banco de dados separado para testes, garantindo que os dados de desenvolvimento não sejam afetados durante a execução dos testes.

#### Configuração do Banco de Dados de Testes

1. O arquivo `.env.testing` contém as configurações específicas para o ambiente de testes:
   ```
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=travel_management_testing
   DB_USERNAME=travel_user
   DB_PASSWORD=travel_password
   ```

2. Certifique-se de que o banco de dados de testes existe:
   ```bash
   docker compose exec db mysql -u root -ptravel_password -e "CREATE DATABASE IF NOT EXISTS travel_management_testing;"
   ```

3. Execute as migrações no banco de dados de testes:
   ```bash
   docker compose exec travel-request-api php artisan migrate --env=testing
   ```

4. Verifique se o arquivo `phpunit.xml` está configurado para usar o ambiente de testes:
   ```xml
   <php>
       <env name="APP_ENV" value="testing"/>
       <env name="DB_DATABASE" value="travel_management_testing"/>
       <!-- outras configurações -->
   </php>
   ```

### Executando os Testes

Para executar os testes automatizados, utilize o seguinte comando:

```bash
docker compose exec travel-request-api php artisan test
```

A suíte de testes inclui 32 testes com 64 asserções, cobrindo todos os aspectos críticos da aplicação.

Para executar um grupo específico de testes:

```bash
docker compose exec travel-request-api php artisan test --filter=TravelRequestServiceTest
```

Para gerar um relatório de cobertura de código (requer Xdebug):

```bash
docker compose exec travel-request-api php artisan test --coverage
```

### Proteção do Banco de Dados de Desenvolvimento

Os testes são configurados para verificar se estão sendo executados no ambiente correto, evitando que o banco de dados de desenvolvimento seja afetado acidentalmente. Esta verificação é implementada nos testes através do seguinte código:

```php
if (DB::connection()->getDatabaseName() !== ':memory:') {
    $currentDatabase = DB::connection()->getDatabaseName();
    if (strpos($currentDatabase, 'testing') === false && strpos($currentDatabase, 'test') === false) {
        $this->markTestSkipped('ATENÇÃO: Testes não executados para proteger o banco de dados de produção/desenvolvimento!');
        return;
    }
}
```

Para garantir que os testes sejam executados no ambiente correto, sempre use o comando:

```bash
docker compose exec travel-request-api php artisan test --env=testing
```

## Contribuição

Para contribuir com o projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das suas alterações (`git commit -m 'Adiciona nova funcionalidade'`)
4. Envie para o branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a [Licença MIT](https://opensource.org/licenses/MIT).

---

Desenvolvido como parte de um teste técnico, este projeto demonstra a implementação de uma API RESTful com Laravel 12, seguindo as melhores práticas de desenvolvimento e arquitetura de software.
