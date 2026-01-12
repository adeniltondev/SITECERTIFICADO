<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getConnection();
$mensagem = '';
$tipo_mensagem = '';

// Diretório para armazenar PDFs
$pdf_dir = '../assets/pdf/';
if (!is_dir($pdf_dir)) {
    mkdir($pdf_dir, 0755, true);
}

function sanitizeFilename($filename) {
    // Remove extensão original
    $filename = pathinfo($filename, PATHINFO_FILENAME);
    // Remove caracteres especiais
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    return substr($filename, 0, 50);
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
    $permitir_download = isset($_POST['permitir_download']) ? 1 : 0;
    
    $arquivo_pdf = null;
    
    // Processar upload do PDF
    if (!empty($_FILES['pdf']['name'])) {
        $file = $_FILES['pdf'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $mensagem = 'Erro ao fazer upload do arquivo.';
            $tipo_mensagem = 'error';
        } elseif ($file['size'] > $max_size) {
            $mensagem = 'O arquivo é muito grande. Máximo 10MB.';
            $tipo_mensagem = 'error';
        } else {
            // Validar se é um PDF real (magic bytes)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            // Verificar magic bytes do PDF
            $handle = fopen($file['tmp_name'], 'r');
            $header = fread($handle, 5);
            fclose($handle);
            
            if ($mime_type !== 'application/pdf' || strpos($header, '%PDF') === false) {
                $mensagem = 'O arquivo enviado não é um PDF válido.';
                $tipo_mensagem = 'error';
            } else {
                // Gerar nome único para o arquivo
                $arquivo_pdf = 'cert_' . time() . '_' . sanitizeFilename($nome_aluno) . '.pdf';
                $upload_path = $pdf_dir . $arquivo_pdf;
                
                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $mensagem = 'Erro ao salvar o arquivo.';
                    $tipo_mensagem = 'error';
                    $arquivo_pdf = null;
                }
            }
        }
    }
    
    if (empty($codigo) || empty($nome_aluno) || empty($curso) || empty($data_conclusao) || $carga_horaria <= 0) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'error';
    } elseif ($tipo_mensagem !== 'error') {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM certificados WHERE codigo = ?");
            $checkStmt->execute([$codigo]);
            
            if ($checkStmt->fetch()) {
                $mensagem = 'Este código de certificado já existe.';
                $tipo_mensagem = 'error';
            } else {
                $stmt = $pdo->prepare("INSERT INTO certificados 
                    (codigo, nome_aluno, cpf, curso, carga_horaria, data_inicio, data_conclusao, nota, instituicao, observacoes, arquivo_pdf, permitir_download) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $codigo, $nome_aluno, $cpf ?: null, $curso, $carga_horaria,
                    $data_inicio ?: null, $data_conclusao, $nota,
                    $instituicao ?: 'Instituição', $observacoes ?: null, $arquivo_pdf, $permitir_download
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
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50">
    <div class="min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-800">Novo Certificado</h1>
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

            <form method="POST" action="" class="space-y-6" enctype="multipart/form-data">
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
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    name="codigo" 
                                    id="codigo"
                                    required
                                    placeholder="Ex: CERT-2026-001"
                                    value="<?php echo htmlspecialchars($_POST['codigo'] ?? $codigo_sugerido); ?>"
                                    class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                                >
                                <button type="button" id="gerarCodigo" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-colors" title="Gerar código">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nome_aluno" 
                                required
                                placeholder="Nome do aluno"
                                value="<?php echo htmlspecialchars($_POST['nome_aluno'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">CPF</label>
                            <input 
                                type="text" 
                                name="cpf"
                                id="cpf"
                                placeholder="000.000.000-00"
                                maxlength="14"
                                value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Instituição</label>
                            <input 
                                type="text" 
                                name="instituicao"
                                placeholder="Nome da instituição"
                                value="<?php echo htmlspecialchars($_POST['instituicao'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
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
                                placeholder="Ex: Curso de Programação PHP"
                                value="<?php echo htmlspecialchars($_POST['curso'] ?? ''); ?>"
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
                                placeholder="Ex: 40"
                                value="<?php echo htmlspecialchars($_POST['carga_horaria'] ?? ''); ?>"
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
                                placeholder="Ex: 9.50"
                                value="<?php echo htmlspecialchars($_POST['nota'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Data de Início</label>
                            <input 
                                type="date" 
                                name="data_inicio"
                                value="<?php echo htmlspecialchars($_POST['data_inicio'] ?? ''); ?>"
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
                                value="<?php echo htmlspecialchars($_POST['data_conclusao'] ?? date('Y-m-d')); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:border-primary-500 focus:bg-white transition-all"
                            >
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Observações</label>
                            <textarea 
                                name="observacoes"
                                rows="3"
                                placeholder="Observações adicionais (opcional)"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all resize-none"
                            ><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Arquivo PDF -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        Arquivo PDF do Certificado
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Upload do PDF</label>
                            <div class="relative">
                                <input 
                                    type="file" 
                                    name="pdf"
                                    id="pdf"
                                    accept=".pdf"
                                    class="hidden"
                                >
                                <label for="pdf" class="block px-4 py-8 border-2 border-dashed border-gray-300 rounded-xl text-center cursor-pointer hover:border-primary-500 hover:bg-blue-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    <p class="text-sm text-gray-700 font-medium">Clique ou arraste um arquivo PDF</p>
                                    <p class="text-xs text-gray-500 mt-1">Máximo 10MB</p>
                                </label>
                            </div>
                            <p id="fileName" class="text-sm text-gray-600 mt-2"></p>
                        </div>

                        <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <input 
                                type="checkbox" 
                                name="permitir_download"
                                id="permitir_download"
                                checked
                                class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                            <label for="permitir_download" class="text-sm text-gray-700">
                                Permitir download do PDF no site (usuários poderão baixar o certificado)
                            </label>
                        </div>

                        <p class="text-xs text-gray-500 bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <strong>Dica:</strong> O PDF é opcional. Se não quiser enviar, deixe em branco. A opção de download só aparecerá se um PDF for enviado.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 justify-end">
                    <a href="index.php" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors text-center flex items-center justify-center gap-2">
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
    <script>
        // Manipular upload de arquivo
        const pdfInput = document.getElementById('pdf');
        const fileNameDisplay = document.getElementById('fileName');

        pdfInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = '✓ Arquivo selecionado: ' + this.files[0].name;
                fileNameDisplay.classList.remove('text-red-600');
                fileNameDisplay.classList.add('text-emerald-600');
            } else {
                fileNameDisplay.textContent = '';
            }
        });

        // Gerar código
        document.getElementById('gerarCodigo').addEventListener('click', function() {
            const ano = new Date().getFullYear();
            const random = Math.floor(Math.random() * 999) + 1;
            const codigo = 'CERT-' + ano + '-' + String(random).padStart(3, '0');
            document.getElementById('codigo').value = codigo;
        });
    </script>
</body>
</html>
