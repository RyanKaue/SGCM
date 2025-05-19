# Sistema de Gestão de Clínicas Médicas (SGCM) - Backend

Este é o backend do Sistema de Gestão de Clínicas Médicas (SGCM), desenvolvido com Laravel e API RESTful para integração com o frontend.

## Tecnologias Utilizadas

- PHP 8.1
- Laravel 10
- SQLite (para desenvolvimento)
- Laravel Breeze API para autenticação

## Estrutura do Projeto

O backend segue a arquitetura MVC do Laravel e inclui:

- **Models**: User, Medico, Paciente, Consulta, Prontuario, Especialidade
- **Controllers**: MedicoController, PacienteController, ConsultaController, ProntuarioController, EspecialidadeController
- **Migrations**: Todas as tabelas necessárias para o funcionamento do sistema
- **Routes**: API RESTful protegida por autenticação

## Funcionalidades Implementadas

- Sistema de autenticação com diferentes perfis (admin, médico, paciente)
- Cadastro e gerenciamento de médicos
- Cadastro e gerenciamento de pacientes
- Agendamento de consultas
- Registro e visualização de prontuários
- Gerenciamento de especialidades médicas

## Instalação e Configuração

1. Clone o repositório
2. Execute `composer install` para instalar as dependências
3. Configure o arquivo `.env` (use o `.env.example` como base)
4. Execute `php artisan migrate` para criar as tabelas no banco de dados
5. Execute `php artisan db:seed` para popular o banco com dados iniciais (opcional)
6. Execute `php artisan serve` para iniciar o servidor de desenvolvimento

## API Endpoints

### Autenticação
- `POST /api/register` - Registrar novo usuário
- `POST /api/login` - Login de usuário
- `POST /api/logout` - Logout de usuário (requer autenticação)

### Médicos
- `GET /api/medicos` - Listar todos os médicos
- `POST /api/medicos` - Cadastrar novo médico
- `GET /api/medicos/{id}` - Visualizar médico específico
- `PUT /api/medicos/{id}` - Atualizar médico
- `DELETE /api/medicos/{id}` - Remover médico

### Pacientes
- `GET /api/pacientes` - Listar todos os pacientes
- `POST /api/pacientes` - Cadastrar novo paciente
- `GET /api/pacientes/{id}` - Visualizar paciente específico
- `PUT /api/pacientes/{id}` - Atualizar paciente
- `DELETE /api/pacientes/{id}` - Remover paciente

### Consultas
- `GET /api/consultas` - Listar consultas (filtradas por perfil de usuário)
- `POST /api/consultas` - Agendar nova consulta
- `GET /api/consultas/{id}` - Visualizar consulta específica
- `PUT /api/consultas/{id}` - Atualizar consulta
- `DELETE /api/consultas/{id}` - Remover consulta (apenas admin)

### Prontuários
- `GET /api/prontuarios` - Listar prontuários (filtrados por perfil de usuário)
- `POST /api/prontuarios` - Criar novo prontuário (apenas médicos)
- `GET /api/prontuarios/{id}` - Visualizar prontuário específico
- `PUT /api/prontuarios/{id}` - Atualizar prontuário (apenas médico responsável)
- `DELETE /api/prontuarios/{id}` - Remover prontuário (apenas admin)

### Especialidades
- `GET /api/especialidades` - Listar todas as especialidades
- `POST /api/especialidades` - Cadastrar nova especialidade (apenas admin)
- `GET /api/especialidades/{id}` - Visualizar especialidade específica
- `PUT /api/especialidades/{id}` - Atualizar especialidade (apenas admin)
- `DELETE /api/especialidades/{id}` - Remover especialidade (apenas admin)

## Controle de Acesso

O sistema implementa controle de acesso baseado em perfis:

- **Admin**: Acesso total ao sistema
- **Médico**: Acesso às suas consultas, prontuários de seus pacientes
- **Paciente**: Acesso às suas consultas e agendamentos

## Integração com Frontend

Para integrar com o frontend HTML/CSS/JS existente:

1. Configure o CORS no Laravel para permitir requisições do frontend
2. Use JavaScript para fazer requisições aos endpoints da API
3. Implemente o fluxo de autenticação usando tokens
4. Adapte os formulários existentes para enviar dados para a API

## Próximos Passos

- Implementar testes automatizados
- Configurar ambiente de produção com MySQL
- Implementar sistema de notificações
- Adicionar relatórios e dashboards
# SGCM
