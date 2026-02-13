<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-8 pt-2">
    <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Pengaturan</h1>
    <p class="text-slate-500 mt-2 text-lg">Kelola profil bisnis, keuangan, dan keamanan data.</p>
</div>

<!-- Notifications -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3 shadow-sm">
        <i data-lucide="check-circle" class="w-6 h-6 flex-shrink-0"></i>
        <span class="font-medium"><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-3 shadow-sm">
        <i data-lucide="alert-circle" class="w-6 h-6 flex-shrink-0"></i>
        <span class="font-medium"><?= session()->getFlashdata('error') ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
    
    <!-- LEFT COLUMN: Profile & System -->
    <div class="space-y-8">
        
        <!-- 1. Profile & Modal Settings -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <i data-lucide="user-cog" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Profil & Modal</h3>
            </div>
            
            <form action="/settings/update_profile" method="post" class="space-y-5">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Pemilik</label>
                        <input type="text" name="name" value="<?= esc($user->name) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Bisnis</label>
                        <input type="text" name="business_name" value="<?= esc($user->business_name) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none" required>
                    </div>
                </div>

                <!-- Core Feature: Locked Capital -->
                <div class="p-5 bg-indigo-50 rounded-xl border border-indigo-100 relative">
                    <div class="flex justify-between items-start mb-2">
                        <label class="block text-sm font-bold text-indigo-900">
                            Modal Terkunci (Safety Limit)
                        </label>
                        <i data-lucide="lock" class="w-5 h-5 text-indigo-500"></i>
                    </div>
                    
                    <p class="text-xs text-indigo-600/80 mb-4 leading-relaxed">
                        Batas minimum saldo yang <b>tidak boleh</b> diambil untuk Prive (Keperluan Pribadi). Menjaga cashflow bisnis tetap aman.
                    </p>
                    
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-500 font-bold">Rp</span>
                        <input type="number" name="locked_capital_amount" value="<?= esc($user->locked_capital_amount) ?>" class="w-full pl-11 pr-4 py-3 bg-white border border-indigo-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-800 shadow-sm outline-none transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 text-white py-3 px-6 rounded-xl hover:bg-slate-800 transition-colors font-semibold flex justify-center items-center gap-2 shadow-lg shadow-slate-200">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Backup Database Card -->
        <div class="bg-amber-50 p-6 rounded-2xl border border-amber-200 relative overflow-hidden group hover:shadow-md transition-shadow">
            <!-- Decorative Elements -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-amber-100 rounded-full opacity-50 blur-3xl group-hover:bg-amber-200 transition-colors"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-amber-100 rounded-lg text-amber-700">
                        <i data-lucide="database-backup" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-amber-900">Backup Database</h3>
                </div>
                
                <p class="text-sm text-amber-800 mb-6 leading-relaxed max-w-lg">
                    Data tersimpan di komputer ini (Localhost). Wajib download backup secara berkala untuk mencegah kehilangan data jika komputer rusak.
                </p>
                
                <a href="<?= base_url('settings/backup') ?>" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white py-2.5 px-6 rounded-xl font-medium transition-colors shadow-sm">
                    <i data-lucide="download" class="w-4 h-4"></i> Download Backup (.sql)
                </a>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: Wallet Management -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Daftar Dompet</h3>
            </div>
            <span class="bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-full"><?= count($wallets) ?> Akun</span>
        </div>

        <!-- Wallet List -->
        <div class="space-y-4 mb-8">
            <?php if(empty($wallets)): ?>
                <div class="text-center py-12 px-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                    <div class="bg-white p-3 rounded-full shadow-sm w-fit mx-auto mb-3">
                        <i data-lucide="credit-card" class="w-6 h-6 text-slate-400"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Belum ada dompet terdaftar.</p>
                </div>
            <?php else: ?>
                <?php foreach($wallets as $wallet): ?>
                <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all duration-200">
                    <div class="flex items-center gap-4 mb-3 sm:mb-0">
                        <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-hover:text-emerald-600 group-hover:border-emerald-200 shadow-sm transition-colors">
                            <i data-lucide="credit-card" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-700 group-hover:text-emerald-900 transition-colors text-lg"><?= esc($wallet->name) ?></p>
                            <p class="text-xs text-slate-400 font-mono">ID: #<?= $wallet->id ?></p>
                        </div>
                    </div>
                    <div class="text-right pl-16 sm:pl-0">
                        <span class="block text-lg font-bold text-slate-800">Rp <?= number_format($wallet->balance, 0, ',', '.') ?></span>
                        <span class="text-xs text-slate-400 font-medium">Saldo Saat Ini</span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Add Wallet Form -->
        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200">
            <h4 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-emerald-500"></i> 
                Tambah Dompet Baru
            </h4>
            
            <form action="/settings/add_wallet" method="post" class="space-y-4">
                <?= csrf_field() ?>
                
                <div>
                    <input type="text" name="wallet_name" placeholder="Nama Akun (Contoh: Kasir Utama, BCA, OVO)" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all text-sm">
                </div>
                
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">Rp</span>
                    <input type="number" name="initial_balance" placeholder="Saldo Awal" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all text-sm">
                </div>
                
                <button type="submit" class="w-full bg-white border border-slate-300 text-slate-700 py-2.5 rounded-xl hover:bg-emerald-600 hover:text-white hover:border-emerald-600 font-semibold transition-all shadow-sm active:scale-[0.98]">
                    + Simpan Dompet
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>