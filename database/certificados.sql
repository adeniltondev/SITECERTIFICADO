-- =============================================
-- BANCO DE DADOS - VALIDADOR DE CERTIFICADOS
-- Execute este SQL no phpMyAdmin da Hostinger
-- =============================================

CREATE TABLE IF NOT EXISTS `certificados` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(50) NOT NULL UNIQUE,
    `nome_aluno` VARCHAR(255) NOT NULL,
    `cpf` VARCHAR(14) DEFAULT NULL,
    `curso` VARCHAR(255) NOT NULL,
    `carga_horaria` INT(11) NOT NULL,
    `data_inicio` DATE DEFAULT NULL,
    `data_conclusao` DATE NOT NULL,
    `nota` DECIMAL(5,2) DEFAULT NULL,
    `instituicao` VARCHAR(255) DEFAULT 'Nome da Instituição',
    `status` ENUM('ativo', 'revogado') DEFAULT 'ativo',
    `observacoes` TEXT DEFAULT NULL,
    `arquivo_pdf` VARCHAR(255) DEFAULT NULL,
    `permitir_download` TINYINT(1) DEFAULT 1,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_codigo` (`codigo`),
    INDEX `idx_cpf` (`cpf`),
    INDEX `idx_nome` (`nome_aluno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de administradores
CREATE TABLE IF NOT EXISTS `administradores` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de acesso
CREATE TABLE IF NOT EXISTS `logs_validacao` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `certificado_id` INT(11) DEFAULT NULL,
    `codigo_buscado` VARCHAR(50) NOT NULL,
    `ip_acesso` VARCHAR(45) DEFAULT NULL,
    `encontrado` TINYINT(1) DEFAULT 0,
    `data_acesso` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`certificado_id`) REFERENCES `certificados`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir administrador padrão (senha: admin123 - ALTERE APÓS O PRIMEIRO ACESSO!)
INSERT INTO `administradores` (`nome`, `email`, `senha`) VALUES 
('Administrador', 'admin@seusite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir certificados de exemplo
INSERT INTO `certificados` (`codigo`, `nome_aluno`, `cpf`, `curso`, `carga_horaria`, `data_inicio`, `data_conclusao`, `nota`, `instituicao`) VALUES 
('CERT-2026-001', 'João da Silva', '123.456.789-00', 'Curso de Programação PHP', 40, '2025-12-01', '2026-01-10', 9.50, 'Instituto de Tecnologia'),
('CERT-2026-002', 'Maria Santos', '987.654.321-00', 'Curso de Design Gráfico', 60, '2025-11-15', '2026-01-05', 8.75, 'Instituto de Tecnologia'),
('CERT-2026-003', 'Pedro Oliveira', '456.789.123-00', 'Curso de Marketing Digital', 30, '2025-12-10', '2026-01-08', 10.00, 'Instituto de Tecnologia');
