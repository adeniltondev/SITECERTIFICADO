<?php
require_once 'config/database.php';

$resultado = null;
$erro = null;
$codigo_busca = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo'])) {
    $codigo_busca = trim($_POST['codigo']);
    
    if (empty($codigo_busca)) {
        $erro = "Por favor, informe o código do certificado.";
    } else {
        try {
            $pdo = getConnection();
            
            // Buscar certificado
            $stmt = $pdo->prepare("SELECT * FROM certificados WHERE codigo = ? AND status = 'ativo'");
            $stmt->execute([$codigo_busca]);
            $resultado = $stmt->fetch();
            
            // Registrar log
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
            $logStmt = $pdo->prepare("INSERT INTO logs_validacao (certificado_id, codigo_buscado, ip_acesso, encontrado) VALUES (?, ?, ?, ?)");
            $logStmt->execute([
                $resultado ? $resultado['id'] : null,
                $codigo_busca,
                $ip,
                $resultado ? 1 : 0
            ]);
            
            if (!$resultado) {
                $erro = "Certificado não encontrado ou revogado. Verifique o código informado.";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao consultar. Tente novamente mais tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <i class="fas fa-certificate"></i>
                <h1><?php echo SITE_NAME; ?></h1>
            </div>
            <p class="subtitle">Verifique a autenticidade do seu certificado</p>
        </header>

        <!-- Formulário de Busca -->
        <main class="main-content">
            <div class="search-box">
                <h2><i class="fas fa-search"></i> Validar Certificado</h2>
                <form method="POST" action="">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="codigo" 
                            id="codigo"
                            placeholder="Digite o código do certificado (Ex: CERT-2026-001)"
                            value="<?php echo htmlspecialchars($codigo_busca); ?>"
                            required
                            autocomplete="off"
                        >
                        <button type="submit">
                            <i class="fas fa-check-circle"></i> Validar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resultado da Busca -->
            <?php if ($erro): ?>
                <div class="result-box error">
                    <div class="result-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3>Certificado Não Encontrado</h3>
                    <p><?php echo htmlspecialchars($erro); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($resultado): ?>
                <div class="result-box success">
                    <div class="result-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Certificado Válido!</h3>
                    <p>Este certificado é autêntico e foi emitido por nossa instituição.</p>
                    
                    <div class="certificate-details">
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-hashtag"></i> Código:</span>
                            <span class="value"><?php echo htmlspecialchars($resultado['codigo']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-user"></i> Nome:</span>
                            <span class="value"><?php echo htmlspecialchars($resultado['nome_aluno']); ?></span>
                        </div>
                        <?php if ($resultado['cpf']): ?>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-id-card"></i> CPF:</span>
                            <span class="value"><?php echo htmlspecialchars(substr($resultado['cpf'], 0, 3) . '.***.' . substr($resultado['cpf'], -6)); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-graduation-cap"></i> Curso:</span>
                            <span class="value"><?php echo htmlspecialchars($resultado['curso']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-clock"></i> Carga Horária:</span>
                            <span class="value"><?php echo $resultado['carga_horaria']; ?> horas</span>
                        </div>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-calendar-check"></i> Data de Conclusão:</span>
                            <span class="value"><?php echo date('d/m/Y', strtotime($resultado['data_conclusao'])); ?></span>
                        </div>
                        <?php if ($resultado['nota']): ?>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-star"></i> Nota:</span>
                            <span class="value"><?php echo number_format($resultado['nota'], 2, ',', '.'); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-building"></i> Instituição:</span>
                            <span class="value"><?php echo htmlspecialchars($resultado['instituicao']); ?></span>
                        </div>
                    </div>

                    <div class="validation-stamp">
                        <i class="fas fa-shield-alt"></i>
                        <span>Validado em <?php echo date('d/m/Y \à\s H:i'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Instruções -->
            <div class="instructions">
                <h3><i class="fas fa-info-circle"></i> Como validar seu certificado</h3>
                <ol>
                    <li>Localize o código de validação no seu certificado</li>
                    <li>Digite o código no campo acima</li>
                    <li>Clique em "Validar" para verificar a autenticidade</li>
                </ol>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
            <p><a href="admin/login.php"><i class="fas fa-lock"></i> Área Administrativa</a></p>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
