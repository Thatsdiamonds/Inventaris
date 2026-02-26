<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use ZipArchive;

class QrController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $authLocIds = [];
        if (! $user->isRoot()) {
            $authLocs = $user->authorizedLocations();
            if ($authLocs->isNotEmpty()) {
                $authLocIds = $authLocs->pluck('id')->toArray();
            }
        }

        $query = Item::with(['category', 'location'])->where('is_active', true);

        if (! empty($authLocIds)) {
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
        if (! empty($authLocIds)) {
            $locations->whereIn('id', $authLocIds);
        }
        $locations = $locations->get();

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris';

        return view('qr.index', compact('items', 'categories', 'locations', 'appName'));
    }

    public function printImages()
    {
        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris';

        // Fixed label layout config (100mm x 30mm on A4)
        // Margins calculated for 18 labels per page (2x9)
        // 2 cols of 100mm = 200mm. Margin L/R = (210-200)/2 = 5mm.
        // 9 rows of 30mm = 270mm. Margin T/B = (297-270)/2 = 13.5mm.
        $layout = (object) [
            'name' => 'Default',
            'width' => 100,
            'height' => 30,
            'margin_top' => 13.5,
            'margin_bottom' => 13.5,
            'margin_left' => 5,
            'margin_right' => 5,
            'gap_x' => 0,
            'gap_y' => 0,
            'font_size' => 12,
            'paper_size' => 'A4',
        ];

        return view('qr.print_images', compact('appName', 'layout'));
    }

    /**
     * Return a label image as base64 JSON for a single item.
     * Used by the cross-tab queue system.
     */
    public function labelImageJson(Item $item)
    {
        $item->load(['location', 'category']);

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris';

        $imgContent = $this->createLabelImageGD($item, $appName);

        return response()->json([
            'id' => $item->id,
            'uqcode' => $item->uqcode,
            'name' => $item->name,
            'location' => $item->location->name ?? '-',
            'image' => 'data:image/jpeg;base64,' . base64_encode($imgContent),
        ]);
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
            'message' => 'File label sedang diproses untuk '.count($request->item_ids).' barang.',
            'downloadUrl' => route('qr.download_file'),
            'redirectUrl' => $request->input('redirect_url', route('items.index')),
            'method' => 'POST',
            'params' => $request->all(),
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

        $writer = new PngWriter;

        if ($format === 'zip') {
            $zip = new ZipArchive;
            $zipFileName = 'qr_labels_'.time().'.zip';
            $zipPath = storage_path('app/public/'.$zipFileName);

            if (! file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }

            if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                foreach ($items as $item) {
                    $imgContent = $this->createLabelImageGD($item, $appName);
                    $zip->addFromString($item->uqcode.'.jpg', $imgContent);
                }
                $zip->close();
            }
            $response = response()->download($zipPath)->deleteFileAfterSend(true);
        } elseif ($format === 'img' && $items->count() === 1) {
            $item = $items->first();
            $imgContent = $this->createLabelImageGD($item, $appName);
            $response = response($imgContent)
                ->header('Content-Type', 'image/jpeg')
                ->header('Content-Disposition', 'attachment; filename="'.$item->uqcode.'.jpg"');

        } elseif ($format === 'html_print') {
            $labelImages = [];
            foreach ($items as $item) {
                $imgContent = $this->createLabelImageGD($item, $appName);
                $labelImages[$item->id] = base64_encode($imgContent);
            }

            return view('qr.print', [
                'items' => $items,
                'labelImages' => $labelImages,
                'appName' => $appName,
            ]);
        } else {
            // PDF Fallback
            $labelImages = [];
            foreach ($items as $item) {
                $imgContent = $this->createLabelImageGD($item, $appName);
                $labelImages[$item->id] = base64_encode($imgContent);
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qr.pdf', [
                'items' => $items,
                'labelImages' => $labelImages,
                'appName' => $appName,
            ]);

            $response = $pdf->download('qr_labels-'.time().'.pdf');
        }

        // Sinkronisasi: Set cookie agar client tahu file sudah dikirim
        if ($request->has('download_token')) {
            \Illuminate\Support\Facades\Cookie::queue('download_status', $request->download_token, 1, '/', null, false, false);
        }

        return $response;
    }

    /**
     * Generate a label image (JPEG) for one item using GD.
     * Fixed size: 100mm x 30mm. Layout based on user image:
     * Church Name (Line 1)
     * Ruang, Kode Barang, Tahun Inventaris, Tahun Perolehan (Aligned colons)
     * QR Code (Right Aligned)
     */
    private function createLabelImageGD($item, $appName)
    {
        $dpm = 4; // dots per mm
        $widthMm = 100;
        $heightMm = 30;

        $totalWidth = intval($widthMm * $dpm);
        $totalHeight = intval($heightMm * $dpm);

        $im = imagecreatetruecolor($totalWidth, $totalHeight);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $totalWidth, $totalHeight, $white);
        imagerectangle($im, 0, 0, $totalWidth - 1, $totalHeight - 1, $black);

        $padding = intval(3 * $dpm); // 12px padding

        // 1. Render QR Code (right-aligned)
        $qrSize = $totalHeight - ($padding * 2);
        if ($qrSize > 300) $qrSize = 300;

        $qrContent = sprintf(
            "Kode: %s\nNama: %s\nLokasi: %s\nKategori: %s",
            $item->uqcode, $item->name, $item->location->name ?? '-', $item->category->name ?? '-'
        );

        $qrCode = new QrCode(data: $qrContent, size: $qrSize, margin: 0);
        $writer = new PngWriter;
        $qrImg = imagecreatefromstring($writer->write($qrCode)->getString());

        $qrX = $totalWidth - $qrSize - $padding;
        $qrY = ($totalHeight - $qrSize) / 2;

        imagecopyresampled($im, $qrImg, (int) $qrX, (int) $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));
        imagedestroy($qrImg);

        // 2. Render Text
        $textX = $padding;
        $lineSpacing = 5;

        // Lines following the provided image exactly
        $lines = [
            ['text' => $appName ?: 'Gereja Katolik ST Jusup Paroki Pati', 'font' => 3], // Slightly larger/bold header
            ['text' => 'Ruang            : ' . ($item->location->name ?? '-'), 'font' => 2],
            ['text' => 'Kode Barang      : ' . $item->uqcode, 'font' => 2],
            ['text' => 'Tahun Inventaris : ' . ($item->created_at ? $item->created_at->format('Y') : '-'), 'font' => 2],
            ['text' => 'Tahun Perolehan  : ' . ($item->acquisition_date ? $item->acquisition_date->format('Y') : '-'), 'font' => 2],
        ];

        $textTotalHeight = 0;
        foreach ($lines as $line) {
            $textTotalHeight += imagefontheight($line['font']) + $lineSpacing;
        }
        $textTotalHeight -= $lineSpacing;

        $textY = ($totalHeight - $textTotalHeight) / 2;

        foreach ($lines as $line) {
            imagestring($im, $line['font'], $textX, (int) $textY, $line['text'], $black);
            $textY += imagefontheight($line['font']) + $lineSpacing;
        }

        ob_start();
        imagejpeg($im, null, 90);
        $content = ob_get_clean();
        imagedestroy($im);

        return $content;
    }
}
