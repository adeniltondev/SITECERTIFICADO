<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getConnection();
$mensagem = '';
$tipo_mensagem = '';

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
    
    if (empty($codigo) || empty($nome_aluno) || empty($curso) || empty($data_conclusao) || $carga_horaria <= 0) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'error';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM certificados WHERE codigo = ?");
            $checkStmt->execute([$codigo]);
            
            if ($checkStmt->fetch()) {
                $mensagem = 'Este código de certificado já existe.';
                $tipo_mensagem = 'error';
            } else {
                $stmt = $pdo->prepare("INSERT INTO certificados 
                    (codigo, nome_aluno, cpf, curso, carga_horaria, data_inicio, data_conclusao, nota, instituicao, observacoes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $codigo, $nome_aluno, $cpf ?: null, $curso, $carga_horaria,
                    $data_inicio ?: null, $data_conclusao, $nota,
                    $instituicao ?: 'Instituição', $observacoes ?: null
                ]);
                
                $mensagem = 'Certificado cadastrado com sucesso!';
                $tipo_mensagem = 'success';
                $_POST = [];
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro ao cadastrar certificado.';
            $tipo_mensagem = 'error';
        }
    }
}

$ano = date('Y');
$countStmt = $pdo->query("SELECT COUNT(*) + 1 as proximo FROM certificados WHERE YEAR(criado_em) = $ano");
$proximo = $countStmt->fetch()['proximo'];
$codigo_sugerido = sprintf("CERT-%d-%03d", $ano, $proximo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Certificado</title>
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
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="min-h-screen">
        <!-- Top Bar -->
        <header class="bg-slate-800/50 backdrop-blur-xl border-b border-white/10">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="p-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    <h1 class="text-xl font-bold text-white">Novo Certificado</h1>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto p-4 lg:p-8">
            <?php if ($mensagem): ?>
            <div class="mb-6 flex items-center gap-3 p-4 <?php echo $tipo_mensagem === 'success' ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-red-500/10 border-red-500/30'; ?> border rounded-xl">
                <?php if ($tipo_mensagem === 'success'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-emerald-300"><?php echo htmlspecialchars($mensagem); ?></span>
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-red-300"><?php echo htmlspecialchars($mensagem); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <!-- Informações Básicas -->
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Informações Básicas
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">
                                Código do Certificado <span class="text-red-400">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    name="codigo" 
                                    id="codigo"
                                    required
                                    placeholder="Ex: CERT-2026-001"
                                    value="<?php echo htmlspecialchars($_POST['codigo'] ?? $codigo_sugerido); ?>"
                                    class="flex-1 px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                                >
                                <button type="button" id="gerarCodigo" class="px-4 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors" title="Gerar código">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">
                                Nome Completo <span class="text-red-400">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nome_aluno" 
                                required
                                placeholder="Nome do aluno"
                                value="<?php echo htmlspecialchars($_POST['nome_aluno'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">CPF</label>
                            <input 
                                type="text" 
                                name="cpf"
                                id="cpf"
                                placeholder="000.000.000-00"
                                maxlength="14"
                                value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">Instituição</label>
                            <input 
                                type="text" 
                                name="instituicao"
                                placeholder="Nome da instituição"
                                value="<?php echo htmlspecialchars($_POST['instituicao'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>
                    </div>
                </div>

                <!-- Informações do Curso -->
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                        Informações do Curso
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-slate-300 text-sm font-medium mb-2">
                                Nome do Curso <span class="text-red-400">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="curso"
                                required
                                placeholder="Ex: Curso de Programação PHP"
                                value="<?php echo htmlspecialchars($_POST['curso'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">
                                Carga Horária (horas) <span class="text-red-400">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="carga_horaria"
                                required
                                min="1"
                                placeholder="Ex: 40"
                                value="<?php echo htmlspecialchars($_POST['carga_horaria'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">Nota (0-10)</label>
                            <input 
                                type="number" 
                                name="nota"
                                min="0"
                                max="10"
                                step="0.01"
                                placeholder="Ex: 9.50"
                                value="<?php echo htmlspecialchars($_POST['nota'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">Data de Início</label>
                            <input 
                                type="date" 
                                name="data_inicio"
                                value="<?php echo htmlspecialchars($_POST['data_inicio'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-medium mb-2">
                                Data de Conclusão <span class="text-red-400">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="data_conclusao"
                                required
                                value="<?php echo htmlspecialchars($_POST['data_conclusao'] ?? date('Y-m-d')); ?>"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white focus:outline-none focus:border-primary-500 transition-all"
                            >
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-slate-300 text-sm font-medium mb-2">Observações</label>
                            <textarea 
                                name="observacoes"
                                rows="3"
                                placeholder="Observações adicionais (opcional)"
                                class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary-500 transition-all resize-none"
                            ><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 justify-end">
                    <a href="index.php" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors text-center flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        Cancelar
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Salvar Certificado
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
