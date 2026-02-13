<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Finansia' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0F172A', 
                        accent: '#3B82F6', 
                        success: '#10B981', 
                        danger: '#EF4444', 
                        warning: '#F59E0B', 
                    }
                }
            }
        }
    </script>
</head>
<body class="text-slate-800 antialiased">

    <!-- SIDEBAR (Desktop) -->
    <aside class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-white border-r border-slate-200 hidden sm:block">
        <!-- Container Flex agar footer sidebar (Pengaturan) selalu di bawah -->
        <div class="h-full flex flex-col px-3 py-4">
            
            <!-- Bagian Atas: Logo & Menu Utama -->
            <div class="flex-1 overflow-y-auto">
                <a href="/dashboard" class="flex items-center pl-2.5 mb-8">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center mr-3">
                        <i data-lucide="bar-chart-2" class="text-white w-5 h-5"></i>
                    </div>
                    <span class="self-center text-xl font-bold whitespace-nowrap text-slate-800">Finansia</span>
                </a>
                
                <ul class="space-y-2 font-medium">
                    <?php $uri = service('uri'); ?>
                    
                    <!-- Menu Dashboard -->
                    <li>
                        <a href="/dashboard" class="flex items-center p-2 rounded-lg group <?= $uri->getSegment(1) == 'dashboard' || $uri->getSegment(1) == '' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 <?= $uri->getSegment(1) == 'dashboard' || $uri->getSegment(1) == '' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>

                    <!-- Menu BARU: Kasir / POS -->
                    <li>
                        <a href="/pos" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'pos' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="monitor" class="w-5 h-5 <?= $uri->getSegment(1) == 'pos' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Kasir / POS</span>
                        </a>
                    </li>
                    
                    <!-- Menu Transaksi -->
                    <li>
                        <a href="/transactions" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'transactions' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="arrow-left-right" class="w-5 h-5 <?= $uri->getSegment(1) == 'transactions' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Transaksi</span>
                        </a>
                    </li>

                    <!-- Menu Hutang & Piutang (Kasbon) -->
                    <li>
                        <a href="/debts" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'debts' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="book-open" class="w-5 h-5 <?= $uri->getSegment(1) == 'debts' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Hutang & Piutang</span>
                        </a>
                    </li>

                    <!-- Menu Anggaran (Budgeting) -->
                    <li>
                        <a href="/budgets" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'budgets' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="target" class="w-5 h-5 <?= $uri->getSegment(1) == 'budgets' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Anggaran Belanja</span>
                        </a>
                    </li>

                    <!-- Menu Stok Barang (Inventory) -->
                    <li>
                        <a href="/products" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'products' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="package" class="w-5 h-5 <?= $uri->getSegment(1) == 'products' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Stok Barang</span>
                        </a>
                    </li>
                    
                    <!-- Menu Laporan -->
                    <li>
                        <a href="/reports" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'reports' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <i data-lucide="pie-chart" class="w-5 h-5 <?= $uri->getSegment(1) == 'reports' ? 'text-primary' : 'text-slate-400 group-hover:text-primary' ?>"></i>
                            <span class="ml-3">Laporan Laba Rugi</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Bagian Bawah: Pengaturan & Logout -->
            <div class="border-t border-slate-100 pt-4 mt-auto">
                <a href="/settings" class="flex items-center p-2 rounded-lg group transition-colors <?= $uri->getSegment(1) == 'settings' ? 'bg-slate-100 text-primary' : 'text-slate-500 hover:bg-slate-50' ?>">
                    <i data-lucide="settings" class="w-5 h-5 <?= $uri->getSegment(1) == 'settings' ? 'text-primary' : 'text-slate-400' ?>"></i>
                    <span class="ml-3">Pengaturan</span>
                </a>
                <a href="/logout" class="flex items-center p-2 rounded-lg group transition-colors text-red-500 hover:bg-red-50 mt-1">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span class="ml-3">Keluar</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="p-4 sm:ml-64 pb-24 sm:pb-4">
        <!-- Header Mobile -->
        <div class="flex justify-between items-center mb-6 sm:hidden">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                    <i data-lucide="bar-chart-2" class="text-white w-5 h-5"></i>
                </div>
                <h1 class="font-bold text-lg text-slate-800">Finansia</h1>
            </div>
            <button class="p-2 rounded-full bg-slate-100 relative">
                <i data-lucide="bell" class="w-5 h-5 text-slate-600"></i>
            </button>
        </div>

        <!-- Dynamic Content -->
        <?= $this->renderSection('content') ?>
        
    </div>

    <!-- BOTTOM NAV (Mobile Only) -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 sm:hidden z-50">
        <div class="grid grid-cols-5 gap-1 p-2">
            <!-- Home -->
            <a href="/dashboard" class="flex flex-col items-center justify-center p-2 <?= $uri->getSegment(1) == 'dashboard' || $uri->getSegment(1) == '' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?>">
                <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                <span class="text-[10px] mt-1 font-medium">Home</span>
            </a>
            
            <!-- Mutasi -->
            <a href="/transactions" class="flex flex-col items-center justify-center p-2 transition <?= $uri->getSegment(1) == 'transactions' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?>">
                <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                <span class="text-[10px] mt-1">Mutasi</span>
            </a>
            
            <!-- FAB (Add Transaction) -->
            <div class="relative -top-6">
                <!-- Cek apakah modalTransaction ada di halaman ini, jika tidak arahkan ke dashboard -->
                <button onclick="if(document.getElementById('modalTransaction')) { toggleModal('modalTransaction') } else { window.location.href='/dashboard' }" class="w-14 h-14 bg-primary text-white rounded-full flex items-center justify-center shadow-lg shadow-primary/40 border-4 border-slate-50">
                    <i data-lucide="plus" class="w-8 h-8"></i>
                </button>
            </div>
            
            <!-- Laporan -->
            <a href="/reports" class="flex flex-col items-center justify-center p-2 transition <?= $uri->getSegment(1) == 'reports' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?>">
                <i data-lucide="pie-chart" class="w-6 h-6"></i>
                <span class="text-[10px] mt-1">Laporan</span>
            </a>
            
            <!-- Akun/Settings -->
            <a href="/settings" class="flex flex-col items-center justify-center p-2 transition <?= $uri->getSegment(1) == 'settings' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?>">
                <i data-lucide="settings" class="w-6 h-6"></i>
                <span class="text-[10px] mt-1">Akun</span>
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();
        function toggleModal(modalID){
            const modal = document.getElementById(modalID);
            if(modal) {
                modal.classList.toggle("hidden");
            }
        }
    </script>
</body>
</html>