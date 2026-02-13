<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
        }
        .meta-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .income { color: #000; } /* Tetap hitam untuk print formal */
        .expense { color: #000; }

        /* Print Settings */
        @media print {
            @page {
                size: A4;
                margin: 2cm;
            }
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()"> <!-- Otomatis Print saat dibuka -->

    <div class="header">
        <h1>Laporan Keuangan</h1>
        <p><?= esc($business_name ?? 'Bisnis Saya') ?> | <?= esc($user_name) ?></p>
    </div>

    <div class="meta-info">
        <strong>Periode Laporan:</strong><br>
        <?= date('d F Y', strtotime($start_date)) ?> s/d <?= date('d F Y', strtotime($end_date)) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 30%">Keterangan</th>
                <th style="width: 15%">Kategori</th>
                <th style="width: 15%">Dompet</th>
                <th style="width: 10%">Tipe</th>
                <th style="width: 15%" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalMasuk = 0;
            $totalKeluar = 0;
            if(empty($transactions)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi pada periode ini.</td>
                </tr>
            <?php else: ?>
                <?php foreach($transactions as $trx): 
                    if($trx['type'] == 'income') $totalMasuk += $trx['amount'];
                    else $totalKeluar += $trx['amount'];
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($trx['transaction_date'])) ?></td>
                    <td><?= esc($trx['description']) ?></td>
                    <td><?= esc($trx['category_name']) ?></td>
                    <td><?= esc($trx['wallet_name']) ?></td>
                    <td><?= $trx['type'] == 'income' ? 'Masuk' : 'Keluar' ?></td>
                    <td class="text-right">
                        <?= number_format($trx['amount'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total Pemasukan</th>
                <th class="text-right"><?= number_format($totalMasuk, 0, ',', '.') ?></th>
            </tr>
            <tr>
                <th colspan="5" class="text-right">Total Pengeluaran</th>
                <th class="text-right"><?= number_format($totalKeluar, 0, ',', '.') ?></th>
            </tr>
            <tr>
                <th colspan="5" class="text-right">Selisih (Laba/Rugi)</th>
                <th class="text-right"><?= number_format($totalMasuk - $totalKeluar, 0, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>

    <p style="font-size: 10px; text-align: center; margin-top: 50px; color: #666;">
        Dicetak otomatis oleh Sistem Finansia pada <?= date('d F Y H:i') ?>
    </p>

</body>
</html>