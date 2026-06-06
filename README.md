# MediAgenda

Sistema web em PHP para agendamento de consultas médicas, com login, agenda mensal, cadastro de médicos, cadastro de especialidades e integração com MySQL/MariaDB.

## Descrição

O projeto foi evoluído para atender ao trabalho acadêmico de Programação Web, mantendo o padrão visual do MediAgenda e integrando os módulos principais com o banco de dados.

## Funcionalidades implementadas

- Login de usuário com sessão.
- Visualização da agenda mensal no painel principal.
- CRUD de médicos com listagem, edição e inativação.
- CRUD de especialidades com listagem, edição e inativação.
- Cadastro de agendamentos integrado a médicos e especialidades.
- Navegação lateral com acesso para Agenda, Agendamentos, Médicos, Especialidades e Regras do trabalho.

## Tecnologias utilizadas

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- SweetAlert2
- Git e GitHub

## Como executar

### Opção 1: com Docker

1. Abra um terminal na raiz do projeto.
2. Suba os containers com:

```bash
docker-compose up -d --build
```

3. Acesse `http://localhost:8080/login.php` no navegador.
4. O phpMyAdmin ficará disponível em `http://localhost:8081`.

### Opção 2: sem Docker

1. Import o arquivo `script.sql` no MySQL ou MariaDB.
2. Ajuste a conexão em `www/conexao.php` se necessário.
3. Sirva a pasta `www` com Apache, Nginx ou PHP embutido.
4. Acesse `login.php` pela URL do servidor local.

### Usuários de teste

- Usuário: `aluno` | Senha: `123456`
- Usuário: `professor` | Senha: `professor123`

## Estrutura principal

- `www/login.php`
- `www/principal.php`
- `www/cadastro_agendas.php`
- `www/cadastro_medicos.php`
- `www/cadastro_especialidades.php`
- `www/regras_trabalho.php`
- `www/logout.php`
- `www/conexao.php`
- `script.sql`

## Integrantes do grupo

- João Pedro de Almeida Modesto
- Gustavo Vieira Barbosa
- Gustavo Amaral
- Maria Olivia Cassucci
- Matheus Vicente
- Pablo Victor
- Victor Gmeiner

## Observação

Este projeto foi desenvolvido para fins acadêmicos e funciona melhor com o ambiente Docker definido no repositório.
