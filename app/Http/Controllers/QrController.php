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
        $query = Item::with(['category', 'location']);

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

        $items = $query->orderBy('uqcode')->get();
        
        $categories = Category::all();
        $locations = Location::all();

        $setting = \App\Models\Setting::first();
        $appName = $setting->nama_gereja ?? 'Inventaris';

        return view('qr.index', compact('items', 'categories', 'locations', 'appName'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
            'church_name' => 'required|string',
        ]);

        $itemIds = $request->input('item_ids');
        $churchName = $request->input('church_name');
        
        $items = Item::with('location')->whereIn('id', $itemIds)->get();

        $zip = new ZipArchive;
        $zipFileName = 'qr_labels_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);
        
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            
            foreach ($items as $item) {
                $imgContent = $this->createLabelImage($item, $churchName);
                
                $fileName = $item->uqcode . '.jpg';
                $zip->addFromString($fileName, $imgContent);
            }
            
            $zip->close();
        } else {
            return back()->with('error', 'Failed to create zip file.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function createLabelImage($item, $headerText)
    {
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
            size: 130,
            margin: 0,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrRaw = $result->getString();

        $width = 400;
        $height = 250;
        $im = imagecreate($width, $height);
        
        // Colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        // Border
        imagerectangle($im, 0,0, $width-1, $height-1, $black);

        // Header (Church Name) - Font 5 is largest built-in
        $font = 5;
        $headerX = ($width - imagefontwidth($font) * strlen($headerText)) / 2;
        imagestring($im, $font, $headerX, 10, $headerText, $black);

        // QR Code processing
        $qrImg = imagecreatefromstring($qrRaw);
        $qrW = imagesx($qrImg);
        $qrH = imagesy($qrImg);
        
        // Place QR in center
        $dstX = ($width - $qrW) / 2;
        $dstY = 40;
        imagecopy($im, $qrImg, $dstX, $dstY, 0, 0, $qrW, $qrH);

        // Footer Text
        $uqcode = $item->uqcode;
        $loc = $item->location->name ?? '-';
        
        $fontSmall = 4;
        
        // UQCODE
        $textX = ($width - imagefontwidth($font) * strlen($uqcode)) / 2;
        imagestring($im, $font, $textX, $dstY + $qrH + 10, $uqcode, $black);
        
        // Location
        $locX = ($width - imagefontwidth($fontSmall) * strlen($loc)) / 2;
        imagestring($im, $fontSmall, $locX, $dstY + $qrH + 35, $loc, $black);

        // Capture Output
        ob_start();
        imagejpeg($im);
        $content = ob_get_clean();
        
        imagedestroy($im);
        imagedestroy($qrImg);

        return $content;
    }
}
