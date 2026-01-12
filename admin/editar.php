<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getConnection();
$mensagem = '';
$tipo_mensagem = '';

// Verificar ID
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Buscar certificado
$stmt = $pdo->prepare("SELECT * FROM certificados WHERE id = ?");
$stmt->execute([$id]);
$certificado = $stmt->fetch();

if (!$certificado) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nome_aluno = trim($_POST['nome_aluno'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $curso = trim($_POST['curso'] ?? '');
    $carga_horaria = intval($_POST['carga_horaria'] ?? 0);
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_conclusao = $_POST['data_conclusao'] ?? '';
    $nota = !empty($_POST['nota']) ? floatval($_POST['nota']) : null;
    $instituicao = trim($_POST['instituicao'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $status = $_POST['status'] ?? 'ativo';
    
    if (empty($codigo) || empty($nome_aluno) || empty($curso) || empty($data_conclusao) || $carga_horaria <= 0) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'error';
    } else {
        try {
            // Verificar se código já existe (exceto o atual)
            $checkStmt = $pdo->prepare("SELECT id FROM certificados WHERE codigo = ? AND id != ?");
            $checkStmt->execute([$codigo, $id]);
            
            if ($checkStmt->fetch()) {
                $mensagem = 'Este código de certificado já existe.';
                $tipo_mensagem = 'error';
            } else {
                $stmt = $pdo->prepare("UPDATE certificados SET 
                    codigo = ?, nome_aluno = ?, cpf = ?, curso = ?, carga_horaria = ?, 
                    data_inicio = ?, data_conclusao = ?, nota = ?, instituicao = ?, 
                    observacoes = ?, status = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $codigo,
                    $nome_aluno,
                    $cpf ?: null,
                    $curso,
                    $carga_horaria,
                    $data_inicio ?: null,
                    $data_conclusao,
                    $nota,
                    $instituicao ?: 'Instituição',
                    $observacoes ?: null,
                    $status,
                    $id
                ]);
                
                $mensagem = 'Certificado atualizado com sucesso!';
                $tipo_mensagem = 'success';
                
                // Recarregar dados
                $stmt = $pdo->prepare("SELECT * FROM certificados WHERE id = ?");
                $stmt->execute([$id]);
                $certificado = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro ao atualizar certificado.';
            $tipo_mensagem = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Certificado</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container admin-container">
        <header class="header">
            <div class="admin-header">
                <div class="logo">
                    <i class="fas fa-edit"></i>
                    <h1>Editar Certificado</h1>
                </div>
                <nav class="admin-nav">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Voltar</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <div class="search-box">
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        
                        <div class="form-group">
                            <label for="codigo"><i class="fas fa-hashtag"></i> Código do Certificado *</label>
                            <input 
                                type="text" 
                                name="codigo" 
                                id="codigo" 
                                required
                                value="<?php echo htmlspecialchars($certificado['codigo']); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="nome_aluno"><i class="fas fa-user"></i> Nome Completo *</label>
                            <input 
                                type="text" 
                                name="nome_aluno" 
                                id="nome_aluno" 
                                required
                                value="<?php echo htmlspecialchars($certificado['nome_aluno']); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="cpf"><i class="fas fa-id-card"></i> CPF</label>
                            <input 
                                type="text" 
                                name="cpf" 
                                id="cpf"
                                maxlength="14"
                                value="<?php echo htmlspecialchars($certificado['cpf'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="curso"><i class="fas fa-graduation-cap"></i> Nome do Curso *</label>
                            <input 
                                type="text" 
                                name="curso" 
                                id="curso" 
                                required
                                value="<?php echo htmlspecialchars($certificado['curso']); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="carga_horaria"><i class="fas fa-clock"></i> Carga Horária (horas) *</label>
                            <input 
                                type="number" 
                                name="carga_horaria" 
                                id="carga_horaria" 
                                required
                                min="1"
                                value="<?php echo $certificado['carga_horaria']; ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="nota"><i class="fas fa-star"></i> Nota</label>
                            <input 
                                type="number" 
                                name="nota" 
                                id="nota"
                                min="0"
                                max="10"
                                step="0.01"
                                value="<?php echo $certificado['nota'] ?? ''; ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="data_inicio"><i class="fas fa-calendar"></i> Data de Início</label>
                            <input 
                                type="date" 
                                name="data_inicio" 
                                id="data_inicio"
                                value="<?php echo $certificado['data_inicio'] ?? ''; ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="data_conclusao"><i class="fas fa-calendar-check"></i> Data de Conclusão *</label>
                            <input 
                                type="date" 
                                name="data_conclusao" 
                                id="data_conclusao" 
                                required
                                value="<?php echo $certificado['data_conclusao']; ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="status"><i class="fas fa-toggle-on"></i> Status</label>
                            <select name="status" id="status">
                                <option value="ativo" <?php echo $certificado['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="revogado" <?php echo $certificado['status'] === 'revogado' ? 'selected' : ''; ?>>Revogado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="instituicao"><i class="fas fa-building"></i> Instituição</label>
                            <input 
                                type="text" 
                                name="instituicao" 
                                id="instituicao"
                                value="<?php echo htmlspecialchars($certificado['instituicao'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                            <textarea 
                                name="observacoes" 
                                id="observacoes"
                            ><?php echo htmlspecialchars($certificado['observacoes'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div style="margin-top: 20px; padding: 15px; background: var(--bg-light); border-radius: 8px; font-size: 0.9rem; color: var(--text-light);">
                        <p><i class="fas fa-info-circle"></i> Cadastrado em: <?php echo date('d/m/Y H:i', strtotime($certificado['criado_em'])); ?></p>
                        <p><i class="fas fa-clock"></i> Última atualização: <?php echo date('d/m/Y H:i', strtotime($certificado['atualizado_em'])); ?></p>
                    </div>

                    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                        <a href="index.php" class="btn">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <footer class="footer">
            <p>* Campos obrigatórios</p>
        </footer>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
