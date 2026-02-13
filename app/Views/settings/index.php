<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6 pt-2">
    <h1 class="text-2xl font-bold text-slate-800">Pengaturan</h1>
    <p class="text-slate-500 text-sm mt-1">Kelola profil bisnis dan proteksi modal Anda.</p>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- LEFT: Profile & Capital Guard -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i data-lucide="shield" class="w-5 h-5 text-primary"></i> Profil & Modal
        </h3>
        
        <form action="/settings/update_profile" method="post" class="space-y-4">
            <?= csrf_field() ?>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pemilik</label>
                <input type="text" name="name" value="<?= esc($user->name) ?>" class="block w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Bisnis</label>
                <input type="text" name="business_name" value="<?= esc($user->business_name) ?>" class="block w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>

            <!-- THE CORE FEATURE -->
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mt-2">
                <label class="block text-sm font-bold text-blue-800 mb-1 flex justify-between">
                    <span>Modal Terkunci (Safety Limit)</span>
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </label>
                <p class="text-xs text-blue-600 mb-2">
                    Sistem akan <b>menolak</b> transaksi "Prive" (Tarik Tunai) jika sisa uang Anda menyentuh batas ini.
                </p>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-slate-500 font-bold">Rp</span>
                    <input type="number" name="locked_capital_amount" value="<?= esc($user->locked_capital_amount) ?>" class="block w-full pl-10 px-3 py-2 border border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-bold text-slate-800">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-slate-700 transition font-medium">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- RIGHT: Wallet Management -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i data-lucide="wallet" class="w-5 h-5 text-primary"></i> Daftar Dompet / Akun
        </h3>

        <div class="space-y-3 mb-6">
            <?php foreach($wallets as $wallet): ?>
            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700"><?= esc($wallet->name) ?></p>
                        <p class="text-xs text-slate-400">ID: <?= $wallet->id ?></p>
                    </div>
                </div>
                <span class="text-sm font-bold text-slate-800">Rp <?= number_format($wallet->balance, 0, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <hr class="border-slate-100 mb-4">
        
        <h4 class="text-sm font-bold text-slate-700 mb-3">Tambah Dompet Baru</h4>
        <form action="/settings/add_wallet" method="post" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?= csrf_field() ?>
            <input type="text" name="wallet_name" placeholder="Nama (mis: OVO / Dana)" required class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
            <input type="number" name="initial_balance" placeholder="Saldo Awal (Rp)" required class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
            <button type="submit" class="sm:col-span-2 bg-white border border-slate-300 text-slate-700 py-2 rounded-lg text-sm hover:bg-slate-50 font-medium">
                + Tambah Dompet
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>