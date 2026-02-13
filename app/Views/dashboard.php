<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Flash Message -->
<?php if (session()->getFlashdata('error')) : ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<!-- Header Desktop -->
<div class="hidden sm:flex justify-between items-center mb-8 pt-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Ringkasan Bisnis</h1>
        <p class="text-slate-500 text-sm mt-1">Halo, <?= esc($user_name) ?>! Semangat jaga modal.</p>
    </div>
    <div class="flex items-center gap-3">
        <!-- TOMBOL EXPORT PDF BARU (Membuka Modal) -->
        <button onclick="toggleModal('modalExport')" class="flex items-center gap-2 px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-sm">
            <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
            <span>Export PDF</span>
        </button>

        <button onclick="toggleModal('modalTransaction')" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-slate-800 transition shadow-lg shadow-slate-300/50">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Catat Transaksi</span>
        </button>
    </div>
</div>

<!-- INFO CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <!-- Card 1: Saldo Kas -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Kas Real</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp <?= number_format($total_kas, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-slate-50 rounded-lg">
                <i data-lucide="briefcase" class="w-5 h-5 text-slate-600"></i>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm">
            <span class="text-slate-400">Total uang di semua dompet</span>
        </div>
    </div>

    <!-- Card 2: Modal Terkunci -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-2 -mt-2"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div>
                <p class="text-xs font-semibold text-blue-500 uppercase tracking-wider flex items-center gap-1">
                    <i data-lucide="lock" class="w-3 h-3"></i> Modal Terkunci
                </p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp <?= number_format($modal_terkunci, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg">
                <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
            </div>
        </div>
        <p class="text-xs text-slate-500 relative z-10">Dana ini <b>DILARANG</b> dipakai pribadi.</p>
        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-4">
            <div class="bg-blue-500 h-1.5 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    <!-- Card 3: Laba Bersih -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wider">Boleh Diambil (Prive)</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-emerald-50 rounded-lg">
                <i data-lucide="coins" class="w-5 h-5 text-emerald-600"></i>
            </div>
        </div>
        <?php if($laba_bersih < 0): ?>
            <p class="text-xs text-red-500 font-bold mt-2">⚠️ MODAL TERGERUS!</p>
        <?php else: ?>
            <button class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-md font-medium hover:bg-emerald-200 transition w-full mt-2">
                Ambil Gaji
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN CONTENT GRID -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- LEFT: CHART SECTION -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-slate-800">Tren Arus Kas (Bulan Ini)</h3>
        </div>
        <div class="relative h-72 w-full">
            <canvas id="cashflowChart"></canvas>
        </div>
    </div>

    <!-- RIGHT: RECENT TRANSACTIONS -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-slate-800">Transaksi Terakhir</h3>
        </div>
        
        <div class="space-y-4">
            <?php if(empty($recent_trx)): ?>
                <p class="text-center text-slate-400 py-4">Belum ada transaksi.</p>
            <?php else: ?>
                <?php foreach($recent_trx as $trx): ?>
                    <div class="flex items-center justify-between border-b border-slate-50 pb-3 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?= $trx['type'] == 'income' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' ?> flex items-center justify-center">
                                <i data-lucide="<?= $trx['type'] == 'income' ? 'arrow-down-left' : 'shopping-cart' ?>" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800"><?= esc($trx['description']) ?></p>
                                <p class="text-xs text-slate-400">
                                    <?= date('d M', strtotime($trx['transaction_date'])) ?> • <?= esc($trx['category_name']) ?>
                                    <?php if(!empty($trx['attachment'])): ?>
                                        <i data-lucide="paperclip" class="w-3 h-3 inline-block ml-1 text-slate-400"></i>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-bold <?= $trx['type'] == 'income' ? 'text-emerald-600' : 'text-slate-800' ?>">
                            <?= $trx['type'] == 'income' ? '+' : '-' ?>Rp <?= number_format($trx['amount'], 0, ',', '.') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL ADD TRANSACTION (EXISTING) -->
<div id="modalTransaction" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="toggleModal('modalTransaction')"></div>
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 border-b border-slate-100 flex justify-between">
                <h3 class="text-lg font-bold text-slate-900">Catat Transaksi Baru</h3>
                <button onclick="toggleModal('modalTransaction')"><i data-lucide="x" class="text-slate-400"></i></button>
            </div>

            <form action="/dashboard/save_transaction" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-4">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jenis</label>
                        <select name="type" class="block w-full py-2 px-3 border border-slate-300 rounded-lg bg-white">
                            <option value="income">Pemasukan (+)</option>
                            <option value="expense">Pengeluaran (-)</option>
                        </select>
                    </div>
                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" required class="block w-full py-2 px-3 border border-slate-300 rounded-lg font-bold" placeholder="0">
                    </div>
                </div>

                <!-- Wallet -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Dompet / Akun</label>
                    <select name="wallet_id" class="block w-full py-2 px-3 border border-slate-300 rounded-lg bg-white">
                        <?php foreach($wallets as $w): ?>
                            <option value="<?= $w->id ?>"><?= esc($w->name) ?> (Sisa: Rp <?= number_format($w->balance,0,',','.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                    <select name="category_id" class="block w-full py-2 px-3 border border-slate-300 rounded-lg bg-white">
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c->id ?>">
                                <?= esc($c->name) ?> 
                                <?= $c->is_prive ? '(PRIVE - Hati-hati)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Input File -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bukti Struk/Nota (Foto)</label>
                    <input type="file" name="attachment" accept="image/png, image/jpeg" class="block w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-slate-100 file:text-primary
                        hover:file:bg-slate-200
                    "/>
                    <p class="text-xs text-slate-400 mt-1">Maksimal 2MB (JPG/PNG)</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
                    <textarea name="description" rows="2" class="block w-full py-2 px-3 border border-slate-300 rounded-lg" placeholder="Contoh: Makan siang staff"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="toggleModal('modalTransaction')" class="px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-white bg-primary rounded-lg hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL FILTER EXPORT (BARU) -->
<div id="modalExport" class="fixed inset-0 z-[70] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="toggleModal('modalExport')"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-red-600"></i> Export Laporan PDF
            </h3>
            
            <form action="/dashboard/export_pdf" method="get" target="_blank">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" required value="<?= date('Y-m-01') ?>" class="w-full border-slate-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" required value="<?= date('Y-m-d') ?>" class="w-full border-slate-300 rounded-lg p-2 text-sm">
                    </div>
                </div>
                
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="toggleModal('modalExport')" class="flex-1 py-2 text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-lg shadow-red-200" onclick="toggleModal('modalExport')">
                        Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('cashflowChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [{
                    label: 'Arus Kas',
                    data: [<?= $total_kas * 0.8 ?>, <?= $total_kas * 0.9 ?>, <?= $total_kas * 0.85 ?>, <?= $total_kas ?>],
                    borderColor: '#10B981',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10B981',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: false, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>