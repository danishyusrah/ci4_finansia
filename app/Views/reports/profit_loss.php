<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Header & Filter -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 pt-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Laporan Laba Rugi</h1>
        <p class="text-slate-500 text-sm mt-1">Periode: <?= date('F Y', mktime(0, 0, 0, $month, 10)) ?></p>
    </div>
    
    <!-- Filter Form -->
    <form action="" method="get" class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
        <select name="month" class="bg-slate-50 border-none text-sm rounded focus:ring-0">
            <?php for($m=1; $m<=12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                </option>
            <?php endfor; ?>
        </select>
        <select name="year" class="bg-slate-50 border-none text-sm rounded focus:ring-0">
            <?php for($y=date('Y'); $y>=date('Y')-2; $y--): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="bg-primary text-white px-3 py-1.5 rounded text-sm hover:bg-slate-700">
            Tampilkan
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <!-- Pemasukan -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
        <h3 class="text-2xl font-bold text-emerald-600 mt-1">+ Rp <?= number_format($income, 0, ',', '.') ?></h3>
    </div>

    <!-- Pengeluaran -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Beban Bisnis</p>
        <h3 class="text-2xl font-bold text-red-600 mt-1">- Rp <?= number_format($expense, 0, ',', '.') ?></h3>
        <p class="text-xs text-slate-400 mt-1">*Tidak termasuk Prive</p>
    </div>

    <!-- Laba Bersih -->
    <div class="bg-<?= $net_profit >= 0 ? 'emerald' : 'red' ?>-50 p-6 rounded-2xl shadow-sm border border-<?= $net_profit >= 0 ? 'emerald' : 'red' ?>-100">
        <p class="text-xs font-semibold text-<?= $net_profit >= 0 ? 'emerald' : 'red' ?>-600 uppercase tracking-wider">
            <?= $net_profit >= 0 ? 'Keuntungan Bersih' : 'Kerugian Bersih' ?>
        </p>
        <h3 class="text-2xl font-bold text-<?= $net_profit >= 0 ? 'emerald' : 'red' ?>-700 mt-1">
            Rp <?= number_format($net_profit, 0, ',', '.') ?>
        </h3>
        <?php if($net_profit > 0): ?>
            <p class="text-xs text-emerald-600 mt-1">🎉 Bagus! Bisnis untung.</p>
        <?php else: ?>
            <p class="text-xs text-red-600 mt-1">⚠️ Hati-hati! Evaluasi pengeluaran.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Tabel Pengeluaran Terbesar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="font-bold text-slate-800 mb-4">Rincian Pengeluaran</h3>
        <?php if(empty($breakdown)): ?>
            <p class="text-slate-400 text-sm">Belum ada data pengeluaran.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach($breakdown as $row): 
                    $percent = ($expense > 0) ? ($row['total'] / $expense) * 100 : 0;
                ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-700 font-medium"><?= esc($row['category_name']) ?></span>
                        <span class="text-slate-800 font-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-red-400 h-2 rounded-full" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Tips / Insight -->
    <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
        <h3 class="font-bold text-blue-800 mb-2 flex items-center gap-2">
            <i data-lucide="lightbulb" class="w-5 h-5"></i> Insight Keuangan
        </h3>
        <p class="text-blue-700 text-sm leading-relaxed mb-4">
            Untuk menjaga margin keuntungan tetap sehat, usahakan total biaya operasional tidak melebihi <strong>40%</strong> dari total pendapatan.
        </p>
        
        <?php if($income > 0): 
            $margin = ($net_profit / $income) * 100;
        ?>
            <div class="bg-white p-4 rounded-xl border border-blue-100">
                <span class="text-xs text-slate-500 uppercase">Margin Keuntungan Saat Ini</span>
                <div class="text-2xl font-bold text-blue-600"><?= number_format($margin, 1) ?>%</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>