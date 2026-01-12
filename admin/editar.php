<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getConnection();
$mensagem = '';
$tipo_mensagem = '';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

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
    $permitir_download = isset($_POST['permitir_download']) ? 1 : 0;
    
    if (empty($codigo) || empty($nome_aluno) || empty($curso) || empty($data_conclusao) || $carga_horaria <= 0) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'error';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM certificados WHERE codigo = ? AND id != ?");
            $checkStmt->execute([$codigo, $id]);
            
            if ($checkStmt->fetch()) {
                $mensagem = 'Este código de certificado já existe.';
                $tipo_mensagem = 'error';
            } else {
                $stmt = $pdo->prepare("UPDATE certificados SET 
                    codigo = ?, nome_aluno = ?, cpf = ?, curso = ?, carga_horaria = ?, 
                    data_inicio = ?, data_conclusao = ?, nota = ?, instituicao = ?, 
                    observacoes = ?, status = ?, permitir_download = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $codigo, $nome_aluno, $cpf ?: null, $curso, $carga_horaria,
                    $data_inicio ?: null, $data_conclusao, $nota,
                    $instituicao ?: 'Instituição', $observacoes ?: null, $status, $permitir_download, $id
                ]);
                
                $mensagem = 'Certificado atualizado com sucesso!';
                $tipo_mensagem = 'success';
                
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50">
    <div class="min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Editar Certificado</h1>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($certificado['codigo']); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($certificado['status'] === 'ativo'): ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-sm font-medium">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        Ativo
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-50 text-red-600 rounded-full text-sm font-medium">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        Revogado
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto p-4 lg:p-8">
            <?php if ($mensagem): ?>
            <div class="mb-6 flex items-center gap-3 p-4 <?php echo $tipo_mensagem === 'success' ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200'; ?> border rounded-xl">
                <?php if ($tipo_mensagem === 'success'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-emerald-700"><?php echo htmlspecialchars($mensagem); ?></span>
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-red-700"><?php echo htmlspecialchars($mensagem); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <!-- Informações Básicas -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Informações Básicas
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                Código do Certificado <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="codigo"
                                required
                                value="<?php echo htmlspecialchars($certificado['codigo']); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nome_aluno"
                                required
                                value="<?php echo htmlspecialchars($certificado['nome_aluno']); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">CPF</label>
                            <input 
                                type="text" 
                                name="cpf"
                                id="cpf"
                                maxlength="14"
                                value="<?php echo htmlspecialchars($certificado['cpf'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Instituição</label>
                            <input 
                                type="text" 
                                name="instituicao"
                                value="<?php echo htmlspecialchars($certificado['instituicao'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Status</label>
                            <select 
                                name="status"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                                <option value="ativo" <?php echo $certificado['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="revogado" <?php echo $certificado['status'] === 'revogado' ? 'selected' : ''; ?>>Revogado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Informações do Curso -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                        Informações do Curso
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                Nome do Curso <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="curso"
                                required
                                value="<?php echo htmlspecialchars($certificado['curso']); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                Carga Horária (horas) <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="carga_horaria"
                                required
                                min="1"
                                value="<?php echo $certificado['carga_horaria']; ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Nota (0-10)</label>
                            <input 
                                type="number" 
                                name="nota"
                                min="0"
                                max="10"
                                step="0.01"
                                value="<?php echo $certificado['nota'] ?? ''; ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Data de Início</label>
                            <input 
                                type="date" 
                                name="data_inicio"
                                value="<?php echo $certificado['data_inicio'] ?? ''; ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                Data de Conclusão <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="data_conclusao"
                                required
                                value="<?php echo $certificado['data_conclusao']; ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Observações</label>
                            <textarea 
                                name="observacoes"
                                rows="3"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all resize-none"
                            ><?php echo htmlspecialchars($certificado['observacoes'] ?? ''); ?></textarea>
                        </div>

                        <?php if (!empty($certificado['arquivo_pdf'])): ?>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-medium mb-2 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                Arquivo PDF Anexado
                            </label>
                            <div class="flex items-center gap-3 bg-blue-50 p-4 rounded-xl border border-blue-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M7 3a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H7z"/></svg>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 font-medium"><?php echo htmlspecialchars($certificado['arquivo_pdf']); ?></p>
                                </div>
                                <a href="../assets/pdf/<?php echo htmlspecialchars($certificado['arquivo_pdf']); ?>" target="_blank" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Visualizar</a>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-medium mb-3">Opções do PDF</label>
                            <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <input 
                                    type="checkbox" 
                                    name="permitir_download"
                                    id="permitir_download"
                                    <?php echo $certificado['permitir_download'] ? 'checked' : ''; ?>
                                    class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                >
                                <label for="permitir_download" class="text-sm text-gray-700">
                                    Permitir download do PDF no site
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Quando habilitado, usuários poderão baixar o certificado em PDF ao validar.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex flex-wrap gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Cadastrado em: <?php echo date('d/m/Y H:i', strtotime($certificado['criado_em'])); ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Atualizado em: <?php echo date('d/m/Y H:i', strtotime($certificado['atualizado_em'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 justify-end">
                    <a href="index.php" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors text-center flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        Cancelar
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/30 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
