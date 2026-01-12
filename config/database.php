<?php
/**
 * Configuração do Banco de Dados
 * Hostinger - Altere os dados conforme seu painel
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'alunofaculdadepr_bancoteste'); // Altere para o nome do seu banco
define('DB_USER', 'alunofaculdadepr_bacntotesdsd');      // Altere para seu usuário
define('DB_PASS', 'nesi&Kb3PT6bdouZ');        // Altere para sua senha

// Conexão PDO
function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Configurações do site
define('SITE_NAME', 'Validador de Certificados');
define('SITE_URL', 'https://alunofaculdadeprominas.com/novosite'); // Altere para sua URL

// Timezone Brasil
date_default_timezone_set('America/Sao_Paulo');

// Sessão segura
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
