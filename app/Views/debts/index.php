<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6 pt-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Catatan Kasbon</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola hutang piutang agar tidak lupa tagih.</p>
    </div>
    <button onclick="toggleModal('modalDebt')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-slate-700 shadow-lg shadow-slate-300/50 flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-4 h-4"></i> Catat Baru
    </button>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- KOLOM KIRI: PIUTANG (Orang Hutang ke Kita) -->
    <div class="space-y-4">
        <h3 class="font-bold text-emerald-600 flex items-center gap-2 uppercase text-sm tracking-wider">
            <i data-lucide="arrow-up-right" class="w-4 h-4"></i> Piutang (Uang Masuk Nanti)
        </h3>
        
        <?php if(empty($piutang)): ?>
            <div class="p-6 bg-white rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-sm">
                Tidak ada data piutang.
            </div>
        <?php else: ?>
            <?php foreach($piutang as $row): ?>
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden group">
                <?php if($row['status']=='paid'): ?>
                    <div class="absolute right-0 top-0 bg-emerald-100 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">LUNAS</div>
                <?php else: ?>
                    <div class="absolute right-0 top-0 bg-orange-100 text-orange-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">BELUM LUNAS</div>
                <?php endif; ?>

                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-bold text-slate-800"><?= esc($row['party_name']) ?></h4>
                        <p class="text-xs text-slate-500"><?= esc($row['description']) ?></p>
                    </div>
                    <span class="font-bold text-lg text-emerald-600">Rp <?= number_format($row['amount'],0,',','.') ?></span>
                </div>
                
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-50">
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i> Jatuh Tempo: <?= date('d M Y', strtotime($row['due_date'])) ?>
                    </span>
                    
                    <?php if($row['status']=='unpaid'): ?>
                    <button onclick="openPayModal('<?= $row['id'] ?>', '<?= esc($row['party_name']) ?>', 'income')" class="text-xs bg-emerald-600 text-white px-3 py-1.5 rounded hover:bg-emerald-700 transition">
                        Terima Pembayaran
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- KOLOM KANAN: HUTANG (Kita Hutang ke Orang) -->
    <div class="space-y-4">
        <h3 class="font-bold text-red-600 flex items-center gap-2 uppercase text-sm tracking-wider">
            <i data-lucide="arrow-down-left" class="w-4 h-4"></i> Hutang (Harus Dibayar)
        </h3>

        <?php if(empty($hutang)): ?>
            <div class="p-6 bg-white rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-sm">
                Tidak ada data hutang.
            </div>
        <?php else: ?>
            <?php foreach($hutang as $row): ?>
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden">
                <?php if($row['status']=='paid'): ?>
                    <div class="absolute right-0 top-0 bg-emerald-100 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">LUNAS</div>
                <?php else: ?>
                    <div class="absolute right-0 top-0 bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-bl-lg">BELUM LUNAS</div>
                <?php endif; ?>

                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-bold text-slate-800"><?= esc($row['party_name']) ?></h4>
                        <p class="text-xs text-slate-500"><?= esc($row['description']) ?></p>
                    </div>
                    <span class="font-bold text-lg text-red-600">Rp <?= number_format($row['amount'],0,',','.') ?></span>
                </div>
                
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-50">
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i> Jatuh Tempo: <?= date('d M Y', strtotime($row['due_date'])) ?>
                    </span>
                    
                    <?php if($row['status']=='unpaid'): ?>
                    <button onclick="openPayModal('<?= $row['id'] ?>', '<?= esc($row['party_name']) ?>', 'expense')" class="text-xs bg-red-600 text-white px-3 py-1.5 rounded hover:bg-red-700 transition">
                        Lunasi Sekarang
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL ADD DEBT -->
<div id="modalDebt" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalDebt')"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Catat Kasbon Baru</h3>
            <form action="/debts/save" method="post" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jenis</label>
                    <select name="type" class="w-full border-slate-300 rounded-lg p-2 bg-white">
                        <option value="receivable">Piutang (Orang Hutang ke Saya)</option>
                        <option value="payable">Hutang (Saya Hutang ke Orang)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Orang/Toko</label>
                    <input type="text" name="party_name" required class="w-full border-slate-300 rounded-lg p-2" placeholder="Cth: Pak Budi / Toko Material">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                    <input type="number" name="amount" required class="w-full border-slate-300 rounded-lg p-2 font-bold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
                    <input type="text" name="description" class="w-full border-slate-300 rounded-lg p-2" placeholder="Cth: Bon Rokok / Ambil Semen">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jatuh Tempo</label>
                    <input type="date" name="due_date" required class="w-full border-slate-300 rounded-lg p-2">
                </div>
                <button type="submit" class="w-full bg-primary text-white font-bold py-2 rounded-lg mt-4">Simpan</button>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PAY (KONFIRMASI PELUNASAN) -->
<div id="modalPay" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closePayModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3 text-emerald-600">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Pelunasan</h3>
            <p class="text-sm text-slate-500 mb-4">Dana akan otomatis ditambahkan/dikurangi dari dompet yang dipilih.</p>
            
            <form id="formPay" method="post" action="">
                <?= csrf_field() ?>
                <div class="text-left mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Pilih Dompet / Akun</label>
                    <select name="wallet_id" class="w-full border-slate-300 rounded-lg p-2 bg-slate-50 text-sm">
                        <?php foreach($wallets as $w): ?>
                            <option value="<?= $w->id ?>"><?= $w->name ?> (Rp <?= number_format($w->balance,0,',','.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2 rounded-lg">Ya, Sudah Lunas</button>
                <button type="button" onclick="closePayModal()" class="w-full text-slate-400 text-sm mt-3">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openPayModal(id, name, type) {
        document.getElementById('modalPay').classList.remove('hidden');
        document.getElementById('formPay').action = '/debts/mark_paid/' + id;
    }
    function closePayModal() {
        document.getElementById('modalPay').classList.add('hidden');
    }
</script>

<?= $this->endSection() ?>