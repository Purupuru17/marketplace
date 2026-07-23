
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

window.Swal = Swal;

document.addEventListener('alpine:init', () => {
    Alpine.store('layout', {
        sidebarOpen: false,
        collapsed: localStorage.getItem('sidebar_collapsed') === '1',

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed ? '1' : '0');
        },
    });

    Alpine.store('theme', {
        dark: localStorage.getItem('theme') === 'dark',
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        },
        init() {
            document.documentElement.classList.toggle('dark', this.dark);
        },
    });

    Alpine.store('toast', {
        items: [],

        push(message, type = 'info', duration = 4000) {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type, duration });

            if (duration > 0) {
                setTimeout(() => this.remove(id), duration);
            }
        },

        remove(id) {
            this.items = this.items.filter(item => item.id !== id);
        },

        success(message, duration = 4000) {
            this.push(message, 'success', duration);
        },

        error(message, duration = 6000) {
            this.push(message, 'error', duration);
        },

        warning(message, duration = 5000) {
            this.push(message, 'warning', duration);
        },

        info(message, duration = 4000) {
            this.push(message, 'info', duration);
        },
    });

    Alpine.magic('confirm', () => {
        return (options = {}) => {
            const colorMap = { danger: '#DC2626', warning: '#F59E0B', brand: '#3C50E0' };
            return Swal.fire({
                title: options.title || 'Konfirmasi',
                text: options.message || 'Apakah kamu yakin?',
                icon: options.variant === 'danger' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
                cancelButtonText: options.cancelText || 'Batal',
                confirmButtonColor: colorMap[options.variant] || '#3C50E0',
                reverseButtons: true,
            }).then(result => result.isConfirmed);
        };
    });
});


window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();
