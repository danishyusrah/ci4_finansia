<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="h-[calc(100vh-100px)] flex flex-col md:flex-row gap-4 overflow-hidden">
    
    <!-- LEFT: PRODUCT GRID -->
    <div class="flex-1 flex flex-col overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h1 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i data-lucide="grid" class="w-5 h-5 text-primary"></i> Katalog Produk
            </h1>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                <input type="text" id="searchProduct" placeholder="Cari barang..." class="pl-9 pr-4 py-2 bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none w-48 transition-all focus:w-64">
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 bg-slate-50">
            <?php if(empty($products)): ?>
                <div class="h-full flex flex-col items-center justify-center text-slate-400">
                    <i data-lucide="package-open" class="w-12 h-12 mb-2 opacity-50"></i>
                    <p>Stok barang kosong.</p>
                    <a href="/products" class="text-primary text-sm font-bold mt-2 hover:underline">Isi Stok Dulu</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="productGrid">
                    <?php foreach($products as $p): ?>
                    <!-- ProductModel return array, jadi $p['id'] aman -->
                    <div onclick='addToCart(<?= json_encode($p) ?>)' class="product-card bg-white p-3 rounded-xl border border-slate-200 hover:border-primary hover:shadow-md cursor-pointer transition group relative">
                        <!-- Icon Placeholder -->
                        <div class="aspect-square bg-slate-100 rounded-lg mb-2 flex items-center justify-center text-slate-300 group-hover:text-primary transition">
                            <i data-lucide="package" class="w-8 h-8"></i>
                        </div>
                        
                        <h3 class="font-bold text-slate-700 text-sm truncate"><?= esc($p['name']) ?></h3>
                        <div class="flex justify-between items-end mt-1">
                            <span class="text-primary font-bold text-sm">Rp <?= number_format($p['sell_price'],0,',','.') ?></span>
                            <span class="text-[10px] text-slate-400">Stok: <?= $p['stock'] ?></span>
                        </div>

                        <!-- Add Overlay -->
                        <div class="absolute inset-0 bg-primary/10 rounded-xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <span class="bg-primary text-white text-xs font-bold px-2 py-1 rounded-full shadow">+ Tambah</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: CART & CHECKOUT -->
    <div class="w-full md:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 flex flex-col h-1/2 md:h-auto">
        <div class="p-4 border-b border-slate-100 bg-slate-50 rounded-t-2xl">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i> Keranjang
            </h2>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="cartContainer">
            <!-- Items injected via JS -->
            <div id="emptyCart" class="text-center text-slate-400 py-10">
                <p class="text-sm">Keranjang kosong</p>
                <p class="text-xs mt-1">Pilih produk di sebelah kiri</p>
            </div>
        </div>

        <!-- Checkout Section -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl">
            <div class="flex justify-between items-center mb-4">
                <span class="text-slate-500 text-sm">Total Tagihan</span>
                <span class="text-2xl font-bold text-slate-800" id="totalDisplay">Rp 0</span>
            </div>

            <form action="/pos/checkout" method="post" id="checkoutForm">
                <?= csrf_field() ?>
                <input type="hidden" name="cart_data" id="cartDataInput">
                <input type="hidden" name="total_amount" id="totalAmountInput">

                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Masuk Ke</label>
                        <select name="wallet_id" class="w-full text-xs border-slate-300 rounded-lg p-2 bg-white">
                            <?php foreach($wallets as $w): ?>
                                <!-- PERBAIKAN: Menggunakan sintaks Object ($w->id) -->
                                <option value="<?= $w->id ?>"><?= esc($w->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Kategori</label>
                        <select name="category_id" class="w-full text-xs border-slate-300 rounded-lg p-2 bg-white">
                            <?php foreach($categories as $c): ?>
                                <!-- PERBAIKAN: Menggunakan sintaks Object ($c->id) -->
                                <option value="<?= $c->id ?>"><?= esc($c->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="button" onclick="processCheckout()" class="w-full bg-primary text-white font-bold py-3 rounded-xl hover:bg-slate-800 shadow-lg shadow-primary/30 transition flex justify-center items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4"></i> Bayar & Selesaikan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        
        if (existing) {
            if(existing.qty < product.stock) {
                existing.qty++;
            } else {
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
        // id bisa string/int, gunakan loose equality ==
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

    // Fungsi Baru: Menangani Input Manual
    function manualQty(id, value) {
        const item = cart.find(item => item.id == id);
        if (item) {
            let newQty = parseInt(value);
            
            // Validasi jika input kosong atau bukan angka
            if (isNaN(newQty)) return; 

            if (newQty <= 0) {
                if(confirm('Hapus item ini dari keranjang?')) {
                    cart = cart.filter(i => i.id != id);
                } else {
                    // Reset ke 1 jika batal hapus
                    item.qty = 1;
                }
            } else if (newQty > item.max_stock) {
                alert('Stok tidak cukup. Maksimal: ' + item.max_stock);
                item.qty = item.max_stock;
            } else {
                item.qty = newQty;
            }
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        const emptyMsg = document.getElementById('emptyCart');
        
        container.innerHTML = '';
        
        if (cart.length === 0) {
            container.appendChild(emptyMsg);
            document.getElementById('totalDisplay').innerText = 'Rp 0';
            return;
        }

        let total = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;

            const div = document.createElement('div');
            div.className = 'flex justify-between items-center bg-white p-3 rounded-lg border border-slate-200';
            // Perubahan: Menambahkan input type="number" menggantikan span
            div.innerHTML = `
                <div class="flex-1">
                    <h4 class="font-bold text-slate-700 text-sm">${item.name}</h4>
                    <p class="text-xs text-primary font-medium">Rp ${item.price.toLocaleString('id-ID')}</p>
                </div>
                <div class="flex items-center gap-1 bg-slate-50 rounded-lg p-1">
                    <button onclick="updateQty('${item.id}', -1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow text-xs font-bold hover:bg-red-50 hover:text-red-500 transition border border-slate-200">-</button>
                    
                    <input type="number" 
                        value="${item.qty}" 
                        onchange="manualQty('${item.id}', this.value)" 
                        class="w-12 text-center text-xs font-bold bg-transparent border-none focus:ring-0 p-0 appearance-none mx-1"
                        min="1"
                    >
                    
                    <button onclick="updateQty('${item.id}', 1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow text-xs font-bold hover:bg-primary hover:text-white transition border border-slate-200">+</button>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('totalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('totalAmountInput').value = total;
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    }

    // Search Filter
    document.getElementById('searchProduct').addEventListener('keyup', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        
        cards.forEach(card => {
            const name = card.querySelector('h3').innerText.toLowerCase();
            if(name.includes(term)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    function processCheckout() {
        if(cart.length === 0) {
            alert('Keranjang belanja kosong!');
            return;
        }
        if(confirm('Proses transaksi ini? Stok akan berkurang otomatis.')) {
            document.getElementById('checkoutForm').submit();
        }
    }
</script>

<?= $this->endSection() ?>