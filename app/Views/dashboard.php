<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Header & Button (Desktop) -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
        <p class="text-slate-500 text-sm">Ringkasan keuangan Anda hari ini.</p>
    </div>
    <button onclick="toggleModal('modalTransaction')" class="hidden sm:inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors shadow-sm">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
        Transaksi Baru
    </button>
</div>

<!-- Alerts (Notifikasi) -->
<?php if(session()->getFlashdata('success')): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg relative" role="alert">
        <div class="flex">
            <div class="flex-shrink-0">
                <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700"><?= session()->getFlashdata('success') ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg relative" role="alert">
        <div class="flex">
            <div class="flex-shrink-0">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700"><?= session()->getFlashdata('error') ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Stats Cards (Grid Layout) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Kas -->
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500 relative overflow-hidden">
        <div class="flex justify-between items-start z-10 relative">
            <div>
                <p class="text-xs font-semibold text-blue-500 uppercase tracking-wider mb-1">Total Kas</p>
                <h3 class="text-xl font-bold text-slate-800">Rp <?= number_format($total_kas, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg text-blue-500">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Laba Bersih -->
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500 relative overflow-hidden">
        <div class="flex justify-between items-start z-10 relative">
            <div>
                <p class="text-xs font-semibold text-green-500 uppercase tracking-wider mb-1">Laba Bersih</p>
                <h3 class="text-xl font-bold text-slate-800">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-green-50 rounded-lg text-green-500">
                <i data-lucide="coins" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Modal Terkunci -->
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500 relative overflow-hidden">
        <div class="flex justify-between items-start z-10 relative">
            <div>
                <p class="text-xs font-semibold text-yellow-500 uppercase tracking-wider mb-1">Modal Terkunci</p>
                <h3 class="text-xl font-bold text-slate-800">Rp <?= number_format($modal_terkunci, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-yellow-50 rounded-lg text-yellow-500">
                <i data-lucide="lock" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Pengeluaran Bulan Ini -->
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500 relative overflow-hidden">
        <div class="flex justify-between items-start z-10 relative">
            <div>
                <p class="text-xs font-semibold text-red-500 uppercase tracking-wider mb-1">Pengeluaran (Bulan Ini)</p>
                <h3 class="text-xl font-bold text-slate-800" id="val-expense">Rp <?= number_format($expense_month, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-red-50 rounded-lg text-red-500">
                <i data-lucide="trending-down" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Transactions Wrapper -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-slate-800">Arus Kas Bulan Ini</h3>
            <span class="inline-flex items-center px-2 py-1 bg-green-50 text-green-600 text-xs font-medium rounded-full">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                Realtime
            </span>
        </div>
        <div class="relative h-80 w-full">
            <canvas id="myAreaChart"></canvas>
        </div>
    </div>

    <!-- Recent Transactions List -->
    <div class="bg-white rounded-xl shadow-sm p-0 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Transaksi Terakhir</h3>
            <!-- Tombol Trigger Modal Export -->
            <button onclick="toggleModal('modalExport')" class="flex items-center text-xs font-medium text-slate-600 hover:text-primary bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-full transition-colors">
                <i data-lucide="download" class="w-3 h-3 mr-1.5"></i> Export Laporan
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto max-h-[400px]">
            <?php if(empty($recent_trx)): ?>
                <div class="flex flex-col items-center justify-center h-48 text-slate-400">
                    <i data-lucide="inbox" class="w-10 h-10 mb-2 opacity-50"></i>
                    <p class="text-sm">Belum ada transaksi</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-slate-50">
                    <?php foreach($recent_trx as $t): ?>
                        <?php 
                            $catName = '-';
                            foreach($categories as $c) {
                                $c = (array) $c;
                                if($c['id'] == $t['category_id']) { $catName = $c['name']; break; }
                            }
                            $isIncome = $t['type'] == 'income';
                        ?>
                        <li class="p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $isIncome ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                                        <i data-lucide="<?= $isIncome ? 'arrow-down' : 'arrow-up' ?>" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 line-clamp-1"><?= esc($t['description']) ?></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-slate-500"><?= date('d M, H:i', strtotime($t['created_at'])) ?></span>
                                            <span class="text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded border border-slate-200"><?= $catName ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold <?= $isIncome ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= $isIncome ? '+' : '-' ?> <?= number_format($t['amount'], 0, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
            <a href="<?= base_url('transactions') ?>" class="text-sm text-primary font-medium hover:underline">Lihat Semua</a>
        </div>
    </div>
</div>

<!-- Modal Transaksi -->
<div id="modalTransaction" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="toggleModal('modalTransaction')"></div>
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-primary px-4 py-3 sm:px-6 flex justify-between items-center">
                <h3 class="text-base font-semibold leading-6 text-white">Catat Transaksi</h3>
                <button onclick="toggleModal('modalTransaction')" class="text-white/70 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="<?= base_url('dashboard/save_transaction') ?>" method="post" enctype="multipart/form-data">
                <div class="px-4 py-5 sm:p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 uppercase">Tipe Transaksi</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <input type="radio" name="type" id="type_expense" value="expense" class="peer hidden" checked>
                                <label for="type_expense" class="flex items-center justify-center w-full px-4 py-2 border border-slate-200 rounded-lg cursor-pointer text-slate-600 hover:bg-slate-50 peer-checked:bg-red-50 peer-checked:text-red-600 peer-checked:border-red-500 transition-all">
                                    <i data-lucide="arrow-up" class="w-4 h-4 mr-2"></i> Pengeluaran
                                </label>
                            </div>
                            <div>
                                <input type="radio" name="type" id="type_income" value="income" class="peer hidden">
                                <label for="type_income" class="flex items-center justify-center w-full px-4 py-2 border border-slate-200 rounded-lg cursor-pointer text-slate-600 hover:bg-slate-50 peer-checked:bg-green-50 peer-checked:text-green-600 peer-checked:border-green-500 transition-all">
                                    <i data-lucide="arrow-down" class="w-4 h-4 mr-2"></i> Pemasukan
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 uppercase">Nominal</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-slate-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="amount" class="block w-full rounded-lg border-0 py-2.5 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 font-semibold bg-slate-50" placeholder="0" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1 uppercase">Dompet</label>
                            <select name="wallet_id" class="block w-full rounded-lg border-0 py-2.5 text-slate-900 ring-1 ring-inset ring-slate-300 bg-white" required>
                                <?php foreach($wallets as $w): $w = (array)$w; ?>
                                    <option value="<?= $w['id'] ?>"><?= $w['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1 uppercase">Kategori</label>
                            <select name="category_id" class="block w-full rounded-lg border-0 py-2.5 text-slate-900 ring-1 ring-inset ring-slate-300 bg-white" required>
                                <?php foreach($categories as $c): $c = (array)$c; ?>
                                    <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 uppercase">Keterangan</label>
                        <textarea name="description" rows="2" class="block w-full rounded-lg border-0 py-2 text-slate-900 ring-1 ring-inset ring-slate-300 bg-white" placeholder="Cth: Makan siang..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 uppercase">Struk (Opsional)</label>
                        <input type="file" name="attachment" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-slate-700 transition-colors"/>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 sm:ml-3 sm:w-auto transition-colors">Simpan</button>
                    <button type="button" onclick="toggleModal('modalTransaction')" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export Laporan (BARU) -->
<div id="modalExport" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="toggleModal('modalExport')"></div>
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm">
            <div class="bg-white px-4 py-5 sm:p-6">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 mb-4">
                        <i data-lucide="download" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Export Laporan</h3>
                    <p class="text-sm text-slate-500">Pilih periode laporan yang ingin diunduh.</p>
                </div>
                
                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 text-left">BULAN</label>
                        <select id="exportMonth" class="block w-full rounded-lg border-0 py-2 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                            <?php 
                            $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            foreach($months as $k => $v) {
                                $selected = ($k == date('n')) ? 'selected' : '';
                                echo "<option value='$k' $selected>$v</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 text-left">TAHUN</label>
                        <select id="exportYear" class="block w-full rounded-lg border-0 py-2 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                            <?php 
                            $currentYear = date('Y');
                            for($i = $currentYear; $i >= $currentYear - 4; $i--) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                <button type="button" onclick="downloadReport('pdf')" class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> PDF
                </button>
                <button type="button" onclick="downloadReport('excel')" class="inline-flex w-full justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors mt-2 sm:mt-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i> Excel
                </button>
                <button type="button" onclick="toggleModal('modalExport')" class="mt-2 sm:mt-0 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inisialisasi Chart & Data Realtime
    document.addEventListener("DOMContentLoaded", function() {
        const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);

        var ctx = document.getElementById("myAreaChart").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Pemasukan',
                    data: [],
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderColor: '#10B981',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Pengeluaran',
                    data: [],
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderColor: '#EF4444',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 7 } },
                    y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { callback: function(value) { return 'Rp ' + (value/1000).toLocaleString('id-ID') + 'k'; } } }
                },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, displayColors: false, callbacks: { label: function(context) { return context.dataset.label + ': ' + formatRupiah(context.parsed.y); } } } }
            }
        });

        function updateChartData() {
            fetch('<?= base_url('dashboard/chart-data') ?>')
                .then(response => response.json())
                .then(data => {
                    myChart.data.labels = data.labels;
                    myChart.data.datasets[0].data = data.income;
                    myChart.data.datasets[1].data = data.expense;
                    myChart.update('none');
                    const elExpense = document.getElementById('val-expense');
                    if(elExpense) elExpense.innerText = formatRupiah(data.summary.expense);
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        updateChartData();
        setInterval(updateChartData, 5000);
    });

    // Fungsi Download Report
    function downloadReport(type) {
        const month = document.getElementById('exportMonth').value;
        const year = document.getElementById('exportYear').value;
        const baseUrl = '<?= base_url('dashboard') ?>';
        
        if (type === 'excel') {
            window.location.href = `${baseUrl}/export_excel?month=${month}&year=${year}`;
        } else {
            window.location.href = `${baseUrl}/export_pdf?month=${month}&year=${year}`;
        }
    }
</script>

<?= $this->endSection() ?>