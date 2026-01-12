# Validador de Certificados - PHP

Sistema completo para validação de certificados online, desenvolvido em PHP para hospedagem na Hostinger.

## 📁 Estrutura do Projeto

```
SITECERTIFICADO/
├── admin/
│   ├── index.php       # Painel administrativo
│   ├── login.php       # Tela de login
│   ├── logout.php      # Logout
│   ├── cadastrar.php   # Cadastrar certificado
│   └── editar.php      # Editar certificado
├── assets/
│   ├── css/
│   │   └── style.css   # Estilos do site
│   └── js/
│       └── main.js     # JavaScript
├── config/
│   └── database.php    # Configuração do banco
├── database/
│   └── certificados.sql # SQL para criar tabelas
├── index.php           # Página de validação
├── .htaccess           # Configurações Apache
└── README.md           # Este arquivo
```

## 🚀 Como Instalar na Hostinger

### 1. Criar Banco de Dados
1. Acesse o **hPanel** da Hostinger
2. Vá em **Banco de Dados** → **MySQL**
3. Crie um novo banco de dados
4. Anote: nome do banco, usuário e senha

### 2. Importar Tabelas
1. Acesse o **phpMyAdmin**
2. Selecione seu banco de dados
3. Vá em **Importar**
4. Envie o arquivo `database/certificados.sql`

### 3. Configurar Conexão
1. Edite o arquivo `config/database.php`
2. Altere as constantes:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_banco_dados');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('SITE_URL', 'https://seusite.com');
```

### 4. Fazer Upload
1. Acesse o **Gerenciador de Arquivos** ou use FTP
2. Faça upload de todos os arquivos para `public_html`

### 5. Primeiro Acesso
- Acesse: `seusite.com/admin/login.php`
- **Email:** admin@seusite.com
- **Senha:** admin123
- ⚠️ **IMPORTANTE:** Altere a senha após o primeiro acesso!

## 🔐 Segurança

- Senhas são criptografadas com `password_hash()`
- Proteção contra SQL Injection com PDO Prepared Statements
- Sessões seguras
- Proteção de arquivos sensíveis via .htaccess
- Logs de todas as validações

## ✨ Funcionalidades

### Página Pública
- Validação de certificados por código
- Design responsivo e moderno
- Exibição segura de dados (CPF parcialmente oculto)
- Registro de log de validações

### Painel Administrativo
- Login seguro
- Dashboard com estatísticas
- Cadastro de certificados
- Edição de certificados
- Exclusão de certificados
- Ativar/Revogar certificados
- Busca avançada
- Geração automática de código

## 📝 Campos do Certificado

| Campo | Obrigatório | Descrição |
|-------|-------------|-----------|
| Código | Sim | Código único de validação |
| Nome | Sim | Nome completo do aluno |
| CPF | Não | Documento do aluno |
| Curso | Sim | Nome do curso/evento |
| Carga Horária | Sim | Horas do curso |
| Data Início | Não | Início do curso |
| Data Conclusão | Sim | Conclusão do curso |
| Nota | Não | Nota final (0 a 10) |
| Instituição | Não | Nome da instituição |
| Observações | Não | Notas adicionais |

## 🎨 Personalização

### Cores
Edite as variáveis CSS em `assets/css/style.css`:
```css
:root {
    --primary-color: #2563eb;
    --success-color: #059669;
    --error-color: #dc2626;
}
```

### Logo
Adicione sua logo na pasta `assets/img/` e atualize o HTML.

## 📞 Suporte

Desenvolvido para funcionar na Hostinger com:
- PHP 7.4+
- MySQL 5.7+
- Apache com mod_rewrite

---
© 2026 - Sistema de Validação de Certificados
