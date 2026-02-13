<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Header & Filter Section -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 pt-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Mutasi</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau arus uang masuk dan keluar.</p>
    </div>
    
    <!-- Filter Form -->
    <form action="" method="get" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
        <div class="relative">
            <input type="date" name="start_date" value="<?= $start_date ?>" class="pl-2 pr-2 py-1 text-sm border-none bg-slate-50 rounded focus:ring-0 text-slate-600">
        </div>
        <span class="text-slate-400">-</span>
        <div class="relative">
            <input type="date" name="end_date" value="<?= $end_date ?>" class="pl-2 pr-2 py-1 text-sm border-none bg-slate-50 rounded focus:ring-0 text-slate-600">
        </div>
        <button type="submit" class="bg-primary text-white px-3 py-1.5 rounded text-sm hover:bg-slate-700 transition">
            <i data-lucide="filter" class="w-4 h-4 inline-block mr-1"></i> Filter
        </button>
    </form>
</div>

<!-- Transaction Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-green-50 text-green-700 px-4 py-2 text-sm border-b border-green-100">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4 font-semibold">Tanggal</th>
                    <th class="px-6 py-4 font-semibold">Keterangan</th>
                    <th class="px-6 py-4 font-semibold">Kategori</th>
                    <th class="px-6 py-4 font-semibold">Bukti</th> <!-- Kolom Baru -->
                    <th class="px-6 py-4 font-semibold">Dompet</th>
                    <th class="px-6 py-4 font-semibold text-right">Nominal</th>
                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-50">
                <?php if(empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($transactions as $trx): ?>
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                            <?= date('d M Y', strtotime($trx['transaction_date'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800"><?= esc($trx['description']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <?= esc($trx['category_name']) ?>
                            </span>
                        </td>
                        <!-- Kolom Bukti -->
                        <td class="px-6 py-4">
                            <?php if(!empty($trx['attachment'])): ?>
                                <a href="/uploads/struk/<?= $trx['attachment'] ?>" target="_blank" class="text-primary hover:text-blue-700 flex items-center gap-1 text-xs font-bold bg-blue-50 px-2 py-1 rounded border border-blue-100 w-fit">
                                    <i data-lucide="image" class="w-3 h-3"></i> Lihat
                                </a>
                            <?php else: ?>
                                <span class="text-slate-300 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            <?= esc($trx['wallet_name']) ?>
                        </td>
                        <td class="px-6 py-4 text-right font-bold whitespace-nowrap <?= $trx['type'] == 'income' ? 'text-emerald-600' : 'text-slate-800' ?>">
                            <?= $trx['type'] == 'income' ? '+' : '-' ?> Rp <?= number_format($trx['amount'], 0, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <form action="/transactions/delete/<?= $trx['id'] ?>" method="post" onsubmit="return confirm('Hapus transaksi ini? Saldo akan dikembalikan.');">
                                <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>