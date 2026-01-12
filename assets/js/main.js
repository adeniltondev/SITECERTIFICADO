/**
 * Validador de Certificados - JavaScript
 * Modern UI with Tailwind CSS
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus no campo de código
    const codigoInput = document.getElementById('codigo');
    if (codigoInput && !codigoInput.value) {
        codigoInput.focus();
    }

    // Formatar código enquanto digita
    if (codigoInput) {
        codigoInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }

    // Gerar código automático
    const gerarCodigoBtn = document.getElementById('gerarCodigo');
    if (gerarCodigoBtn) {
        gerarCodigoBtn.addEventListener('click', function() {
            const ano = new Date().getFullYear();
            const random = Math.floor(Math.random() * 9000) + 1000;
            const codigo = `CERT-${ano}-${random}`;
            const codigoField = document.getElementById('codigo');
            if (codigoField) {
                codigoField.value = codigo;
                codigoField.focus();
                
                // Visual feedback
                this.classList.add('scale-95');
                setTimeout(() => this.classList.remove('scale-95'), 150);
            }
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

    // Smooth reveal animation for result boxes
    const fadeElements = document.querySelectorAll('.fade-in-up');
    fadeElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
    });

    // Form validation visual feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('invalid', function(e) {
                this.classList.add('border-red-500');
            });
            input.addEventListener('input', function() {
                if (this.validity.valid) {
                    this.classList.remove('border-red-500');
                }
            });
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const activeForm = document.querySelector('form');
            if (activeForm) {
                activeForm.submit();
            }
        }
    });

    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('button[type="submit"], .btn-ripple');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add ripple animation
    if (!document.getElementById('ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});

// Utility function to copy text
function copiarCodigo(codigo) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(codigo).then(function() {
            showToast('Código copiado!', 'success');
        }).catch(function() {
            fallbackCopy(codigo);
        });
    } else {
        fallbackCopy(codigo);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showToast('Código copiado!', 'success');
    } catch (err) {
        showToast('Erro ao copiar', 'error');
    }
    document.body.removeChild(textarea);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-emerald-500' : type === 'error' ? 'bg-red-500' : 'bg-primary-500';
    
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-xl shadow-lg transform translate-y-full opacity-0 transition-all duration-300 z-50`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-full', 'opacity-0');
    });
    
    setTimeout(() => {
        toast.classList.add('translate-y-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
