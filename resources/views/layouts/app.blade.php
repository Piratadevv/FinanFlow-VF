<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <script>
        // Apply theme immediately to prevent flash of wrong theme
        (function () {
            try {
                const prefs = JSON.parse(localStorage.getItem('finanflow_prefs') || '{}');
                if (prefs.theme === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) { }
        })();
    </script>
</head>

<body class="bg-gray-100 dark:bg-slate-900 dark:text-slate-200 min-h-screen" x-data="appData()" x-init="initApp()"
    @confirm-delete.window="openConfirmDialog($event.detail.message, $event.detail.callback)"
    @show-toast.window="addToast($event.detail.message, $event.detail.type || 'success')">

    {{-- HEADER --}}
    <header
        class="bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700 fixed top-0 left-0 right-0 z-50 h-16">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center">
                        <img src="https://i0.wp.com/unimagec.ma/wp-content/uploads/2021/03/Logo-Unimagec-Web3.png?w=370&ssl=1"
                            alt="Unimagec Logo" class="h-10 object-contain">
                    </div>
                    <h1 class="text-lg font-semibold text-gray-800 dark:text-white hidden sm:block">Gestion des
                        Opérations Bancaires</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Configuration Button --}}
                <a href="{{ route('parametres.index') }}"
                    class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-600 dark:text-slate-300 hover:text-gray-800 dark:hover:text-white"
                    title="Paramètres">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </a>

                {{-- User --}}
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                        <span
                            class="text-blue-600 font-semibold text-xs">{{ strtoupper(substr(Auth::user()->username, 0, 2)) }}</span>
                    </div>
                    <span class="hidden sm:inline font-medium">{{ Auth::user()->username }}</span>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="p-2 rounded-lg hover:bg-red-50 text-gray-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition"
                        title="Déconnexion">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- SIDEBAR --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-16 left-0 bottom-0 w-64 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 z-40 transition-transform duration-300 overflow-y-auto">
        <nav class="p-4 space-y-1">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                      {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-800 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Tableau de Bord
            </a>
            <a href="{{ route('escomptes.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                      {{ request()->routeIs('escomptes.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-800 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Escomptes
            </a>
            <a href="{{ route('refinancements.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                      {{ request()->routeIs('refinancements.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-800 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Refinancements
            </a>
            <a href="{{ route('logs.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                      {{ request()->routeIs('logs.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-800 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Logs
            </a>
            <a href="{{ route('parametres.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                      {{ request()->routeIs('parametres.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:text-gray-800 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Paramètres
            </a>
        </nav>
    </aside>

    {{-- SIDEBAR OVERLAY (mobile) --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-30 lg:hidden" x-cloak>
    </div>

    {{-- MAIN CONTENT --}}
    <main class="pt-16 lg:pl-64 min-h-screen">
        <div class="p-4 sm:p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    {{-- TOAST NOTIFICATIONS --}}
    <div class="fixed bottom-4 right-4 z-[100] space-y-2" x-cloak>
        <template x-for="(toast, index) in toasts" :key="index">
            <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="transform translate-x-full opacity-0"
                x-transition:enter-end="transform translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border min-w-[320px]" :class="{
                     'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
                     'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                     'bg-yellow-50 border-yellow-200 text-yellow-800': toast.type === 'warning',
                     'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info'
                 }">
                <span class="text-sm font-medium" x-text="toast.message"></span>
                <button @click="removeToast(index)" class="ml-auto opacity-60 hover:opacity-100">&times;</button>
            </div>
        </template>
    </div>

    {{-- CONFIGURATION MODAL --}}
    <div x-show="showConfigModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showConfigModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Configuration</h2>
                <button @click="showConfigModal = false"
                    class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Autorisation Bancaire (DH)</label>
                    <input type="number" x-model="configForm.autorisation_bancaire" step="0.01" min="0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm">
                    <p class="font-medium text-blue-800 mb-2">Aperçu de l'impact :</p>
                    <div class="space-y-1 text-blue-700">
                        <p>Nouvel encours restant : <span class="font-semibold"
                                x-text="formatMontant(configForm.autorisation_bancaire - configImpact.cumulTotal)"></span>
                        </p>
                        <p>Nouvelle utilisation : <span class="font-semibold"
                                x-text="(configForm.autorisation_bancaire > 0 ? Math.round((configImpact.cumulTotal / configForm.autorisation_bancaire) * 100) : 0) + ' %'"></span>
                        </p>
                    </div>
                    <p x-show="configForm.autorisation_bancaire < configImpact.cumulTotal"
                        class="mt-2 text-red-600 font-medium">⚠️ L'autorisation est inférieure au cumul total !</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showConfigModal = false"
                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Annuler</button>
                <button @click="updateConfig()"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">Enregistrer</button>
            </div>
        </div>
    </div>

    {{-- CONFIRMATION MODAL --}}
    <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showConfirmModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Confirmer la suppression</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6" x-text="confirmMessage"></p>
            <div class="flex justify-end gap-3">
                <button @click="showConfirmModal = false"
                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Annuler</button>
                <button @click="confirmAction()"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition">Supprimer</button>
            </div>
        </div>
    </div>

    <script>
        function _getPrefs() {
            try {
                return JSON.parse(localStorage.getItem('finanflow_prefs') || '{}');
            } catch (e) { return {}; }
        }

        // Global format helpers — available everywhere
        window.ffFormatMontant = function (value) {
            if (value === null || value === undefined) return '0,00 DH';
            const prefs = _getPrefs();
            const locale = prefs.moneyFormat === 'intl' ? 'en-US' : 'fr-FR';
            return new Intl.NumberFormat(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' DH';
        };

        window.ffFormatDate = function (dateStr) {
            if (!dateStr) return '';
            const prefs = _getPrefs();
            const d = new Date(dateStr);
            const fmt = prefs.dateFormat || 'dd/MM/yyyy';
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            switch (fmt) {
                case 'yyyy-MM-dd': return `${year}-${month}-${day}`;
                case 'MM/dd/yyyy': return `${month}/${day}/${year}`;
                default: return `${day}/${month}/${year}`;
            }
        };

        function appData() {
            return {
                sidebarOpen: false,
                toasts: [],
                showConfigModal: false,
                showConfirmModal: false,
                confirmMessage: '',
                confirmCallback: null,
                configForm: { autorisation_bancaire: 200000 },
                configImpact: { cumulTotal: 0 },

                async initApp() {
                    // Apply theme
                    this._applyTheme();
                    // Listen for preference changes
                    window.addEventListener('prefs-updated', () => this._applyTheme());

                    try {
                        const res = await fetch('/api/configuration', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.configForm.autorisation_bancaire = data.autorisation_bancaire;
                        }
                        const kpiRes = await fetch('/api/dashboard/kpi', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                        });
                        if (kpiRes.ok) {
                            const kpi = await kpiRes.json();
                            this.configImpact.cumulTotal = kpi.cumulTotal || 0;
                        }
                    } catch (e) { }
                },

                _applyTheme() {
                    const prefs = _getPrefs();
                    if (prefs.theme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },

                addToast(message, type = 'success') {
                    const toast = { message, type, show: true };
                    this.toasts.push(toast);
                    setTimeout(() => { toast.show = false; }, 4000);
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.show); }, 4500);
                },

                removeToast(index) {
                    this.toasts.splice(index, 1);
                },

                formatMontant(value) {
                    return window.ffFormatMontant(value);
                },

                formatDate(dateStr) {
                    return window.ffFormatDate(dateStr);
                },

                relativeTime(dateStr) {
                    if (!dateStr) return '';
                    const now = new Date();
                    const date = new Date(dateStr);
                    const diffMs = now - date;
                    const diffMin = Math.floor(diffMs / 60000);
                    const diffH = Math.floor(diffMin / 60);
                    const diffD = Math.floor(diffH / 24);
                    if (diffMin < 1) return 'À l\'instant';
                    if (diffMin < 60) return `Il y a ${diffMin} minute${diffMin > 1 ? 's' : ''}`;
                    if (diffH < 24) return `Il y a ${diffH} heure${diffH > 1 ? 's' : ''}`;
                    if (diffD < 7) return `Il y a ${diffD} jour${diffD > 1 ? 's' : ''}`;
                    return this.formatDate(dateStr);
                },

                async updateConfig() {
                    try {
                        const res = await fetch('/api/configuration', {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ autorisation_bancaire: parseFloat(this.configForm.autorisation_bancaire) })
                        });
                        if (res.ok) {
                            this.showConfigModal = false;
                            this.addToast('Configuration mise à jour avec succès');
                            window.dispatchEvent(new CustomEvent('config-updated'));
                        } else {
                            const err = await res.json();
                            this.addToast(err.errors?.autorisation_bancaire?.[0] || 'Erreur de validation', 'error');
                        }
                    } catch (e) { this.addToast('Erreur de connexion', 'error'); }
                },

                openConfirmDialog(message, callback) {
                    this.confirmMessage = message;
                    this.confirmCallback = callback;
                    this.showConfirmModal = true;
                },

                confirmAction() {
                    if (this.confirmCallback) this.confirmCallback();
                    this.showConfirmModal = false;
                    this.confirmCallback = null;
                },

                csrfToken() {
                    return document.querySelector('meta[name=csrf-token]').content;
                }
            }
        }
    </script>
    @stack('scripts')
</body>

</html>