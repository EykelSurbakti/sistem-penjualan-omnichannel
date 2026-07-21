<?php

use App\Models\ShiftSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// =========================================================================
// API ENDPOINT STRUK THERMER PRO / MATE BLUETOOTH PRINT (JSON)
// =========================================================================
Route::get('/api/thermer-receipt/{id}', function ($id, Request $request) {
    $order = \App\Models\Order::with(['items', 'outlet', 'cashier'])->find($id);
    if (!$order) {
        return response()->json(new \stdClass());
    }

    // Helper untuk merapikan teks 2 kolom tepat 32 karakter (58mm)
    $formatRow32 = function ($left, $right) {
        $maxLeftLen = 32 - strlen($right) - 1;
        if (strlen($left) > $maxLeftLen) {
            $left = substr($left, 0, $maxLeftLen);
        }
        $spaces = max(1, 32 - strlen($left) - strlen($right));
        return $left . str_repeat(' ', $spaces) . $right;
    };

    // Helper rata tengah 32 karakter
    $formatCenter32 = function ($text) {
        $text = trim($text);
        if (strlen($text) >= 32) return substr($text, 0, 32);
        $pad = (int) floor((32 - strlen($text)) / 2);
        return str_repeat(' ', max(0, $pad)) . $text;
    };

    // Helper untuk merapikan nama kasir jika panjang
    $formatCashier32 = function ($name) {
        $label = "Cashier      ";
        $maxRight = 32 - strlen($label);
        if (strlen($name) <= $maxRight) {
            return $label . $name;
        }
        $line1 = $label . substr($name, 0, $maxRight);
        $line2 = str_repeat(' ', strlen($label)) . substr($name, $maxRight, $maxRight);
        return $line1 . "\n" . $line2;
    };

    // Helper untuk membersihkan karakter non-ASCII (seperti tanda petik melengkung ’ yang jadi ┌ÇÖ di printer thermal)
    $cleanAscii = function ($text) {
        if (!$text) return '';
        $search = ["\xE2\x80\x98", "\xE2\x80\x99", "\xE2\x80\x9A", "\xE2\x80\x9B", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x93", "\xE2\x80\x94"];
        $replace = ["'", "'", "'", "'", '"', '"', "-", "-"];
        return preg_replace('/[^\x20-\x7E]/', '', str_replace($search, $replace, $text));
    };

    $notesData = !empty($order->notes) ? json_decode($order->notes, true) : [];
    $cashReceived = $request->query('cash', $notesData['cash_received'] ?? $order->total_amount);
    $changeAmount = $request->query('change', $notesData['change_amount'] ?? max(0, $cashReceived - $order->total_amount));
    $cashierName = $request->query('cashier') ?: ($notesData['cashier_name'] ?? $order->cashier?->name ?? 'Kasir');

    // Rakip seluruh teks struk dalam 1 string berurutan agar urutan baris dijamin 100% presisi dan tidak diacak oleh HashMap Java
    $lines = [];
    $lines[] = $formatCenter32("Muliku Plastik store");
    $lines[] = $formatCenter32(strtoupper($cleanAscii($order->outlet?->name ?? 'MULIKU STORE 02')));
    $lines[] = $formatCenter32("Jalan raya bungin pekon purawiwi");
    $lines[] = $formatCenter32("tan kecamatan kebun tebu");
    $lines[] = $formatCenter32("Indonesia, Lampung");
    $lines[] = $formatCenter32("Lampung Barat");
    $lines[] = $formatCenter32("081278295297");
    $lines[] = $formatCenter32("Receipt No. " . $order->order_number);
    $lines[] = " ";
    $lines[] = $formatRow32("Order Date", $order->created_at->format('d/m/Y H:i:s'));
    $lines[] = $formatCashier32($cleanAscii($cashierName));
    $lines[] = "--------------------------------";

    $totalItems = 0;
    foreach ($order->items as $item) {
        $qty = (int)$item->quantity;
        $totalItems += $qty;
        $priceFormatted = number_format($item->unit_price, 0, ',', '.');
        $itemTotalFormatted = number_format($item->total_price, 0, ',', '.');
        $name = strtoupper($cleanAscii($item->product_name ?? \App\Models\Product::find($item->product_id)?->name ?? 'ITEM'));

        if ($qty == 1) {
            $lines[] = $formatRow32("1 " . $name, $itemTotalFormatted);
        } else {
            $lines[] = $formatRow32($qty . " " . $name, $itemTotalFormatted);
            $lines[] = "  @ " . $priceFormatted;
        }
    }

    $lines[] = "--------------------------------";
    $lines[] = $totalItems . " Items";
    $lines[] = $formatRow32("Subtotal", number_format($order->subtotal ?? $order->total_amount, 0, ',', '.'));
    $lines[] = $formatRow32("TOTAL", number_format($order->total_amount, 0, ',', '.'));
    $lines[] = " ";
    $lines[] = $formatRow32("Cash", number_format($cashReceived, 0, ',', '.'));
    $lines[] = $formatRow32("Change Due", number_format($changeAmount, 0, ',', '.'));
    $lines[] = " ";
    $lines[] = $formatCenter32("@mulikustore");
    $lines[] = $formatCenter32("Thanks for shopping");
    $lines[] = " ";

    $fullReceiptText = implode("\n", $lines);

    $a = [];

    // Objek 0: Seluruh Teks Struk (Rata Kiri, Format 0 agar tidak ada tanda seru !)
    $a[] = [
        'type' => 0, // text
        'content' => $fullReceiptText,
        'bold' => 0,
        'align' => 0,
        'format' => 0
    ];

    // Objek 1: Barcode Nomor Transaksi (Rata Tengah)
    $a[] = [
        'type' => 2, // barcode
        'value' => preg_replace('/[^a-zA-Z0-9]/', '', $order->order_number),
        'width' => 160,
        'height' => 45,
        'align' => 1
    ];

    // Kembalikan sebagai JSONObject (dengan JSON_FORCE_OBJECT) agar aplikasi Java Thermer bisa membacanya tanpa error
    return response()->json($a, 200, [], JSON_FORCE_OBJECT);
})->name('api.thermer-receipt');

// =========================================================================
// API ENDPOINT SIMPLE BLUETOOTH PRINTER (btprinter:// scheme)
// =========================================================================
Route::get('/api/btprinter-redirect/{id}', function ($id, Request $request) {
    $order = \App\Models\Order::with(['items', 'outlet', 'cashier'])->find($id);
    if (!$order) {
        return redirect()->to('/');
    }

    $formatRow32 = function ($left, $right) {
        $maxLeftLen = 32 - strlen($right) - 1;
        if (strlen($left) > $maxLeftLen) {
            $left = substr($left, 0, $maxLeftLen);
        }
        $spaces = max(1, 32 - strlen($left) - strlen($right));
        return $left . str_repeat(' ', $spaces) . $right;
    };

    $formatCenter32 = function ($text) {
        $text = trim($text);
        if (strlen($text) >= 32) return substr($text, 0, 32);
        $pad = (int) floor((32 - strlen($text)) / 2);
        return str_repeat(' ', max(0, $pad)) . $text;
    };

    $formatCashier32 = function ($name) {
        $label = "Cashier      ";
        $maxRight = 32 - strlen($label);
        if (strlen($name) <= $maxRight) {
            return $label . $name;
        }
        $line1 = $label . substr($name, 0, $maxRight);
        $line2 = str_repeat(' ', strlen($label)) . substr($name, $maxRight, $maxRight);
        return $line1 . "\n" . $line2;
    };

    // Helper untuk membersihkan karakter non-ASCII
    $cleanAscii = function ($text) {
        if (!$text) return '';
        $search = ["\xE2\x80\x98", "\xE2\x80\x99", "\xE2\x80\x9A", "\xE2\x80\x9B", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x93", "\xE2\x80\x94"];
        $replace = ["'", "'", "'", "'", '"', '"', "-", "-"];
        return preg_replace('/[^\x20-\x7E]/', '', str_replace($search, $replace, $text));
    };

    $notesData = !empty($order->notes) ? json_decode($order->notes, true) : [];
    $cashReceived = $request->query('cash', $notesData['cash_received'] ?? $order->total_amount);
    $changeAmount = $request->query('change', $notesData['change_amount'] ?? max(0, $cashReceived - $order->total_amount));
    $cashierName = $request->query('cashier') ?: ($notesData['cashier_name'] ?? $order->cashier?->name ?? 'Kasir');

    $lines = [];
    $lines[] = $formatCenter32("Muliku Plastik store");
    $lines[] = $formatCenter32(strtoupper($cleanAscii($order->outlet?->name ?? 'MULIKU STORE 02')));
    $lines[] = $formatCenter32("Jalan raya bungin pekon purawiwi");
    $lines[] = $formatCenter32("tan kecamatan kebun tebu");
    $lines[] = $formatCenter32("Indonesia, Lampung");
    $lines[] = $formatCenter32("Lampung Barat");
    $lines[] = $formatCenter32("081278295297");
    $lines[] = $formatCenter32("Receipt No. " . $order->order_number);
    $lines[] = " ";
    $lines[] = $formatRow32("Order Date", $order->created_at->format('d/m/Y H:i:s'));
    $lines[] = $formatCashier32($cleanAscii($cashierName));
    $lines[] = "--------------------------------";

    $totalItems = 0;
    foreach ($order->items as $item) {
        $qty = (int)$item->quantity;
        $totalItems += $qty;
        $priceFormatted = number_format($item->unit_price, 0, ',', '.');
        $itemTotalFormatted = number_format($item->total_price, 0, ',', '.');
        $name = strtoupper($cleanAscii($item->product_name ?? \App\Models\Product::find($item->product_id)?->name ?? 'ITEM'));

        if ($qty == 1) {
            $lines[] = $formatRow32("1 " . $name, $itemTotalFormatted);
        } else {
            $lines[] = $formatRow32($qty . " " . $name, $itemTotalFormatted);
            $lines[] = "  @ " . $priceFormatted;
        }
    }

    $lines[] = "--------------------------------";
    $lines[] = $totalItems . " Items";
    $lines[] = $formatRow32("Subtotal", number_format($order->subtotal ?? $order->total_amount, 0, ',', '.'));
    $lines[] = $formatRow32("TOTAL", number_format($order->total_amount, 0, ',', '.'));
    $lines[] = " ";
    $lines[] = $formatRow32("Cash", number_format($cashReceived, 0, ',', '.'));
    $lines[] = $formatRow32("Change Due", number_format($changeAmount, 0, ',', '.'));
    $lines[] = " ";
    $lines[] = $formatCenter32("@mulikustore");
    $lines[] = $formatCenter32("Thanks for shopping");
    $lines[] = $formatCenter32("*" . preg_replace('/[^a-zA-Z0-9]/', '', $order->order_number) . "*");
    $lines[] = "\n\n ";

    $fullReceiptText = implode("\n", $lines);
    $deepLink = "btprinter://print?content=" . urlencode($fullReceiptText);

    return redirect()->to($deepLink);
})->name('api.btprinter-redirect');

// =========================================================================
// API ENDPOINT RAWBT PRINTER (rawbt:base64 scheme - GRATIS AUTO-RETURN)
// =========================================================================
Route::get('/api/rawbt-redirect/{id}', function ($id, Request $request) {
    $order = \App\Models\Order::with(['items', 'outlet', 'cashier'])->find($id);
    if (!$order) {
        return redirect()->to('/');
    }

    $formatRow32 = function ($left, $right) {
        $maxLeftLen = 32 - strlen($right) - 1;
        if (strlen($left) > $maxLeftLen) {
            $left = substr($left, 0, $maxLeftLen);
        }
        $spaces = max(1, 32 - strlen($left) - strlen($right));
        return $left . str_repeat(' ', $spaces) . $right;
    };

    $formatCenter32 = function ($text) {
        $text = trim($text);
        if (strlen($text) >= 32) return substr($text, 0, 32);
        $pad = (int) floor((32 - strlen($text)) / 2);
        return str_repeat(' ', max(0, $pad)) . $text;
    };

    $formatCashier32 = function ($name) {
        $label = "Cashier      ";
        $maxRight = 32 - strlen($label);
        if (strlen($name) <= $maxRight) {
            return $label . $name;
        }
        $line1 = $label . substr($name, 0, $maxRight);
        $line2 = str_repeat(' ', strlen($label)) . substr($name, $maxRight, $maxRight);
        return $line1 . "\n" . $line2;
    };

    // Helper untuk membersihkan karakter non-ASCII
    $cleanAscii = function ($text) {
        if (!$text) return '';
        $search = ["\xE2\x80\x98", "\xE2\x80\x99", "\xE2\x80\x9A", "\xE2\x80\x9B", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x93", "\xE2\x80\x94"];
        $replace = ["'", "'", "'", "'", '"', '"', "-", "-"];
        return preg_replace('/[^\x20-\x7E]/', '', str_replace($search, $replace, $text));
    };

    $notesData = !empty($order->notes) ? json_decode($order->notes, true) : [];
    $cashReceived = $request->query('cash', $notesData['cash_received'] ?? $order->total_amount);
    $changeAmount = $request->query('change', $notesData['change_amount'] ?? max(0, $cashReceived - $order->total_amount));
    $cashierName = $request->query('cashier') ?: ($notesData['cashier_name'] ?? $order->cashier?->name ?? 'Kasir');

    $lines = [];
    $lines[] = $formatCenter32("Muliku Plastik store");
    $lines[] = $formatCenter32(strtoupper($cleanAscii($order->outlet?->name ?? 'MULIKU STORE 02')));
    $lines[] = $formatCenter32("Jalan raya bungin pekon purawiwi");
    $lines[] = $formatCenter32("tan kecamatan kebun tebu");
    $lines[] = $formatCenter32("Indonesia, Lampung");
    $lines[] = $formatCenter32("Lampung Barat");
    $lines[] = $formatCenter32("081278295297");
    $lines[] = $formatCenter32("Receipt No. " . $order->order_number);
    $lines[] = " ";
    $lines[] = $formatRow32("Order Date", $order->created_at->format('d/m/Y H:i:s'));
    $lines[] = $formatCashier32($cleanAscii($cashierName));
    $lines[] = "--------------------------------";

    $totalItems = 0;
    foreach ($order->items as $item) {
        $qty = (int)$item->quantity;
        $totalItems += $qty;
        $priceFormatted = number_format($item->unit_price, 0, ',', '.');
        $itemTotalFormatted = number_format($item->total_price, 0, ',', '.');
        $name = strtoupper($cleanAscii($item->product_name ?? \App\Models\Product::find($item->product_id)?->name ?? 'ITEM'));

        if ($qty == 1) {
            $lines[] = $formatRow32("1 " . $name, $itemTotalFormatted);
        } else {
            $lines[] = $formatRow32($qty . " " . $name, $itemTotalFormatted);
            $lines[] = "  @ " . $priceFormatted;
        }
    }

    $lines[] = "--------------------------------";
    $lines[] = $totalItems . " Items";
    $lines[] = $formatRow32("Subtotal", number_format($order->subtotal ?? $order->total_amount, 0, ',', '.'));
    $lines[] = $formatRow32("TOTAL", number_format($order->total_amount, 0, ',', '.'));
    $lines[] = " ";
    $lines[] = $formatRow32("Cash", number_format($cashReceived, 0, ',', '.'));
    $lines[] = $formatRow32("Change Due", number_format($changeAmount, 0, ',', '.'));
    $lines[] = " ";
    $lines[] = $formatCenter32("IG: mulikustore");
    $lines[] = $formatCenter32("Thanks for shopping");

    $cleanOrderRef = preg_replace('/[^a-zA-Z0-9]/', '', $order->order_number);

    // Generator Gambar Raster Barcode (GS v 0)
    $genBarcodeImage = function ($codeStr) {
        if (!$codeStr) return '';
        $c39 = [
            '0'=>'000110100','1'=>'100100001','2'=>'001100001','3'=>'101100000','4'=>'000110001',
            '5'=>'100110000','6'=>'001110000','7'=>'000100101','8'=>'100100100','9'=>'001100100',
            'A'=>'100001001','B'=>'001001001','C'=>'101001000','D'=>'000011001','E'=>'100011000',
            'F'=>'001011000','G'=>'000001101','H'=>'100001100','I'=>'001001100','J'=>'000011100',
            'K'=>'100000011','L'=>'001000011','M'=>'101000010','N'=>'000010011','O'=>'100010010',
            'P'=>'001010010','Q'=>'000000111','R'=>'100000110','S'=>'001000110','T'=>'000010110',
            'U'=>'110000001','V'=>'011000001','W'=>'111000000','X'=>'010010001','Y'=>'110010000',
            'Z'=>'011010000','-'=>'010000101','*'=>'001001011'
        ];
        $clean = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $codeStr));
        $full = '*' . $clean . '*';
        $dots = '';
        foreach (str_split($full) as $ch) {
            $pat = $c39[$ch] ?? $c39['0'];
            for ($i = 0; $i < 9; $i++) {
                $isBar = ($i % 2 == 0);
                $isWide = ($pat[$i] == '1');
                $dots .= str_repeat($isBar ? '1' : '0', $isWide ? 3 : 1);
            }
            $dots .= '0'; // Inter-character gap
        }
        $pad = max(0, floor((384 - strlen($dots)) / 2));
        $dots = str_repeat('0', $pad) . $dots;
        while (strlen($dots) < 384) $dots .= '0';
        $row = '';
        for ($i = 0; $i < 384; $i += 8) {
            $row .= chr(bindec(substr($dots, $i, 8)));
        }
        return "\x1D\x76\x30\x00\x30\x00\x3C\x00" . str_repeat($row, 60);
    };

    // Generator Gambar Raster (GS v 0) untuk Logo Instagram + @mulikustore (UKURAN BESAR / BOLD)
    $genIgHeader = function () {
        if (!function_exists('imagecreate')) return "@mulikustore\n";
        $im = imagecreate(384, 40);
        imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        $temp = imagecreate(115, 18);
        imagecolorallocate($temp, 255, 255, 255);
        $tBlack = imagecolorallocate($temp, 0, 0, 0);
        imagestring($temp, 5, 2, 1, "@mulikustore", $tBlack);

        $textW = floor(112 * 1.8);
        $textH = floor(16 * 1.8);
        $igW = 28;
        $gap = 10;
        $totalW = $igW + $gap + $textW;
        $startX = floor((384 - $totalW) / 2);
        $startY = 6;

        for ($t = 0; $t < 3; $t++) {
            imagerectangle($im, $startX + $t, $startY + $t, $startX + $igW - 1 - $t, $startY + $igW - 1 - $t, $black);
        }
        for ($r = 12; $r <= 14; $r++) {
            imagearc($im, $startX + 14, $startY + 14, $r, $r, 0, 360, $black);
        }
        imagefilledrectangle($im, $startX + 21, $startY + 6, $startX + 23, $startY + 8, $black);

        imagecopyresized($im, $temp, $startX + $igW + $gap, $startY - 1, 0, 0, $textW, $textH, 112, 16);
        imagedestroy($temp);

        $rowBytes = '';
        for ($y = 0; $y < 40; $y++) {
            for ($x = 0; $x < 384; $x += 8) {
                $byte = 0;
                for ($b = 0; $b < 8; $b++) {
                    if ($x + $b < 384 && imagecolorat($im, $x + $b, $y) === $black) {
                        $byte |= (1 << (7 - $b));
                    }
                }
                $rowBytes .= chr($byte);
            }
        }
        imagedestroy($im);
        return "\x1D\x76\x30\x00\x30\x00\x28\x00" . $rowBytes;
    };

    // Untuk RawBT: urutan sempurna -> [Gambar Logo IG + @mulikustore] -> Thanks for shopping -> [Gambar Barcode] -> Nomor Resi rapat di bawah barcode
    $topLines = implode("\n", array_slice($lines, 0, count($lines) - 2));
    $igRaster = $genIgHeader();
    $barcodeRaster = $genBarcodeImage($cleanOrderRef);
    $fullBinaryPayload = $topLines . "\n\n" . $igRaster . "\n" . $formatCenter32("Thanks for shopping") . "\n\n" . $barcodeRaster . $formatCenter32($cleanOrderRef) . "\n\n\n\n";
    $deepLink = "rawbt:base64," . base64_encode($fullBinaryPayload);

    return redirect()->to($deepLink);
})->name('api.rawbt-redirect');

Route::middleware(['auth'])->group(function () {
    Route::get('/portal-kasir', function () {
        $activeShift = ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return view('portal-kasir', [
            'activeShift' => $activeShift
        ]);
    })->name('portal-kasir');

    Route::post('/portal-kasir/buka-shift', function (Request $request) {
        $request->validate([
            'cashier_name' => 'required|string|max:100',
            'initial_cash' => 'required|numeric|min:0'
        ]);

        $outletId = auth()->user()->outlet_id ?? 1;
        $cashierName = $request->input('cashier_name', auth()->user()->name);

        $defaultShift = DB::table('shifts')->first();
        if (!$defaultShift) {
            $shiftId = DB::table('shifts')->insertGetId([
                'outlet_id' => $outletId,
                'name' => 'Shift Reguler Toko',
                'start_time' => '07:00:00',
                'end_time' => '22:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $shiftId = $defaultShift->id;
        }

        // Tutup shift terbuka sebelumnya untuk user ini agar mulai sesi shift baru yang bersih
        ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

        $newShift = ShiftSession::create([
            'shift_id' => $shiftId,
            'user_id' => auth()->id(),
            'outlet_id' => $outletId,
            'cashier_name' => $cashierName,
            'initial_cash' => $request->input('initial_cash', 0),
            'status' => 'open',
            'opened_at' => now(),
        ]);

        \App\Models\ActivityLog::record(
            'SHIFT',
            'Shift Kasir',
            "Membuka shift kasir baru atas nama '{$cashierName}' dengan modal awal Rp " . number_format($request->input('initial_cash', 0), 0, ',', '.'),
            $newShift
        );

        return redirect('/admin/pos-kasir');
    })->name('buka-shift');

    Route::post('/portal-kasir/tutup-shift', function (Request $request) {
        $shift = ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($shift) {
            $closingCash = (int) $request->input('closing_cash', 0);
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_cash' => $closingCash,
            ]);

            \App\Models\ActivityLog::record(
                'SHIFT',
                'Shift Kasir',
                "Menutup dan mengakhiri shift kasir atas nama '{$shift->cashier_name}' dengan uang akhir laci Rp " . number_format($closingCash, 0, ',', '.'),
                $shift
            );
        }

        return redirect('/portal-kasir');
    })->name('tutup-shift');

    Route::get('/katalog-toko', \App\Livewire\KatalogToko::class)->name('katalog-toko');

    Route::get('/admin/api/check-new-orders', function (\Illuminate\Http\Request $request) {
        if (!auth()->check() || auth()->user()->outlet_id !== null) {
            return response()->json(['has_new' => false], 403);
        }

        $lastCheck = $request->query('last_check');
        if (!$lastCheck) {
            return response()->json([
                'has_new' => false,
                'server_time' => now()->format('Y-m-d H:i:s')
            ]);
        }

        try {
            // Gunakan perbandingan langsung string format Y-m-d H:i:s agar sinkron sempurna dengan zona waktu server database
            $orders = \App\Models\Order::with('outlet')
                ->where('created_at', '>', $lastCheck)
                ->latest()
                ->get();

            return response()->json([
                'has_new' => $orders->isNotEmpty(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'outlet_name' => $order->outlet?->name ?: 'Cabang Toko',
                        'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                        'created_at' => $order->created_at->format('H:i:s'),
                    ];
                })
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'has_new' => false,
                'server_time' => now()->format('Y-m-d H:i:s')
            ]);
        }
    })->name('admin.api.check-new-orders');

    Route::post('/admin/api/push-subscribe', function (\Illuminate\Http\Request $request) {
        if (!auth()->check() || auth()->user()->outlet_id !== null) {
            return response()->json(['success' => false], 403);
        }
        $endpoint = $request->input('endpoint');
        $key = $request->input('keys.p256dh');
        $token = $request->input('keys.auth');
        if ($endpoint && $key && $token) {
            auth()->user()->updatePushSubscription($endpoint, $key, $token);
        }
        return response()->json(['success' => true]);
    })->name('admin.api.push-subscribe');

    Route::get('/admin/api/push-status', function () {
        if (!auth()->check() || auth()->user()->outlet_id !== null) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $pubKey = config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');
        $privKey = config('webpush.vapid.private_key') ?: env('VAPID_PRIVATE_KEY');
        return response()->json([
            'status' => 'OK',
            'server_time' => now()->format('Y-m-d H:i:s'),
            'vapid_configured' => (!empty($pubKey) && !empty($privKey)),
            'vapid_public_key_preview' => $pubKey ? substr($pubKey, 0, 15) . '...' : 'NOT_SET_IN_RAILWAY_VARIABLES',
            'current_user_id' => auth()->id(),
            'current_user_email' => auth()->user()->email,
            'user_subscriptions_count' => \Illuminate\Support\Facades\DB::table('push_subscriptions')->where('subscribable_id', auth()->id())->count(),
            'all_database_subscriptions' => \Illuminate\Support\Facades\DB::table('push_subscriptions')->get(['id', 'subscribable_id', 'endpoint', 'updated_at']),
            'last_webpush_error' => cache()->get('last_webpush_error', 'No errors logged yet'),
            'last_webpush_success' => cache()->get('last_webpush_success', 'No push sent yet via observer'),
        ]);
    })->name('admin.api.push-status');
});
