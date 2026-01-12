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
            
            $stmt = $pdo->prepare("SELECT * FROM certificados WHERE codigo = ? AND status = 'ativo'");
            $stmt->execute([$codigo_busca]);
            $resultado = $stmt->fetch();
            
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
    <style>
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .float-animation { animation: float 3s ease-in-out infinite; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.6s ease-out; }
        @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(1.3); opacity: 0; } }
        .pulse-ring::before { content: ''; position: absolute; inset: -8px; border-radius: 50%; border: 3px solid currentColor; animation: pulse-ring 1.5s ease-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50">
    <!-- Background Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gray-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-primary-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col">
        <!-- Header -->
        <header class="pt-12 pb-8 px-4">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl shadow-2xl shadow-primary-500/30 mb-6 float-animation">
                    <!-- Heroicon: badge-check -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-3 tracking-tight"><?php echo SITE_NAME; ?></h1>
                <p class="text-lg text-gray-600">Verifique a autenticidade do seu certificado de forma rápida e segura</p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-4 pb-12">
            <div class="max-w-2xl mx-auto space-y-6">
                
                <!-- Search Box -->
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-200">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-primary-100 rounded-xl">
                            <!-- Heroicon: magnifying-glass -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Validar Certificado</h2>
                    </div>
                    
                    <form method="POST" action="" class="space-y-4">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="codigo" 
                                id="codigo"
                                placeholder="Digite o código (Ex: CERT-2026-001)"
                                value="<?php echo htmlspecialchars($codigo_busca); ?>"
                                required
                                autocomplete="off"
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl text-gray-800 placeholder-gray-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all duration-300 text-lg"
                            >
                        </div>
                        <button type="submit" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-semibold rounded-2xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/50 transition-all duration-300 hover:-translate-y-0.5">
                            <!-- Heroicon: check-circle -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Validar Certificado
                        </button>
                    </form>
                </div>

                <!-- Error Result -->
                <?php if ($erro): ?>
                <div class="fade-in-up bg-red-50 rounded-3xl p-8 border border-red-200">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                            <!-- Heroicon: x-circle -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-red-600 mb-2">Certificado Não Encontrado</h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($erro); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Success Result -->
                <?php if ($resultado): ?>
                <div class="fade-in-up bg-emerald-50 rounded-3xl p-8 border border-emerald-200">
                    <div class="text-center mb-8">
                        <div class="relative inline-flex items-center justify-center w-24 h-24 bg-emerald-100 rounded-full mb-4 pulse-ring text-emerald-500">
                            <!-- Heroicon: check-circle -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-emerald-600 mb-2">Certificado Válido!</h3>
                        <p class="text-gray-600">Este certificado é autêntico e foi emitido por nossa instituição.</p>
                    </div>
                    
                    <!-- Certificate Details -->
                    <div class="bg-white rounded-2xl p-6 space-y-4 shadow-sm">
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                Código
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($resultado['codigo']); ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Nome
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($resultado['nome_aluno']); ?></span>
                        </div>
                        
                        <?php if ($resultado['cpf']): ?>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                CPF
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars(substr($resultado['cpf'], 0, 3) . '.***.' . substr($resultado['cpf'], -6)); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                                Curso
                            </span>
                            <span class="font-semibold text-gray-800 text-right"><?php echo htmlspecialchars($resultado['curso']); ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Carga Horária
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo $resultado['carga_horaria']; ?> horas</span>
                        </div>
                        
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Data de Conclusão
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo date('d/m/Y', strtotime($resultado['data_conclusao'])); ?></span>
                        </div>
                        
                        <?php if ($resultado['nota']): ?>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                Nota
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo number_format($resultado['nota'], 2, ',', '.'); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between py-3 flex-wrap gap-2">
                            <span class="flex items-center gap-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Instituição
                            </span>
                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($resultado['instituicao']); ?></span>
                        </div>
                    </div>

                    <!-- Validation Stamp -->
                    <div class="mt-6 flex justify-center">
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-100 rounded-full border border-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <span class="text-emerald-700 text-sm font-medium">Validado em <?php echo date('d/m/Y \à\s H:i'); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Instructions -->
                <div class="bg-white rounded-3xl p-8 border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-amber-100 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Como validar seu certificado</h3>
                    </div>
                    <ol class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-7 h-7 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-sm font-semibold">1</span>
                            <span class="text-gray-600 pt-0.5">Localize o código de validação no seu certificado</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-7 h-7 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-sm font-semibold">2</span>
                            <span class="text-gray-600 pt-0.5">Digite o código no campo acima</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-7 h-7 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-sm font-semibold">3</span>
                            <span class="text-gray-600 pt-0.5">Clique em "Validar" para verificar a autenticidade</span>
                        </li>
                    </ol>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-8 px-4 border-t border-gray-200 bg-white/50">
            <div class="max-w-4xl mx-auto text-center space-y-3">
                <p class="text-gray-500">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
                <a href="admin/login.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-primary-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    Área Administrativa
                </a>
            </div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
