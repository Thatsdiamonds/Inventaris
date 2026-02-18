<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class QrController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $authLocIds = [];
        if (!$user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $authLocIds = $authLocs->pluck('id')->toArray();
            }
        }

        $query = Item::with(['category', 'location'])->where('is_active', true);

        if (!empty($authLocIds)) {
            $query->whereIn('location_id', $authLocIds);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('uqcode', 'LIKE', "%$search%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->input('condition'));
        }

        $perPage = $request->input('per_page', 10);
        $items = $query->orderBy('uqcode')->paginate($perPage)->withQueryString();
        
        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name');
        if (!empty($authLocIds)) {
            $locations->whereIn('id', $authLocIds);
        }
        $locations = $locations->get();

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris';

        return view('qr.index', compact('items', 'categories', 'locations', 'appName'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
            'format' => 'nullable|string|in:pdf,zip,img,html_print',
        ]);

        // Smart Redirect: If it's a GET request for a single image, download directly
        // This is primarily for the "Quick QR" icon in the Item Index.
        if ($request->isMethod('get') && $request->input('format') === 'img') {
            return $this->downloadFile($request);
        }

        return view('items.download_qr', [
            'title' => 'Menyiapkan Label QR',
            'message' => 'File label sedang diproses untuk ' . count($request->item_ids) . ' barang.',
            'downloadUrl' => route('qr.download_file'),
            'redirectUrl' => $request->input('redirect_url', route('items.index')),
            'method' => 'POST',
            'params' => $request->all()
        ]);
    }

    public function downloadFile(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
            'format' => 'nullable|string|in:pdf,zip,img,html_print',
        ]);

        $itemIds = $request->input('item_ids');
        $format = $request->input('format', 'pdf');
        $items = Item::with(['location', 'category'])->whereIn('id', $itemIds)->get();

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris';

        $layout = \App\Models\ReportLayout::where('report_type', 'qr')->first();
        $columns = $layout && $layout->columns ? json_decode($layout->columns, true) : [
            'church_name',
            'location.name',
            'uqcode',
            'created_at',
            'acquisition_date',
            'qr_code'
        ];
        $customCss = $layout->css ?? '';

        $writer = new PngWriter();

        if ($format === 'zip') {
            $zip = new ZipArchive;
            $zipFileName = 'qr_labels_' . time() . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            if (!file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($items as $item) {
                    $imgContent = $this->createLabelImageGD($item, $appName, $columns);
                    $zip->addFromString($item->uqcode . '.jpg', $imgContent);
                }
                $zip->close();
            }
            $response = response()->download($zipPath)->deleteFileAfterSend(true);
        } elseif ($format === 'img' && $items->count() === 1) {
            $item = $items->first();
            $imgContent = $this->createLabelImageGD($item, $appName, $columns);
            $response = response($imgContent)
                ->header('Content-Type', 'image/jpeg')
                ->header('Content-Disposition', 'attachment; filename="' . $item->uqcode . '.jpg"');

        } elseif ($format === 'html_print') {
             $qrCodes = [];
            foreach ($items as $item) {
                $qrContent = sprintf(
                    "Kode: %s\nNama: %s\nLokasi: %s\nKategori: %s",
                    $item->uqcode,
                    $item->name,
                    $item->location->name ?? '-',
                    $item->category->name ?? '-'
                );

                $qrCode = new QrCode(
                    data: $qrContent,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 200,
                    margin: 0
                );

                $result = $writer->write($qrCode);
                $qrCodes[$item->id] = base64_encode($result->getString());
            }

            return view('qr.print', [
                'items' => $items,
                'qrCodes' => $qrCodes,
                'appName' => $appName,
                'columns' => $columns,
                'customCss' => $customCss
            ]);
        } else {
            // Default or PDF Fallback (if we still supported it)
            // PDF Logic
            $qrCodes = [];
            foreach ($items as $item) {
                $qrContent = sprintf(
                    "Kode: %s\nNama: %s\nLokasi: %s\nKategori: %s",
                    $item->uqcode,
                    $item->name,
                    $item->location->name ?? '-',
                    $item->category->name ?? '-'
                );

                $qrCode = new QrCode(
                    data: $qrContent,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 200,
                    margin: 0
                );

                $result = $writer->write($qrCode);
                $qrCodes[$item->id] = base64_encode($result->getString());
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qr.pdf', [
                'items' => $items,
                'qrCodes' => $qrCodes,
                'appName' => $appName,
                'columns' => $columns,
                'customCss' => $customCss
            ]);

            $response = $pdf->download('qr_labels-' . time() . '.pdf');
        }

        // Sinkronisasi: Set cookie agar client tahu file sudah dikirim
        if ($request->has('download_token')) {
            $response->withCookie(cookie('download_status', $request->download_token, 1, '/', null, false, false));
        }

        return $response;
    }

    private function createLabelImageGD($item, $appName, $columns)
    {
        $padding = 20;
        $gap = 30;
        $qrSize = 150;
        $lineSpacing = 8;

        // Prepare lines and calculate dimensions
        $lines = [];
        $maxTextWidth = 0;
        $textTotalHeight = 0;

        foreach ($columns as $col) {
            if ($col === 'qr_code') continue;

            $text = '';
            $font = 4;
            if ($col == 'church_name') { $text = $appName; $font = 5; }
            elseif ($col == 'location.name') $text = "Ruang                  : " . ($item->location->name ?? '-');
            elseif ($col == 'uqcode') $text = "Kode Barang            : " . $item->uqcode;
            elseif ($col == 'created_at') $text = "Tahun Inventarisi      : " . ($item->created_at ? $item->created_at->format('Y') : '-');
            elseif ($col == 'acquisition_date') $text = "Tahun Perolehan        : " . ($item->acquisition_date ? $item->acquisition_date->format('Y') : '-');

            if ($text) {
                $w = imagefontwidth($font) * strlen($text);
                if ($w > $maxTextWidth) $maxTextWidth = $w;
                $lines[] = ['text' => $text, 'font' => $font];
            }
        }

        foreach ($lines as $i => $line) {
            $textTotalHeight += imagefontheight($line['font']);
            if ($i < count($lines) - 1) $textTotalHeight += $lineSpacing;
        }

        $hasQr = in_array('qr_code', $columns);
        
        // Final Dimensions
        $totalWidth = $maxTextWidth + ($hasQr ? ($gap + $qrSize) : 0) + ($padding * 2);
        $totalHeight = max($textTotalHeight, ($hasQr ? $qrSize : 0)) + ($padding * 2);

        $im = imagecreatetruecolor($totalWidth, $totalHeight);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $totalWidth, $totalHeight, $white);
        imagerectangle($im, 0, 0, $totalWidth - 1, $totalHeight - 1, $black);

        // Render QR
        if ($hasQr) {
            $qrContent = sprintf(
                "Kode: %s\nNama: %s\nLokasi: %s\nKategori: %s",
                $item->uqcode, $item->name, $item->location->name ?? '-', $item->category->name ?? '-'
            );
            $qrCode = new QrCode(data: $qrContent, size: $qrSize, margin: 0);
            $writer = new PngWriter();
            $qrImg = imagecreatefromstring($writer->write($qrCode)->getString());
            
            $qrX = $totalWidth - $qrSize - $padding;
            $qrY = ($totalHeight - $qrSize) / 2;
            imagecopyresampled($im, $qrImg, (int)$qrX, (int)$qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));
            imagedestroy($qrImg);
        }

        // Render Text (Vertical Center)
        $currentY = $padding + ($totalHeight - ($padding * 2) - $textTotalHeight) / 2;
        foreach ($lines as $line) {
            imagestring($im, $line['font'], $padding, (int)$currentY, $line['text'], $black);
            $currentY += imagefontheight($line['font']) + $lineSpacing;
        }

        ob_start();
        imagejpeg($im, null, 90);
        $content = ob_get_clean();
        imagedestroy($im);
        return $content;
    }
}
