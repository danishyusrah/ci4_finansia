<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pt-2 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Manajemen Stok</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau persediaan barang dagangan Anda.</p>
    </div>
    
    <div class="flex items-center gap-3 w-full md:w-auto">
        <!-- Card Aset Ringkas -->
        <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 flex flex-col w-full md:w-auto">
            <span class="text-[10px] text-blue-500 font-bold uppercase tracking-wider">Nilai Aset Stok</span>
            <span class="text-lg font-bold text-blue-700">Rp <?= number_format($total_asset, 0, ',', '.') ?></span>
        </div>

        <button onclick="openModal()" class="bg-primary text-white px-4 py-3 md:py-2 rounded-lg hover:bg-slate-700 shadow-lg shadow-slate-300/50 flex items-center justify-center gap-2 w-full md:w-auto">
            <i data-lucide="package-plus" class="w-4 h-4"></i> <span class="md:hidden">Tambah</span><span class="hidden md:inline">Tambah Barang</span>
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200 text-sm">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4 font-semibold">Nama Barang</th>
                    <th class="px-6 py-4 font-semibold">Harga Beli (Modal)</th>
                    <th class="px-6 py-4 font-semibold">Harga Jual</th>
                    <th class="px-6 py-4 font-semibold">Stok</th>
                    <th class="px-6 py-4 font-semibold text-center">Margin</th>
                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-50">
                <?php if(empty($products)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center">
                                <i data-lucide="package-open" class="w-10 h-10 mb-2 text-slate-300"></i>
                                Belum ada data barang.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($products as $p): 
                        $margin = $p['sell_price'] - $p['buy_price'];
                        $marginPercent = ($p['buy_price'] > 0) ? ($margin / $p['buy_price']) * 100 : 0;
                        
                        // Stok Alert
                        $stockClass = 'bg-emerald-100 text-emerald-700';
                        if($p['stock'] <= 5) $stockClass = 'bg-red-100 text-red-700 animate-pulse';
                        elseif($p['stock'] <= 10) $stockClass = 'bg-orange-100 text-orange-700';
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= esc($p['name']) ?></div>
                            <div class="text-xs text-slate-400"><?= esc($p['code'] ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            Rp <?= number_format($p['buy_price'], 0, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">
                            Rp <?= number_format($p['sell_price'], 0, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold <?= $stockClass ?>">
                                <?= $p['stock'] ?> Unit
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                +<?= number_format($marginPercent, 0) ?>%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            <button onclick='editProduct(<?= json_encode($p) ?>)' class="text-slate-400 hover:text-blue-600">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <form action="/products/delete/<?= $p['id'] ?>" method="post" onsubmit="return confirm('Hapus barang ini?');">
                                <button type="submit" class="text-slate-400 hover:text-red-500">
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

<!-- MODAL PRODUCT -->
<div id="modalProduct" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900" id="modalTitle">Tambah Barang Baru</h3>
                <button onclick="closeModal()"><i data-lucide="x" class="text-slate-400"></i></button>
            </div>
            
            <form action="/products/save" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="productId">
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                        <input type="text" name="name" id="productName" required class="w-full border-slate-300 rounded-lg p-2" placeholder="Contoh: Kopi Bubuk 1kg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode/SKU</label>
                        <input type="text" name="code" id="productCode" class="w-full border-slate-300 rounded-lg p-2" placeholder="KOP-001">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Harga Beli (HPP)</label>
                        <input type="number" name="buy_price" id="productBuy" required class="w-full border-slate-300 rounded-lg p-2" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Harga Jual</label>
                        <input type="number" name="sell_price" id="productSell" required class="w-full border-slate-300 rounded-lg p-2 font-bold" placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stok Awal</label>
                    <input type="number" name="stock" id="productStock" required class="w-full border-slate-300 rounded-lg p-2 font-bold text-lg border-2 border-slate-200 focus:border-primary" placeholder="0">
                    <p class="text-xs text-slate-400 mt-1">Stok akan berkurang otomatis jika Anda mencatat penjualan (Fitur Mendatang).</p>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="flex-1 py-2 text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200">Batal</button>
                    <button type="submit" class="flex-1 bg-primary text-white font-bold py-2 rounded-lg hover:bg-slate-800">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalProduct').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Tambah Barang Baru';
        document.getElementById('productId').value = '';
        // Reset Form
        document.getElementById('productName').value = '';
        document.getElementById('productCode').value = '';
        document.getElementById('productBuy').value = '';
        document.getElementById('productSell').value = '';
        document.getElementById('productStock').value = '';
    }

    function editProduct(data) {
        openModal();
        document.getElementById('modalTitle').innerText = 'Edit Barang';
        document.getElementById('productId').value = data.id;
        document.getElementById('productName').value = data.name;
        document.getElementById('productCode').value = data.code;
        document.getElementById('productBuy').value = data.buy_price;
        document.getElementById('productSell').value = data.sell_price;
        document.getElementById('productStock').value = data.stock;
    }

    function closeModal() {
        document.getElementById('modalProduct').classList.add('hidden');
    }
</script>

<?= $this->endSection() ?>