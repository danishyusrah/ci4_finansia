<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- NOTIFIKASI TOAST (FLOATING) -->
<!-- Menggunakan Fixed Position agar tidak mengganggu layout POS -->
<?php if (session()->getFlashdata('success')) : ?>
    <div id="toast-success" class="fixed top-5 right-5 z-[999] flex items-center w-full max-w-xs p-4 text-slate-500 bg-white rounded-xl shadow-2xl border-l-4 border-emerald-500 transform transition-all duration-500 ease-out translate-y-0 opacity-100">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-9 h-9 text-emerald-500 bg-emerald-100 rounded-lg">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
        </div>
        <div class="ml-3 text-sm font-semibold text-slate-800 flex-1">
            <p class="text-xs text-slate-400 font-normal uppercase mb-0.5">Berhasil</p>
            <?= session()->getFlashdata('success') ?>
        </div>
        <button type="button" class="ml-2 -mx-1.5 -my-1.5 bg-white text-slate-400 hover:text-slate-900 rounded-lg focus:ring-2 focus:ring-slate-300 p-1.5 hover:bg-slate-50 inline-flex h-8 w-8 transition-colors" onclick="dismissToast('toast-success')">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div id="toast-error" class="fixed top-5 right-5 z-[999] flex items-center w-full max-w-xs p-4 text-slate-500 bg-white rounded-xl shadow-2xl border-l-4 border-red-500 transform transition-all duration-500 ease-out translate-y-0 opacity-100">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-9 h-9 text-red-500 bg-red-100 rounded-lg">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
        </div>
        <div class="ml-3 text-sm font-semibold text-slate-800 flex-1">
            <p class="text-xs text-slate-400 font-normal uppercase mb-0.5">Gagal</p>
            <?= session()->getFlashdata('error') ?>
        </div>
        <button type="button" class="ml-2 -mx-1.5 -my-1.5 bg-white text-slate-400 hover:text-slate-900 rounded-lg focus:ring-2 focus:ring-slate-300 p-1.5 hover:bg-slate-50 inline-flex h-8 w-8 transition-colors" onclick="dismissToast('toast-error')">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
<?php endif; ?>

<script>
    // Fungsi untuk menghilangkan toast dengan animasi
    function dismissToast(id) {
        const el = document.getElementById(id);
        if(el) {
            el.classList.add('opacity-0', '-translate-y-full'); // Animasi fade out ke atas
            setTimeout(() => el.remove(), 500); // Hapus elemen setelah animasi selesai
        }
    }

    // Auto dismiss setelah 4 detik
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => dismissToast('toast-success'), 4000);
        setTimeout(() => dismissToast('toast-error'), 4000);
    });
</script>

<div class="h-[calc(100vh-100px)] flex flex-col md:flex-row gap-4 overflow-hidden">
    
    <!-- LEFT: PRODUCT GRID -->
    <div class="flex-1 flex flex-col overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0 z-10">
            <h1 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <div class="p-2 bg-indigo-50 rounded-lg">
                    <i data-lucide="grid" class="w-5 h-5 text-primary"></i>
                </div>
                Katalog Produk
            </h1>
            <div class="relative group">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                <input type="text" id="searchProduct" placeholder="Cari barang (Ctrl+/)..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none w-48 transition-all focus:w-64 placeholder:text-slate-400">
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50 custom-scrollbar">
            <?php if(empty($products)): ?>
                <div class="h-full flex flex-col items-center justify-center text-slate-400">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="package-open" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <p class="font-medium">Stok barang kosong.</p>
                    <a href="/products" class="text-primary text-sm font-bold mt-2 hover:underline flex items-center gap-1">
                        Isi Stok Dulu <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="productGrid">
                    <?php foreach($products as $p): ?>
                    <!-- ProductModel return array, jadi $p['id'] aman -->
                    <div onclick='addToCart(<?= json_encode($p) ?>)' class="product-card bg-white p-3 rounded-xl border border-slate-200 hover:border-primary hover:shadow-lg hover:-translate-y-1 cursor-pointer transition-all duration-200 group relative">
                        <!-- Icon Placeholder -->
                        <div class="aspect-square bg-slate-50 rounded-lg mb-3 flex items-center justify-center text-slate-300 group-hover:bg-indigo-50 group-hover:text-primary transition-colors duration-300">
                            <i data-lucide="package" class="w-10 h-10 stroke-1"></i>
                        </div>
                        
                        <h3 class="font-bold text-slate-700 text-sm truncate group-hover:text-primary transition-colors"><?= esc($p['name']) ?></h3>
                        <div class="flex justify-between items-end mt-1">
                            <span class="text-primary font-bold text-sm">Rp <?= number_format($p['sell_price'],0,',','.') ?></span>
                            <span class="text-[10px] <?= $p['stock'] < 5 ? 'text-red-500 font-bold' : 'text-slate-400' ?>">Stok: <?= $p['stock'] ?></span>
                        </div>

                        <!-- Add Overlay -->
                        <div class="absolute inset-0 bg-slate-900/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"></div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-y-2 group-hover:translate-y-0">
                            <span class="bg-primary text-white w-8 h-8 flex items-center justify-center rounded-full shadow-lg">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: CART & CHECKOUT -->
    <div class="w-full md:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 flex flex-col h-1/2 md:h-auto z-20">
        <div class="p-4 border-b border-slate-100 bg-slate-50/80 backdrop-blur rounded-t-2xl flex justify-between items-center">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-primary"></i> Keranjang
            </h2>
            <span class="text-xs font-medium px-2 py-1 bg-white border border-slate-200 rounded-md text-slate-500" id="itemCountBadge">0 Item</span>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" id="cartContainer">
            <!-- Items injected via JS -->
            <div id="emptyCart" class="h-full flex flex-col items-center justify-center text-slate-400 py-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                    <i data-lucide="shopping-bag" class="w-8 h-8 opacity-30"></i>
                </div>
                <p class="text-sm font-medium">Keranjang kosong</p>
                <p class="text-xs mt-1 text-slate-400">Pilih produk di sebelah kiri</p>
            </div>
        </div>

        <!-- Checkout Section -->
        <div class="p-5 bg-white border-t border-slate-100 rounded-b-2xl shadow-[0_-5px_20px_rgba(0,0,0,0.03)]">
            <div class="flex justify-between items-end mb-4">
                <span class="text-slate-500 text-sm font-medium mb-1">Total Tagihan</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight" id="totalDisplay">Rp 0</span>
            </div>

            <form action="/pos/checkout" method="post" id="checkoutForm">
                <?= csrf_field() ?>
                <input type="hidden" name="cart_data" id="cartDataInput">
                <input type="hidden" name="total_amount" id="totalAmountInput">

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pl-1">Masuk Ke</label>
                        <div class="relative">
                            <select name="wallet_id" class="w-full text-xs font-medium border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                <?php foreach($wallets as $w): ?>
                                    <option value="<?= $w->id ?>"><?= esc($w->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i data-lucide="chevron-down" class="w-3 h-3 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pl-1">Kategori</label>
                        <div class="relative">
                            <select name="category_id" class="w-full text-xs font-medium border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c->id ?>"><?= esc($c->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i data-lucide="chevron-down" class="w-3 h-3 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="processCheckout()" class="group w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-slate-800 shadow-lg shadow-primary/25 active:scale-[0.98] transition-all flex justify-center items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 group-hover:scale-110 transition-transform"></i> Bayar & Selesaikan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = [];

    // Focus search on slash key
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            document.getElementById('searchProduct').focus();
        }
    });

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        
        if (existing) {
            if(existing.qty < product.stock) {
                existing.qty++;
            } else {
                // Shake animation or toast for error could go here
                alert('Stok tidak cukup!');
                return;
            }
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.sell_price),
                qty: 1,
                max_stock: product.stock
            });
        }
        renderCart();
    }

    function updateQty(id, change) {
        const item = cart.find(item => item.id == id);
        if (item) {
            const newQty = item.qty + change;
            if (newQty > 0 && newQty <= item.max_stock) {
                item.qty = newQty;
            } else if (newQty <= 0) {
                cart = cart.filter(i => i.id != id);
            } else {
                alert('Stok maksimal tercapai');
            }
        }
        renderCart();
    }

    function manualQty(id, value) {
        const item = cart.find(item => item.id == id);
        if (item) {
            let newQty = parseInt(value);
            
            if (isNaN(newQty)) return; 

            if (newQty <= 0) {
                if(confirm('Hapus item ini dari keranjang?')) {
                    cart = cart.filter(i => i.id != id);
                } else {
                    item.qty = 1;
                    document.querySelector(`input[data-id="${id}"]`).value = 1;
                    return; // Stop render to keep focus? No, need to render total
                }
            } else if (newQty > item.max_stock) {
                alert('Stok tidak cukup. Maksimal: ' + item.max_stock);
                item.qty = item.max_stock;
            } else {
                item.qty = newQty;
            }
        }
        renderCart();
        // Restore focus is tricky with full rerender, but acceptable for now
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        const emptyMsg = document.getElementById('emptyCart');
        const badge = document.getElementById('itemCountBadge');
        
        container.innerHTML = '';
        
        if (cart.length === 0) {
            container.appendChild(emptyMsg);
            document.getElementById('totalDisplay').innerText = 'Rp 0';
            badge.innerText = '0 Item';
            // Disable checkout button visually?
            return;
        }

        let total = 0;
        let totalItems = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            totalItems += item.qty;

            const div = document.createElement('div');
            div.className = 'flex justify-between items-center bg-white p-3 rounded-xl border border-slate-100 hover:border-primary/20 transition-colors shadow-sm';
            
            div.innerHTML = `
                <div class="flex-1 min-w-0 pr-2">
                    <h4 class="font-bold text-slate-700 text-sm truncate" title="${item.name}">${item.name}</h4>
                    <p class="text-xs text-primary font-bold mt-0.5">Rp ${itemTotal.toLocaleString('id-ID')}</p>
                    <p class="text-[10px] text-slate-400">@ Rp ${item.price.toLocaleString('id-ID')}</p>
                </div>
                <div class="flex items-center gap-1 bg-slate-50 rounded-lg p-1 border border-slate-100">
                    <button onclick="updateQty('${item.id}', -1)" class="w-6 h-6 flex items-center justify-center bg-white rounded shadow-sm text-xs font-bold text-slate-600 hover:bg-red-50 hover:text-red-500 hover:border-red-200 border border-slate-200 transition-all active:scale-95">-</button>
                    
                    <input type="number" 
                        value="${item.qty}" 
                        data-id="${item.id}"
                        onchange="manualQty('${item.id}', this.value)" 
                        class="w-8 text-center text-xs font-bold bg-transparent border-none focus:ring-0 p-0 appearance-none mx-0.5 text-slate-700"
                        min="1"
                    >
                    
                    <button onclick="updateQty('${item.id}', 1)" class="w-6 h-6 flex items-center justify-center bg-white rounded shadow-sm text-xs font-bold text-slate-600 hover:bg-primary hover:text-white hover:border-primary border border-slate-200 transition-all active:scale-95">+</button>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('totalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('totalAmountInput').value = total;
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
        badge.innerText = totalItems + ' Item';
        
        // Re-initialize icons for newly added elements
        lucide.createIcons();
    }

    // Search Filter
    document.getElementById('searchProduct').addEventListener('keyup', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        
        cards.forEach(card => {
            const name = card.querySelector('h3').innerText.toLowerCase();
            if(name.includes(term)) {
                card.parentElement.style.display = 'block'; // Ensure grid wrapper visible if needed
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    function processCheckout() {
        if(cart.length === 0) {
            // Simple shake animation on empty cart logic could go here
            alert('Keranjang belanja kosong!');
            return;
        }
        if(confirm('Proses transaksi ini? Stok akan berkurang otomatis.')) {
            document.getElementById('checkoutForm').submit();
        }
    }
</script>

<?= $this->endSection() ?>