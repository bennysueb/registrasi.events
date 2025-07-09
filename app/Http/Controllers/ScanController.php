<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function scanIn()
    {
        return view("scan.scanIn");
    }

    public function scanInProcess(Request $request)
    {
        $status = "error";
        $message = "QR Code not found";
        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('qrcode_invitation',  $request->qrcode)->first();

        if ($invt) {
            if ($invt->checkin_invitation == null) {
                $status = "success";
                $data['checkin_invitation'] = Carbon::now();
                if ($file = $request->file('webcam')) {
                    File::ensureDirectoryExists(public_path('img/scan/scan-in'));
                    $file->move(public_path('img/scan/scan-in'), $request->qrcode . ".jpeg");
                    $data['checkin_img_invitation'] = $request->qrcode . ".jpeg";
                }
                Invitation::where('id_invitation', $invt->id_invitation)->update($data);
                $message = "Welcome : " . $invt->name_guest;
            } else {
                $status = "warning";
                $message = "You've Checked-in";
            }
        }
        return response()->json([
            'status'    => $status,
            'message'   => $message
        ]);
    }


    // SCAN RFID
    public function scanRfid()
    {
        return view("scan.scanRfid");
    }

    public function scanRfidProcess(Request $request)
    {
        $status = "error";
        $message = "RFID Tag not found";

        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('rfid_number_invitation', $request->rfid)
            ->first();

        if ($invt) {
            if ($invt->checkin_invitation == null) {
                $status = "success";
                $data['checkin_invitation'] = Carbon::now();
                Invitation::where('id_invitation', $invt->id_invitation)->update($data);
                $message = "Welcome : " . $invt->name_guest;
            } else {
                $status = "warning";
                $message = "You've Checked-in";
            }
        }

        return response()->json([
            'status'    => $status,
            'message'   => $message
        ]);
    }







    public function scanOut()
    {
        return view("scan.scanOut");
    }

    public function scanOutProcess(Request $request)
    {
        $status = "error";
        $message = "Kode tidak ditemukan";
        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('qrcode_invitation',  $request->qrcode)->first();

        if ($invt) {
            $status = "warning";
            if ($invt->checkin_invitation == null) {
                $message = "Tamu Belum Scan In";
            } else if ($invt->checkout_invitation == null) {
                $status = "success";
                $data['checkout_invitation'] = Carbon::now();
                if ($file = $request->file('webcam')) {
                    File::ensureDirectoryExists(public_path('img/scan/scan-out'));
                    $file->move(public_path('img/scan/scan-out'), $request->qrcode . ".jpeg");
                    $data['checkout_img_invitation'] = $request->qrcode . ".jpeg";
                }
                Invitation::where('id_invitation', $invt->id_invitation)->update($data);
                $message = "Scan successfully, Please Take Souvenir :)";
            } else {
                $status = "warning";
                $message = "Sorry, you've taken a souvenir!";
            }
        }
        return response()->json([
            'status'    => $status,
            'message'   => $message
        ]);
    }
}
