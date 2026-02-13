<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Finansia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#0F172A' } } }
        }
    </script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-8 my-4">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Buat Akun Baru</h1>
            <p class="text-slate-500 text-sm mt-2">Mulai rapikan keuangan bisnis Anda hari ini.</p>
        </div>

        <?php if(session()->getFlashdata('errors')): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-xs">
                <ul class="list-disc pl-4">
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/auth/process_register" method="post" class="space-y-3">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="<?= old('name') ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Bisnis (Toko/Usaha)</label>
                <input type="text" name="business_name" value="<?= old('business_name') ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="<?= old('email') ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ulangi Password</label>
                    <input type="password" name="confpassword" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white font-bold py-3 rounded-lg hover:bg-slate-800 transition mt-4">
                Daftar Sekarang
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Sudah punya akun? <a href="/login" class="text-primary font-bold hover:underline">Login</a>
        </p>
    </div>

</body>
</html>