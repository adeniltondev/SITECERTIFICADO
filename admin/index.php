<?php
require_once '../config/database.php';

// Verificar autenticação
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getConnection();
$mensagem = '';
$tipo_mensagem = '';

// Excluir certificado
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM certificados WHERE id = ?");
        $stmt->execute([$_GET['excluir']]);
        $mensagem = 'Certificado excluído com sucesso!';
        $tipo_mensagem = 'success';
    } catch (PDOException $e) {
        $mensagem = 'Erro ao excluir certificado.';
        $tipo_mensagem = 'error';
    }
}

// Alterar status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $novo_status = $_GET['status'] === 'ativar' ? 'ativo' : 'revogado';
    try {
        $stmt = $pdo->prepare("UPDATE certificados SET status = ? WHERE id = ?");
        $stmt->execute([$novo_status, $_GET['id']]);
        $mensagem = 'Status alterado com sucesso!';
        $tipo_mensagem = 'success';
    } catch (PDOException $e) {
        $mensagem = 'Erro ao alterar status.';
        $tipo_mensagem = 'error';
    }
}

// Busca
$busca = $_GET['busca'] ?? '';
$where = '';
$params = [];

if (!empty($busca)) {
    $where = "WHERE codigo LIKE ? OR nome_aluno LIKE ? OR curso LIKE ? OR cpf LIKE ?";
    $params = ["%$busca%", "%$busca%", "%$busca%", "%$busca%"];
}

// Listar certificados
$stmt = $pdo->prepare("SELECT * FROM certificados $where ORDER BY criado_em DESC");
$stmt->execute($params);
$certificados = $stmt->fetchAll();

// Estatísticas
$statsStmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos,
    SUM(CASE WHEN status = 'revogado' THEN 1 ELSE 0 END) as revogados
    FROM certificados");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Certificados</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container admin-container">
        <header class="header">
            <div class="admin-header">
                <div class="logo">
                    <i class="fas fa-certificate"></i>
                    <h1>Painel Administrativo</h1>
                </div>
                <nav class="admin-nav">
                    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Site</a>
                    <a href="cadastrar.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Certificado</a>
                    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <!-- Estatísticas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="search-box" style="text-align: center; padding: 20px;">
                    <h3 style="color: var(--primary-color); font-size: 2rem;"><?php echo $stats['total']; ?></h3>
                    <p>Total de Certificados</p>
                </div>
                <div class="search-box" style="text-align: center; padding: 20px;">
                    <h3 style="color: var(--success-color); font-size: 2rem;"><?php echo $stats['ativos']; ?></h3>
                    <p>Certificados Ativos</p>
                </div>
                <div class="search-box" style="text-align: center; padding: 20px;">
                    <h3 style="color: var(--error-color); font-size: 2rem;"><?php echo $stats['revogados']; ?></h3>
                    <p>Certificados Revogados</p>
                </div>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <!-- Busca -->
            <div class="search-box">
                <form method="GET" action="">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="busca" 
                            placeholder="Buscar por código, nome, CPF ou curso..."
                            value="<?php echo htmlspecialchars($busca); ?>"
                        >
                        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                        <?php if ($busca): ?>
                            <a href="index.php" class="btn"><i class="fas fa-times"></i> Limpar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabela de Certificados -->
            <div class="table-container">
                <h2><i class="fas fa-list"></i> Certificados Cadastrados</h2>
                
                <?php if (empty($certificados)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-light);">
                        <i class="fas fa-inbox" style="font-size: 3rem; display: block; margin-bottom: 15px;"></i>
                        Nenhum certificado encontrado.
                    </p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nome</th>
                                <th>Curso</th>
                                <th>Conclusão</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificados as $cert): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cert['codigo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cert['nome_aluno']); ?></td>
                                <td><?php echo htmlspecialchars($cert['curso']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cert['data_conclusao'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $cert['status']; ?>">
                                        <?php echo ucfirst($cert['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="editar.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($cert['status'] === 'ativo'): ?>
                                            <a href="?status=revogar&id=<?php echo $cert['id']; ?>" class="btn btn-sm" title="Revogar" onclick="return confirm('Revogar este certificado?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?status=ativar&id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-success" title="Ativar">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?excluir=<?php echo $cert['id']; ?>" class="btn btn-sm btn-danger btn-delete" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>

        <footer class="footer">
            <p>Logado como: <strong><?php echo htmlspecialchars($_SESSION['admin_nome']); ?></strong></p>
        </footer>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
