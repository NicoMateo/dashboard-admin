// ===== Sidebar Toggle =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
    }
}

// ===== Auto-hide alerts =====
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// ===== Responsive Sidebar =====
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('active');
            }
        });

        // Cerrar sidebar al hacer clic fuera en móvil
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
});

// ===== Confirm Delete =====
function confirmarEliminacion(mensaje) {
    return confirm(mensaje || '¿Está seguro de que desea eliminar este elemento?');
}

// ===== Form Validation =====
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valido = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger-color)';
            valido = false;
        } else {
            input.style.borderColor = 'var(--success-color)';
        }
    });

    return valido;
}

// ===== Password Strength Indicator =====
function mostrarFortalezaPassword(inputId, indicatorId) {
    const input = document.getElementById(inputId);
    const indicator = document.getElementById(indicatorId);

    if (!input || !indicator) return;

    input.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;

        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;

        const strengthTexts = ['Muy débil', 'Débil', 'Media', 'Fuerte', 'Muy fuerte'];
        const strengthColors = ['#e74a3b', '#f6c23e', '#36b9cc', '#1cc88a', '#1cc88a'];

        indicator.textContent = strengthTexts[strength - 1] || '';
        indicator.style.color = strengthColors[strength - 1] || '';
    });
}

// ===== Table Search =====
function buscarEnTabla(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (!input || !table) return;

    input.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            let txtValue = rows[i].textContent || rows[i].innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });
}

// ===== Tooltip =====
function inicializarTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');

    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.position = 'absolute';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
        });

        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

// ===== Loading Spinner =====
function mostrarCargando(mostrar = true) {
    let spinner = document.getElementById('loading-spinner');

    if (mostrar) {
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.id = 'loading-spinner';
            spinner.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                ">
                    <div style="
                        background: white;
                        padding: 30px;
                        border-radius: 10px;
                        text-align: center;
                    ">
                        <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: #4e73df;"></i>
                        <p style="margin-top: 15px; color: #5a5c69;">Cargando...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(spinner);
        }
    } else {
        if (spinner) {
            spinner.remove();
        }
    }
}

// ===== Copy to Clipboard =====
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        mostrarNotificacion('Copiado al portapapeles', 'success');
    }).catch(err => {
        console.error('Error al copiar:', err);
        mostrarNotificacion('Error al copiar', 'error');
    });
}

// ===== Toast Notifications =====
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    `;

    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    const colorMap = {
        success: '#1cc88a',
        error: '#e74a3b',
        warning: '#f6c23e',
        info: '#36b9cc'
    };

    toast.innerHTML = `
        <i class="fas ${iconMap[tipo]}" style="color: ${colorMap[tipo]}; font-size: 20px;"></i>
        <span style="color: #5a5c69;">${mensaje}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== Format Date =====
function formatearFecha(fecha, formato = 'dd/mm/yyyy') {
    const d = new Date(fecha);
    const dia = String(d.getDate()).padStart(2, '0');
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const anio = d.getFullYear();
    const hora = String(d.getHours()).padStart(2, '0');
    const minuto = String(d.getMinutes()).padStart(2, '0');

    switch (formato) {
        case 'dd/mm/yyyy':
            return `${dia}/${mes}/${anio}`;
        case 'yyyy-mm-dd':
            return `${anio}-${mes}-${dia}`;
        case 'dd/mm/yyyy hh:mm':
            return `${dia}/${mes}/${anio} ${hora}:${minuto}`;
        default:
            return d.toLocaleDateString();
    }
}

// ===== Animations =====
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ===== Initialize on load =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Admin - Sistema cargado correctamente');
    inicializarTooltips();
});
