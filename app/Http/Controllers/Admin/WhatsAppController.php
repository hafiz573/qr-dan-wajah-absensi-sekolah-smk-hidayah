<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    private $bridgeUrl = 'http://localhost:3000';

    public function connect()
    {
        try {
            $response = Http::get($this->bridgeUrl . '/status');
            $data = $response->json();
        } catch (\Exception $e) {
            $data = ['status' => 'DISCONNECTED', 'message' => 'Bridge server offline'];
        }

        return view('admin.whatsapp.connect', $data);
    }

    public function status()
    {
        try {
            $response = Http::get($this->bridgeUrl . '/status');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'DISCONNECTED']);
        }
    }

    public function testMessage(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        try {
            $response = Http::post($this->bridgeUrl . '/send', [
                'number' => $request->number,
                'message' => $request->message,
            ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Pesan percobaan berhasil dikirim.');
            }

            return redirect()->back()->with('error', 'Gagal mengirim pesan: ' . ($response->json()['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal terhubung ke WhatsApp Bridge server.');
        }
    }

    public function logout()
    {
        try {
            $response = Http::post($this->bridgeUrl . '/logout');
            
            if ($response->successful()) {
                $msg = $response->json()['message'] ?? 'Berhasil memutuskan koneksi WhatsApp.';
                return redirect()->back()->with('success', $msg);
            }
            
            // If failed but is JSON
            if ($response->json()) {
                return redirect()->back()->with('error', 'Gagal memutuskan koneksi: ' . ($response->json()['message'] ?? 'Alasan tidak diketahui.'));
            }

            return redirect()->back()->with('error', 'Gagal memutuskan koneksi: Server Bridge memberikan respon tidak valid.');
        } catch (\Exception $e) {
            \Log::error('Bridge Connection Logout Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal terhubung ke WhatsApp Bridge server.');
        }
    }
}
