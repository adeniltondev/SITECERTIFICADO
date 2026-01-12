/**
 * Validador de Certificados - JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus no campo de código
    const codigoInput = document.getElementById('codigo');
    if (codigoInput) {
        codigoInput.focus();
    }

    // Formatar código enquanto digita (opcional)
    if (codigoInput) {
        codigoInput.addEventListener('input', function(e) {
            // Converter para maiúsculas
            this.value = this.value.toUpperCase();
        });
    }

    // Confirmação de exclusão
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este certificado? Esta ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
    });

    // Gerar código automático
    const gerarCodigoBtn = document.getElementById('gerarCodigo');
    if (gerarCodigoBtn) {
        gerarCodigoBtn.addEventListener('click', function() {
            const ano = new Date().getFullYear();
            const random = Math.floor(Math.random() * 9000) + 1000;
            const codigo = `CERT-${ano}-${random}`;
            document.getElementById('codigo').value = codigo;
        });
    }

    // Máscara de CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Animação suave ao aparecer resultado
    const resultBox = document.querySelector('.result-box');
    if (resultBox) {
        resultBox.style.opacity = '0';
        resultBox.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            resultBox.style.transition = 'all 0.5s ease';
            resultBox.style.opacity = '1';
            resultBox.style.transform = 'translateY(0)';
        }, 100);
    }

    // Imprimir certificado validado
    const printBtn = document.getElementById('printResult');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
});

// Função para copiar código
function copiarCodigo(codigo) {
    navigator.clipboard.writeText(codigo).then(function() {
        alert('Código copiado para a área de transferência!');
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
    });
}
