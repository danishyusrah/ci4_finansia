<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6 pt-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Anggaran Bulanan</h1>
        <p class="text-slate-500 text-sm mt-1">Kontrol pengeluaran agar tidak boncos.</p>
    </div>
    <button onclick="toggleModal('modalBudget')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-slate-700 shadow-lg shadow-slate-300/50 flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-4 h-4"></i> Buat Anggaran
    </button>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if(empty($budgets)): ?>
        <div class="col-span-full text-center py-10 bg-white rounded-xl border border-dashed border-slate-300">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-50 rounded-full mb-4">
                <i data-lucide="target" class="w-8 h-8 text-slate-400"></i>
            </div>
            <p class="text-slate-500">Belum ada anggaran yang diatur.</p>
            <p class="text-xs text-slate-400 mt-1">Buat batas maksimal untuk kategori pengeluaran Anda.</p>
        </div>
    <?php else: ?>
        <?php foreach($budgets as $b): 
            // Tentukan Warna Bar
            $barColor = 'bg-emerald-500'; // Aman
            $textColor = 'text-emerald-600';
            
            if($b['percentage'] >= 75 && $b['percentage'] < 90) {
                $barColor = 'bg-yellow-500'; // Warning
                $textColor = 'text-yellow-600';
            } elseif ($b['percentage'] >= 90) {
                $barColor = 'bg-red-500'; // Bahaya
                $textColor = 'text-red-600';
            }
            
            // Cap percentage at 100 for width visual
            $width = ($b['percentage'] > 100) ? 100 : $b['percentage'];
        ?>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative group hover:shadow-md transition">
            <!-- Header -->
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800"><?= esc($b['category_name']) ?></h3>
                    <p class="text-xs text-slate-400">Limit: Rp <?= number_format($b['limit'], 0, ',', '.') ?></p>
                </div>
                
                <!-- Tombol Hapus (Muncul saat hover) -->
                <form action="/budgets/delete/<?= $b['id'] ?>" method="post" onsubmit="return confirm('Hapus anggaran ini?');" class="opacity-0 group-hover:opacity-100 transition">
                    <button type="submit" class="text-slate-300 hover:text-red-500">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <!-- Angka Realisasi -->
            <div class="flex justify-between items-end mb-2">
                <span class="text-2xl font-bold <?= $textColor ?>">
                    <?= number_format($b['percentage'], 1) ?>%
                </span>
                <span class="text-sm font-medium text-slate-600">
                    Terpakai: Rp <?= number_format($b['spent'], 0, ',', '.') ?>
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                <div class="<?= $barColor ?> h-3 rounded-full transition-all duration-500" style="width: <?= $width ?>%"></div>
            </div>

            <?php if($b['percentage'] > 100): ?>
                <p class="text-xs text-red-500 font-bold mt-2 flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3 h-3"></i> OVER BUDGET!
                </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- MODAL ADD BUDGET -->
<div id="modalBudget" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalBudget')"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Setel Batas Anggaran</h3>
            <form action="/budgets/save" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori Pengeluaran</label>
                    <select name="category_id" class="w-full border-slate-300 rounded-lg p-2 bg-white">
                        <?php foreach($categories as $c): ?>
                            <!-- PERBAIKAN: Menggunakan sintaks Object ($c->id) -->
                            <option value="<?= $c->id ?>"><?= esc($c->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Batas Maksimal (Rp)</label>
                    <input type="number" name="amount_limit" required class="w-full border-slate-300 rounded-lg p-2 font-bold" placeholder="Contoh: 1000000">
                    <p class="text-xs text-slate-400 mt-1">Berlaku untuk bulan ini.</p>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('modalBudget')" class="flex-1 py-2 text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200">Batal</button>
                    <button type="submit" class="flex-1 bg-primary text-white font-bold py-2 rounded-lg hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>