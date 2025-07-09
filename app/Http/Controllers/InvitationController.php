<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Invitation;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\inviteExport;
use App\Helpers\IndonesiaHelper;
use App\Models\Setting;

class InvitationController extends Controller
{

    //  Link for Guest access
    public function linkGuest($qrcode)
    {
        if (!file_exists(public_path('/img/qrCode/' . $qrcode . '.png'))) {
            $this->qrcodeGenerator($qrcode);
        }

        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('qrcode_invitation', $qrcode)->first();
        $event = Event::where('id_event', 1)->first();
        $setting = Setting::first(); // Ambil data setting
        if ($invt) {
            return view('link-guest.index', compact('invt', 'event', 'setting'));
            // return view('link-guest.sendMail', compact('invt', 'event'));
        } else {
            return view('link-guest.notFound');
        }
    }
    public function downloadQrCode($code)
    {
        return response()->download(public_path('img/qrCode/' . $code . ".png"));
    }

    //  Link for Guest send email
    public function linkGuestEmail($qrcode)
    {
        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('qrcode_invitation', $qrcode)->first();
        $event = Event::where('id_event', 1)->first();
        $setting = Setting::first(); // Ambil data setting
        return view('link-guest.sendMail', compact('invt', 'event', 'setting'));
    }

    public function linkReminderEmail($qrcode)
    {
        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('qrcode_invitation', $qrcode)->first();
        $event = Event::where('id_event', 1)->first();
        $setting = Setting::first(); // Ambil data setting

        return view('link-guest.reminder', compact('invt', 'event', 'setting'));
    }

    public function getGuest(Request $request)
    {
        if ($request->ajax()) {
            $data = Guest::where('id_guest', $request->id_guest)->first();
            return response()->json([
                'status' => "success",
                'data'  => $data
            ]);
        }
    }

    private function checkUniq($qrcode)
    {
        $cek = Invitation::where('qrcode_invitation', $qrcode)->get();
        return $cek->count() > 0 ? TRUE : FALSE;
    }
    private function generateCode()
    {
        $qrcode = Str::random(6);
        if ($this->checkUniq($qrcode)) {
            return $this->generateCode();
        }
        return $qrcode;
    }

    public function qrcodeGenerator($code)
    {
        File::ensureDirectoryExists(public_path('img/qrCode'));
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($code)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->validateResult(false)
            ->build();
        $result->saveToFile(public_path('/img/qrCode/' . $code . '.png'));
    }


    public function sendEmail()
    {
        $event = Event::where('id_event', 1)->first(); //
        $setting = Setting::first(); // Pastikan baris ini ada di sini, sebelum penggunaan $setting
        $mail = new PHPMailer(true);
        try {
            $guestQrcode    = $_GET['guestQrcode'];
            $guestName      = $_GET['guestName'];
            $guestMail      = $_GET['guestMail'];

            set_time_limit(180);

            $mail->SMTPDebug  = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Timeout    = 120;
            $mail->SMTPKeepAlive = true;
            $mail->Host       = env("MAIL_HOST");
            $mail->SMTPAuth   = true;
            $mail->Username   = env("MAIL_USERNAME");
            $mail->Password   = env("MAIL_PASSWORD");
            $mail->SMTPSecure = env("MAIL_ENCRYPTION");
            $mail->Port       = env("MAIL_PORT");

            $mail->setFrom(env("MAIL_FROM_ADDRESS"), "E-Ticket " . myEvent()->name_event);
            $mail->addAddress($guestMail, $guestName);

            // QR Code tetap di-embed
            $mail->AddEmbeddedImage(public_path('img/qrCode/' . $guestQrcode . '.png'), 'qrcode', 'qrcode');
            $mail->AddEmbeddedImage(public_path('img/app/' . $setting->favicon_app), 'favicon', 'favicon');


            // Tambahkan gambar background event sebagai embedded image
            $imageBgEventPath = public_path('img/event/' . $event->image_bg_event);
            if (file_exists($imageBgEventPath) && !empty($event->image_bg_event)) {
                $mail->AddEmbeddedImage($imageBgEventPath, 'banner_event', $event->image_bg_event);
                $bannerSrc = 'cid:banner_event';
            } else {
                $bannerSrc = 'https://3flo.my.id/content-images/banner_eticket_dikdasmen.png'; // Fallback
            }

            // Tambahkan logo aplikasi sebagai embedded image
            $logoAppPath = public_path('img/event/' . $event->image_event);
            if (file_exists($logoAppPath) && !empty($event->image_event)) {
                $mail->AddEmbeddedImage($logoAppPath, 'logo_aplikasi', $event->image_event);
                $logoSrc = 'cid:logo_aplikasi';
            } else {
                $logoSrc = 'https://3flo.my.id/content-images/top_logo.png'; // Fallback atau default logo
            }

            $mail->isHTML(true);
            $mail->Subject = "E-Ticket " . myEvent()->name_event;
            // Render view dan sertakan invt, event, dan setting (yang sudah di passing dari linkGuestEmail)
            $mail->Body    = $this->linkGuestEmail($guestQrcode)->render();

            $mail->send();
            $mail->SmtpClose();

            Invitation::where('qrcode_invitation', $guestQrcode)->update(['send_email_invitation' => 1]);

            $status = "success";
            $message = "Berhasil mengirim email";
        } catch (Exception $e) {
            $status = "error";
            $message = "Gagal mengirim ke email: " . $e->getMessage();
        }
        return redirect("invite")->with($status, $message);
    }

    public function sendReminderEmail()
    {
        $event = Event::where('id_event', 1)->first();
        $setting = Setting::first(); // Pastikan baris ini ada di sini, sebelum penggunaan $setting
        $mail = new PHPMailer(true);
        try {
            $guestQrcode    = $_GET['guestQrcode'];
            $guestName      = $_GET['guestName'];
            $guestMail      = $_GET['guestMail'];

            // set_time_limit(0); // remove a time limit if not in safe mode OR
            set_time_limit(180); // set the time limit to 120 seconds

            $mail->SMTPDebug  = SMTP::DEBUG_OFF;                        //Enable verbose debug output
            $mail->isSMTP();
            $mail->Timeout    = 120;
            $mail->SMTPKeepAlive = true;
            $mail->Host       = env("MAIL_HOST");                       //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = env("MAIL_USERNAME");                   //SMTP username
            $mail->Password   = env("MAIL_PASSWORD");                   //SMTP password
            $mail->SMTPSecure = env("MAIL_ENCRYPTION");                 //Enable implicit TLS encryption
            $mail->Port       = env("MAIL_PORT");                       //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            $mail->setFrom(env("MAIL_FROM_ADDRESS"), "Reminder " . myEvent()->name_event);
            $mail->addAddress($guestMail, $guestName);

            $mail->AddEmbeddedImage(public_path('img/app/' . $setting->favicon_app), 'favicon', 'favicon');


            // Tambahkan gambar background event sebagai embedded image
            $imageBgEventPath = public_path('img/event/' . $event->image_bg_event);
            if (file_exists($imageBgEventPath) && !empty($event->image_bg_event)) {
                $mail->AddEmbeddedImage($imageBgEventPath, 'banner_event', $event->image_bg_event);
                $bannerSrc = 'cid:banner_event';
            } else {
                $bannerSrc = 'https://3flo.my.id/content-images/banner_eticket_dikdasmen.png'; // Fallback
            }

            // Tambahkan logo aplikasi sebagai embedded image
            $logoAppPath = public_path('img/event/' . $event->image_event);
            if (file_exists($logoAppPath) && !empty($event->image_event)) {
                $mail->AddEmbeddedImage($logoAppPath, 'logo_aplikasi', $event->image_event);
                $logoSrc = 'cid:logo_aplikasi';
            } else {
                $logoSrc = 'https://3flo.my.id/content-images/top_logo.png'; // Fallback atau default logo
            }




            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Pengingat Acara " . myEvent()->name_event;
            $mail->Body    = $this->linkReminderEmail($guestQrcode)->render();

            $mail->send();
            $mail->SmtpClose();

            Invitation::where('qrcode_invitation', $guestQrcode)->update(['send_reminder_invitation' => 1]);

            $status = "success";
            $message = "Berhasil mengirim email";
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $status = "error";
            $message = "Gagal mengirim ke email";
        }
        return redirect("invite")->with($status, $message);
    }

    public function sendWhatsapp()
    {
        // Asumsi Anda mendapatkan qrcode dari request atau dari invitation
        $guestQrcode = $_GET['guestQrcode']; // Contoh pengambilan qrcode dari GET parameter

        // Ambil data undangan
        $invt = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->where('qrcode_invitation', $guestQrcode)->first();

        if ($invt) {
            // Bangun URL lengkap untuk WhatsApp menggunakan asset() atau url() helper
            // 'asset()' akan menghasilkan URL lengkap ke public_path
            // 'url()' akan menghasilkan URL lengkap ke path yang diberikan
            $fullWhatsAppLink = url($invt->link_invitation); // Ini akan menghasilkan http://localhost/gtc-trisakti/public/invitation/KODE_QR

            // Anda bisa tambahkan teks untuk WhatsApp
            $message = "Halo " . $invt->name_guest . ", ini adalah E-Ticket Anda: " . $fullWhatsAppLink;

            // Redirect ke WhatsApp API
            return redirect("https://wa.me/" . $invt->phone_guest . "?text=" . urlencode($message));
        } else {
            return redirect("invite")->with("error", "Undangan tidak ditemukan.");
        }
    }

    public function index()
    {
        $invitations = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
            ->orderBy('id_invitation', 'DESC')
            ->orderBy('name_guest', 'ASC')
            ->get();

        // Tambahkan ini untuk mengambil data acara
        $event = Event::where('id_event', 1)->first();

        // Tambahkan nama provinsi ke setiap invitation
        foreach ($invitations as $invitation) {
            $invitation->provinsi_nama = IndonesiaHelper::getProvinceNameById($invitation->state_guest);
        }

        return view('invitation.index', compact('invitations', 'event'));
    }

    public function create()
    {
        $guests = Guest::orderBy('name_guest', 'ASC')
            ->orderBy('id_guest', 'DESC')
            ->with('invitation')->get();
        return view('invitation.create', compact('guests'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest'          => 'required',
            'type'           => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $qrcode = $this->generateCode();
        $this->qrcodeGenerator($qrcode);

        Invitation::create([
            "id_guest"                      => $request->guest,
            "qrcode_invitation"             => $qrcode,
            "rfid_number_invitation"       => $request->rfid_number,
            "type_invitation"               => $request->type,
            "information_invitation"        => $request->information,
            "link_invitation"               => '/invitation/' . $qrcode,
            "wa_link_invitation"               => url('/invitation/' . $qrcode), // <--- UBAH BARIS INI
            "image_qrcode_invitation"       => '/img/qrCode/' . $qrcode . ".png",
            "id_event"                      => 1,
        ]);

        return redirect('/invite')->with('success', "Berhasil menambah data");
    }

    public function edit($id)
    {
        $invitation = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')->where('id_invitation', $id)->first();
        return view('invitation.edit', compact('invitation'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type'           => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Invitation::where('id_invitation', $id)->update([
            "type_invitation"               => $request->type,
            "rfid_number_invitation"       => $request->rfid_number,
            "information_invitation"        => $request->information,
        ]);

        return redirect('invite')->with('success', "Berhasil mengedit data");
    }

    public function delete(Request $request)
    {
        if (file_exists(public_path('/img/qrCode/' . $request->qrcode . ".png"))) {
            unlink(public_path('/img/qrCode/' . $request->qrcode . ".png"));
        }
        if (file_exists(public_path('/img/scan/scan-in/' . $request->qrcode . ".jpeg"))) {
            unlink(public_path('/img/scan/scan-in/' . $request->qrcode . ".jpeg"));
        }
        if (file_exists(public_path('/img/scan/scan-out/' . $request->qrcode . ".jpeg"))) {
            unlink(public_path('/img/scan/scan-out/' . $request->qrcode . ".jpeg"));
        }
        Invitation::where('id_invitation', $request->id_invitation)->delete();
        return redirect('invite')->with('success', "Berhasil menghapus data");
    }


    // Guest Register Manual
    public function guestRegister()
    {
        $event = Event::where('id_event', 1)->first(); // Atau query sesuai kebutuhanmu
        return view('register.form', compact('event'));
    }
    public function guestRegisterProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            // 'email'     => [
            //     'required',
            //     'email:rfc,dns',
            //     'regex:/^[a-zA-Z0-9._%+-]+@(mail\.)?std.trisakti\.ac\.id$/'
            // ],

            // 'email' => [
            //     'required',
            //     'email:rfc,dns', // cukup format standar email
            //     'regex:/^[a-zA-Z0-9._%+-]+@std\.trisakti\.ac\.id$/'
            // ],

            'email' => [
                'required',
                'email:rfc,dns'
            ],


            'phone'     => 'required',
            'university'   => 'required',
            'faculty'   => 'required',
            'nim'           => 'required|unique:guest,nim_guest',
            'signature' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            // 'email.regex' => 'Email harus menggunakan domain @unpad.ac.id atau @mail.unpad.ac.id.',
            'email.regex' => 'Email harus menggunakan domain @std.trisakti.ac.id.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'university.required' => 'Universitas wajib diisi.',
            'signature.required' => 'Tanda tangan wajib diisi.',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ✅ Tambahkan deklarasi $event di sini
        $event = Event::where('id_event', 1)->first(); // Ganti dengan ID event yang sesuai jika dinamis


        // Simpan data tamus
        $guest = Guest::create([
            "name_guest"        => $request->name,
            "email_guest"       => $request->email,
            "phone_guest"       => $request->phone,
            "faculty_guest"     => $request->faculty,
            "university_guest"  => $request->university,
            "nim_guest"         => $request->nim,
            "signature_guest"   => $request->signature, // Simpan tanda tangan Base64
            "created_by_guest"  => "register",
        ]);

        // Generate QR Code
        $qrcode = $this->generateCode();
        $this->qrcodeGenerator($qrcode);
        $invitation = Invitation::create([
            "id_guest"                => $guest->id_guest,
            "qrcode_invitation"       => $qrcode,
            "type_invitation"         => "Visitor",
            "link_invitation"         => url('/invitation/' . $qrcode),
            "image_qrcode_invitation" => '/img/qrCode/' . $qrcode . ".png",
            "id_event"                => 1,
        ]);


        // Kirim Email Undangan

        $event = Event::where('id_event', 1)->first();
        $setting = Setting::first(); // Pastikan baris ini ada di sini, sebelum penggunaan $setting

        // Kirim Email Undangan
        $mail = new PHPMailer(true);


        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = env("MAIL_HOST");
            $mail->SMTPAuth   = true;
            $mail->Username   = env("MAIL_USERNAME");
            $mail->Password   = env("MAIL_PASSWORD");
            $mail->SMTPSecure = env("MAIL_ENCRYPTION");
            $mail->Port       = env("MAIL_PORT");

            $mail->setFrom(env("MAIL_FROM_ADDRESS"), myEvent()->name_event);
            $mail->addAddress($guest->email_guest, $guest->name_guest);
            $mail->isHTML(true);
            $mail->AddEmbeddedImage(public_path('img/qrCode/' . $qrcode . '.png'), 'qrcode', 'qrcode');
            $mail->AddEmbeddedImage(public_path('img/app/' . $setting->favicon_app), 'favicon', 'favicon');
            $mail->AddEmbeddedImage(public_path('img/event/' . $event->image_event), 'logo_aplikasi', 'logo_aplikasi');

            // Tambahkan gambar QR Code sebagai embedded image
            $mail->AddEmbeddedImage(public_path('img/qrCode/' . $qrcode . '.png'), 'qrcode', 'qrcode.png');

            // Tambahkan gambar background event sebagai embedded image
            $imageBgEventPath = public_path('img/event/' . $event->image_bg_event);
            if (file_exists($imageBgEventPath) && !empty($event->image_bg_event)) {
                $mail->AddEmbeddedImage($imageBgEventPath, 'banner_event', $event->image_bg_event);
                $bannerSrc = 'cid:banner_event';
            } else {
                $bannerSrc = 'https://3flo.my.id/content-images/banner_eticket_dikdasmen.png'; // Fallback
            }

            // Tambahkan logo aplikasi sebagai embedded image
            $logoAppPath = public_path('img/event/' . $event->image_event);
            if (file_exists($logoAppPath) && !empty($event->image_event)) {
                $mail->AddEmbeddedImage($logoAppPath, 'logo_aplikasi', $event->image_event);
                $logoSrc = 'cid:logo_aplikasi';
            } else {
                $logoSrc = 'https://3flo.my.id/content-images/top_logo.png'; // Fallback atau default logo
            }

            $mail->Subject = myEvent()->type_event . ' ' . myEvent()->name_event;
            $mail->Body    = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <style type="text/css">
        @import url("https://fonts.googleapis.com");
        @import url("https://fonts.gstatic.com");
        @import url("https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@100;200;300;400;500;600;700&family=Roboto:wght@100;300;400;500;700;900&display=swap");

        /*Basics*/
        body {
            margin: 0px !important;
            padding: 0px !important;
            display: block !important;
            min-width: 100% !important;
            width: 100% !important;
            -webkit-text-size-adjust: none;
            font-family: "Roboto, Arial, Helvetica, sans-serif;
 background-color:rgb(255, 255, 255);
        }

        table {
            border-spacing: 0;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table td {
            border-collapse: collapse;
            mso-line-height-rule: exactly;
        }

        td img {
            -ms-interpolation-mode: bicubic;
            width: auto;
            max-width: auto;
            height: auto;
            margin: auto;
            display: block !important;
            border: 0px;
        }

        td p {
            margin: 0;
            padding: 0;
        }

        td div {
            margin: 0;
            padding: 0;
        }

        td a {
            text-decoration: none;
            color: inherit;
        }

        /*Outlook*/
        .ExternalClass {
            width: 100%;
        }

        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: inherit;
        }

        .ReadMsgBody {
            width: 100%;
            background-color: #ffffff;
            box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
        }

        /* iOS freya LINKS */
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        /*Gmail freya links*/
        u+#body a {
            color: inherit;
            text-decoration: none;
            font-size: inherit;
            font-family: inherit;
            font-weight: inherit;
            line-height: inherit;
        }

        /*Buttons fix*/
        .undoreset a,
        .undoreset a:hover {
            text-decoration: none !important;
        }

        .yshortcuts a {
            border-bottom: none !important;
        }

        .ios-footer a {
            color: #aaaaaa !important;
            text-decoration: none;
        }

        /* data-outer-table="800 - 600" */
        .outer-table {
            width: 640px !important;
            max-width: 640px !important;
        }

        /* data-inner-table="780 - 540" */
        .inner-table {
            width: 580px !important;
            max-width: 580px !important;
        }

        /*Responsive-Tablet*/
        @media only screen and (max-width: 799px) and (min-width: 601px) {
            .outer-table.row {
                width: 640px !important;
                max-width: 640px !important;
            }

            .inner-table.row {
                width: 580px !important;
                max-width: 580px !important;
            }
        }

        /*Responsive-Mobile*/
        @media only screen and (max-width: 600px) and (min-width: 320px) {
            table.row {
                width: 100% !important;
                max-width: 100% !important;
            }

            td.row {
                width: 100% !important;
                max-width: 100% !important;
            }

            .img-responsive img {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                margin: auto;
            }

            .center-float {
                float: none !important;
                margin: auto !important;
            }

            .center-text {
                text-align: center !important;
            }

            .container-padding {
                width: 100% !important;
                padding-left: 15px !important;
                padding-right: 15px !important;
            }

            .container-padding10 {
                width: 100% !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .hide-mobile {
                display: none !important;
            }

            .menu-container {
                text-align: center !important;
            }

            .autoheight {
                height: auto !important;
            }

            .m-padding-10 {
                margin: 10px 0 !important;
            }

            .m-padding-15 {
                margin: 15px 0 !important;
            }

            .m-padding-20 {
                margin: 20px 0 !important;
            }

            .m-padding-30 {
                margin: 30px 0 !important;
            }

            .m-padding-40 {
                margin: 40px 0 !important;
            }

            .m-padding-50 {
                margin: 50px 0 !important;
            }

            .m-padding-60 {
                margin: 60px 0 !important;
            }

            .m-padding-top10 {
                margin: 30px 0 0 0 !important;
            }

            .m-padding-top15 {
                margin: 15px 0 0 0 !important;
            }

            .m-padding-top20 {
                margin: 20px 0 0 0 !important;
            }

            .m-padding-top30 {
                margin: 30px 0 0 0 !important;
            }

            .m-padding-top40 {
                margin: 40px 0 0 0 !important;
            }

            .m-padding-top50 {
                margin: 50px 0 0 0 !important;
            }

            .m-padding-top60 {
                margin: 60px 0 0 0 !important;
            }

            .m-height10 {
                font-size: 10px !important;
                line-height: 10px !important;
                height: 10px !important;
            }

            .m-height15 {
                font-size: 15px !important;
                line-height: 15px !important;
                height: 15px !important;
            }

            .m-height20 {
                font-size: 20px !important;
                line-height: 20px !important;
                height: 20px !important;
            }

            .m-height25 {
                font-size: 25px !important;
                line-height: 25px !important;
                height: 25px !important;
            }

            .m-height30 {
                font-size: 30px !important;
                line-height: 30px !important;
                height: 30px !important;
            }

            .radius6 {
                border-radius: 6px !important;
            }

            .fade-white {
                background-color: rgba(255, 255, 255, 0.8) !important;
            }

            .rwd-on-mobile {
                display: inline-block !important;
                padding: 5px !important;
            }

            .center-on-mobile {
                text-align: center !important;
            }

            .rwd-col {
                width: 100% !important;
                max-width: 100% !important;
                display: inline-block !important;
            }

            .type48 {
                font-size: 48px !important;
                line-height: 48px !important;
            }
        }
    </style>
    <style type="text/css" class="export-delete">
        .composer--mobile table.row {
            width: 100% !important;
            max-width: 100% !important;
        }

        .composer--mobile td.row {
            width: 100% !important;
            max-width: 100% !important;
        }

        .composer--mobile .img-responsive img {
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            margin: auto;
        }

        .composer--mobile .center-float {
            float: none !important;
            margin: auto !important;
        }

        .composer--mobile .center-text {
            text-align: center !important;
        }

        .composer--mobile .container-padding {
            width: 100% !important;
            padding-left: 15px !important;
            padding-right: 15px !important;
        }

        .composer--mobile .container-padding10 {
            width: 100% !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .composer--mobile .hide-mobile {
            display: none !important;
        }

        .composer--mobile .menu-container {
            text-align: center !important;
        }

        .composer--mobile .autoheight {
            height: auto !important;
        }

        .composer--mobile .m-padding-10 {
            margin: 10px 0 !important;
        }

        .composer--mobile .m-padding-15 {
            margin: 15px 0 !important;
        }

        .composer--mobile .m-padding-20 {
            margin: 20px 0 !important;
        }

        .composer--mobile .m-padding-30 {
            margin: 30px 0 !important;
        }

        .composer--mobile .m-padding-40 {
            margin: 40px 0 !important;
        }

        .composer--mobile .m-padding-50 {
            margin: 50px 0 !important;
        }

        .composer--mobile .m-padding-60 {
            margin: 60px 0 !important;
        }

        .composer--mobile .m-padding-top10 {
            margin: 30px 0 0 0 !important;
        }

        .composer--mobile .m-padding-top15 {
            margin: 15px 0 0 0 !important;
        }

        .composer--mobile .m-padding-top20 {
            margin: 20px 0 0 0 !important;
        }

        .composer--mobile .m-padding-top30 {
            margin: 30px 0 0 0 !important;
        }

        .composer--mobile .m-padding-top40 {
            margin: 40px 0 0 0 !important;
        }

        .composer--mobile .m-padding-top50 {
            margin: 50px 0 0 0 !important;
        }

        .composer--mobile .m-padding-top60 {
            margin: 60px 0 0 0 !important;
        }

        .composer--mobile .m-height10 {
            font-size: 10px !important;
            line-height: 10px !important;
            height: 10px !important;
        }

        .composer--mobile .m-height15 {
            font-size: 15px !important;
            line-height: 15px !important;
            height: 15px !important;
        }

        .composer--mobile .m-height20 {
            font-srobotoize: 20px !important;
            line-height: 20px !important;
            height: 20px !important;
        }

        .composer--mobile .m-height25 {
            font-size: 25px !important;
            line-height: 25px !important;
            height: 25px !important;
        }

        .composer--mobile .m-height30 {
            font-size: 30px !important;
            line-height: 30px !important;
            height: 30px !important;
        }

        .composer--mobile .radius6 {
            border-radius: 6px !important;
        }

        .composer--mobile .fade-white {
            background-color: rgba(255, 255, 255, 0.8) !important;
        }

        .composer--mobile .rwd-on-mobile {
            display: inline-block !important;
            padding: 5px !important;
        }

        .composer--mobile .center-on-mobile {
            text-align: center !important;
        }

        .composer--mobile .rwd-col {
            width: 100% !important;
            max-width: 100% !important;
            display: inline-block !important;
        }

        .composer--mobile .type48 {
            font-size: 48px !important;
            line-height: 48px !important;
        }
    </style>
</head>

<body data-bgcolor="Body"
    style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; width: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;"
    bgcolor="#FAEEE7">

    <span class="preheader-text" data-preheader-text
        style="color: transparent; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; visibility: hidden; width: 0; display: none; mso-hide: all;"></span>

    <!-- Preheader white space hack -->
    <div style="display: none; max-height: 0px; overflow: hidden;">
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <div data-primary-font=IBM Plex Sans Thai Looped data-secondary-font=Roboto
        style="display:none; font-size:0px; line-height:0px; max-height:0px; max-width:0px; opacity:0; overflow:hidden; visibility:hidden; mso-hide:all;">
    </div>

    <table border="0" align="center" cellpadding="0" cellspacing="0" width="100%" style="width:100%;max-width:100%;">
        <tr><!-- Outer Table -->
            <td align="center" data-bgcolor="Body" bgcolor=" #FAEEE7" data-composer>

                <table data-outer-table border="0" align="center" cellpadding="0" cellspacing="0"
                    class="outer-table row" role="presentation" width="640" style="width:640px;max-width:640px;"
                    data-module="freya-logo">
                    <!-- freya-logo -->
                    <tr>
                        <td align="center">

                            <!-- Content -->
                            <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation"
                                width="100%" style="width:100%;max-width:100%;">
                                <tr data-element="freya-brand-colors" data-label="Brand Colors">
                                    <td align="center">
                                        <!-- Brand Colors -->
                                        <table border="0" align="center" cellpadding="0" cellspacing="0"
                                            role="presentation" width="100%" style="width:100%;max-width:100%;">
                                            <tr>
                                                <td align="center" width="33.33%" height="10" data-bgcolor="1st Color"
                                                    bgcolor="#41852d" style="width:33.33%;"></td>
                                                <td align="center" width="33.33%" height="10" data-bgcolor="2nd Color"
                                                    bgcolor="#b9dd26" style="width:33.33%;"></td>
                                                <td align="center" width="33.33%" height="10" data-bgcolor="3rd Color"
                                                    bgcolor="#ceb732" style="width:33.33%;"></td>
                                            </tr>
                                        </table>
                                        <!-- Brand Colors -->
                                    </td>
                                </tr>
                                <tr>
                                    <td height="40" style="font-size:40px;line-height:40px;" data-height="Spacing top">
                                        &nbsp;</td>
                                </tr>
                                <tr data-element="freya-logo" data-label="Logo">
                                    <td align="center">
                                        <table data-inner-table border="0" align="center" cellpadding="0"
                                            cellspacing="0" class="inner-table row container-padding"
                                            role="presentation" width="580" style="width:580px;max-width:580px;">
                                            <tr>

                                                <td align="center" class="img-responsive">
                                                    <!-- Logo -->
                                                <img style="padding: 3px;width:160px;border:0px;display: inline!important;"
                                                    src="cid:logo_aplikasi" width="160" border="0"
                                                    editable="true" data-icon data-image-edit data-url data-label="Logo" data-image-width
                                                    alt="logo">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td height="10" style="font-size:10px;line-height:10px;"
                                        data-height="Spacing bottom">&nbsp;</td>
                                </tr>
                            </table>
                            <!-- Content -->

                        </td>
                    </tr>
                    <!-- freya-logo -->
                </table>

                <table data-outer-table border="0" align="center" cellpadding="0" cellspacing="0"
                    class="outer-table row container-padding" role="presentation" width="640"
                    style="width:640px;max-width:640px;" data-module="freya-header-16">
                    <!-- freya-header-16 -->
                    <tr>
                        <td align="center" bgcolor="#FFFFFF" data-bgcolor="BgColor" class="container-padding">

                            <table data-inner-table border="0" align="center" cellpadding="0" cellspacing="0"
                                role="presentation" class="inner-table row" width="580"
                                style="width:580px;max-width:580px;">
                                <tr>
                                    <td class="m-height15" height="30" style="font-size:30px;line-height:30px;"
                                        data-height="Spacing top">
                                        &nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <!-- content -->
                                        <table border="0" align="center" cellpadding="0" cellspacing="0"
                                            role="presentation" width="100%" style="width:100%;max-width:100%;">
                                            <tr data-element="freya-header-image" data-label="Header image">
                                                <td align="center" class="img-responsive">
                                                    <img class="auto-width"
                                                        style="display:block;width:100%;max-width:100%;border:0px;"
                                                        data-image-edit data-url data-label="Header image" width="580"
                                                        src="cid:banner_event"
                                                        border="0" editable="true" alt="picture">
                                                </td>
                                            </tr>
                                            <tr data-element="freya-header-image" data-label="Header image">
                                                <td height="20" style="font-size:20px;line-height:20px;"
                                                    data-height="Spacing under image">
                                                    &nbsp;</td>
                                            </tr>


                                            <tr data-element="freya-header-headline" data-label="Header Headline">
                                                <td height="24" style="font-size:24px;line-height:24px;"
                                                    data-height="Spacing under headline">
                                                    &nbsp;</td>
                                            </tr>
                                            <tr data-element="freya-header-message-author" data-label="Message Author">
                                                <td align="center">
                                                    <!-- rwd-col -->
                                                    <table border="0" cellpadding="0" cellspacing="0" align="left"
                                                        role="presentation">
                                                        <tr>
                                                            <td class="rwd-col" align="center">

                                                                <table border="0" align="left" cellpadding="0"
                                                                    cellspacing="0" role="presentation">
                                                                    <tr>
                                                                        <td align="left">
                                                                            <img style="width:60px;border:0px;display: inline!important; border-radius: 50%;"
                                                                                src="cid:favicon"
                                                                                width="60" border="0" editable="true"
                                                                                data-icon data-image-edit data-url
                                                                                data-label="Avatar" alt="Avatar">
                                                                        </td>
                                                                    </tr>
                                                                </table>

                                                            </td>
                                                            <td class="rwd-col" align="center" width="10" height="5"
                                                                style="width:10px;max-width:10px;height:5px;">&nbsp;
                                                            </td>
                                                            <td class="rwd-col" align="center">

                                                                <table border="0" align="left" cellpadding="0"
                                                                    cellspacing="0" role="presentation">
                                                                    <tr>
                                                                        <td data-text-style="Titles" align="left"
                                                                            style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:28px;line-height:38px;font-weight:700;font-style:normal;color:#006931;text-decoration:none;letter-spacing:0px;">
                                                                            <singleline>
                                                                                <div mc:edit data-text-edit>
                                                                                    @aigis.events
                                                                                </div>
                                                                            </singleline>
                                                                        </td>
                                                                    </tr>
                                                                </table>

                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="5" style="font-size:5px;line-height:5px;">&nbsp;
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- rwd-col -->
                                                </td>
                                            </tr>
                                            <tr data-element="freya-header-message-arrow" data-label="Arrow icon">
                                                <td align="left">
                                                    <img style="width:24px;border:0px;display: inline!important;"
                                                        src="https://aigis-moi.id/dont_delete/arrow-chat.png" width="24"
                                                        border="0" editable="true" data-icon data-image-edit data-url
                                                        data-label="Arrow" data-image-width alt="arrow">
                                                </td>
                                            </tr>
                                            <tr data-element="freya-header-message-container"
                                                data-label="Message Container">
                                                <td align="center" bgcolor="#F8F8F8" data-bgcolor="Message BgColor">
                                                    <!-- column -->
                                                    <table border="0" align="center" cellpadding="0" cellspacing="0"
                                                        role="presentation" class="row container-padding" width="93.1%"
                                                        style="width:93.1%;max-width:93.1%;">
                                                        <tr>
                                                            <td height="20" style="font-size:20px;line-height:20px;">
                                                                &nbsp;</td>
                                                        </tr>
                                                        <tr data-element="freya-invitation-message-paragraph"
                                                            data-label="Paragraphs">
                                                            <td data-text-style="Paragraphs" align="left"
                                                                style="font-family:"
                                                                Barlow",Arial,Helvetica,sans-serif;font-size:16px;line-height:26px;font-weight:400;font-style:normal;color:#666666;text-decoration:none;letter-spacing:0px;">
                                                                <singleline>
                                                                    <div mc:edit data-text-edit>
                                                                        Dear ' . $request->salutation . '. <strong>' . $request->name . '</strong>,
                                                                        <br><br>
                                                                        Congratulations! You have registered for ' . myEvent()->name_event . '.
                                                                        <br><br>
                                                                        Please find below the event details:
                                                                        <br>
                                                                        <li>
                                                                            <strong>Event name</strong> : <strong
                                                                                style="color: #006931;">' . mySetting()->name_app . '</strong>
                                                                        </li>
                                                                        <li>
                                                                            <strong>Theme </strong> : <strong
                                                                                style="color: #006931;">' . mySetting()->theme_app . '</strong>
                                                                        </li>
                                                                       <li><strong>Date</strong> : ' . \Carbon\Carbon::parse($event->end_event)->isoFormat('DD MMMM YYYY') . '</li>
                                                                       <li><strong>Time</strong> : ' . \Carbon\Carbon::parse($event->start_event)->isoFormat('HH:mm') . ' - ' . \Carbon\Carbon::parse($event->end_event)->isoFormat('HH:mm') . ' WIB</li>
                                                                        <li>
                                                                            <strong>Location</strong> : ' . myEvent()->place_event . ', ' . myEvent()->location_event . '
                                                                        </li>
                                                                        <br>
                                                                      Here is your information:


                                                                        <li>
                                                                            <strong>Your Name</strong> : ' . $request->name . '
                                                                        </li>

                                                                        <li>
                                                                            <strong>University </strong> : ' . $request->university . '
                                                                        </li>
                                                                        <li>
                                                                            <strong>Faculty </strong> : ' . $request->faculty . '
                                                                        </li>
                                                                        <li>
                                                                            <strong>NIM </strong> : ' . $request->nim . '
                                                                        </li>
                                                                        <li>
                                                                            <strong>Email</strong> : ' . $request->email . '
                                                                        </li>
                                                                        <li>
                                                                            <strong>Phone</strong> : ' . $request->phone . '
                                                                        </li>

                                                                        <br>

                                                                        <div style="text-align: center;">
                                                                            <img style="width:250px;border:0px;display: inline!important; "
                                                                                src="cid:qrcode"
                                                                                width="250" border="0" editable="true"
                                                                                data-icon data-image-edit data-url
                                                                                data-label="Avatar" alt="Avatar">
                                                                            <br>
                                                                            <strong>Kode QR Anda : ' . $qrcode . '</strong>
                                                                        </div>


                                                                        <br>
                                                                        <br>
                                                                        <strong>Notes:</strong> <br>
                                                                        * Save this QR Code and show it to the registration officer sat the event location.
                                                                        <br><br>


                                                                        Thank you for being part of this event. We look forward to welcoming you.
                                                                        <br><br>
                                                                        <strong>Sincerely Greetings,</strong> <br>

                                                                        <strong style="color: #006931;">Event Committee</strong>
                                                                    </div>
                                                                </singleline>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="20" style="font-size:20px;line-height:20px;">
                                                                &nbsp;</td>
                                                        </tr>
                                                    </table>
                                                    <!-- column -->
                                                </td>
                                            </tr>



                                            <tr data-element="freya-header-paragraph" data-label="Header Paragraph">
                                                <td height="20" style="font-size:20px;line-height:20px;"
                                                    data-height="Spacing under paragraph">
                                                    &nbsp;</td>
                                            </tr>

                                        </table>
                                        <!-- content -->
                                    </td>
                                </tr>
                                <tr>
                                    <td class="m-height15" height="30" style="font-size:30px;line-height:30px;"
                                        data-height="Spacing bottom">&nbsp;</td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                </table>



                <table data-outer-table border="0" align="center" cellpadding="0" cellspacing="0"
                    class="outer-table row" role="presentation" width="640" style="width:640px;max-width:640px;"
                    data-module="freya-footer">
                    <!-- freya-footer -->
                    <tr>
                        <td align="center">

                            <table data-inner-table border="0" align="center" cellpadding="0" cellspacing="0"
                                role="presentation" class="inner-table row container-padding" width="580"
                                style="width:580px;max-width:580px;">
                                <tr>
                                    <td height="60" style="font-size:60px;line-height:60px;" data-height="Spacing top">
                                    </td>
                                    &nbsp;
                        </td>
                    </tr>



                    <tr data-element="freya-footer-copyrights-apps" data-label="Copyrights & Apps">
                        <td align="center">
                            <!-- rwd-col -->
                            <table border="0" cellpadding="0" cellspacing="0" align="center" role="presentation"
                                class="container-padding" width="100%" style="width:100%;max-width:100%;">
                                <tr>
                                    <td class="rwd-col" align="center" width="51.72%"
                                        style="width:51.72%;max-width:51.72%;">

                                        <table border="0" align="center" cellpadding="0" cellspacing="0"
                                            role="presentation" align="left" class="center-float">
                                            <tr data-element="freya-footer-paragraph" data-label="Paragraphs">
                                                <td data-text-style="Paragraphs" align="left" class="center-text"
                                                    style="font-family:Roboto, Arial,Helvetica,sans-serif;font-size:12px;line-height:24px;font-weight:400;font-style:normal;color:#000000;text-decoration:none;letter-spacing:0px;">
                                                    <singleline>
                                                        <div mc:edit data-text-edit>
                                                            2025 AIGIS organized by GMSCONSOLIDATE Inc. All Rights Reserved. <br>
                                                        </div>
                                                    </singleline>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>

                                </tr>
                            </table>
                            <!-- rwd-col -->
                        </td>
                    </tr>
                    <tr data-element="freya-footer-copyrights-apps" data-label="Copyrights & Apps">
                        <td height="20" style="font-size:20px;line-height:20px;"
                            data-height="Spacing under copyrights & apps">&nbsp;</td>
                    </tr>
                </table>

                <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" width="100%"
                    style="width:100%;max-width:100%;">
                    <tr data-element="freya-brand-colors" data-label="Brand Colors">
                        <td align="center">
                            <!-- Brand Colors -->
                            <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation"
                                width="100%" style="width:100%;max-width:100%;">
                                <tr>
                                    <td align="center" width="33.33%" height="10" data-bgcolor="1st Color"
                                        bgcolor="#41852d" style="width:33.33%;"></td>
                                    <td align="center" width="33.33%" height="10" data-bgcolor="2nd Color"
                                        bgcolor="#b9dd26" style="width:33.33%;"></td>
                                    <td align="center" width="33.33%" height="10" data-bgcolor="3rd Color"
                                        bgcolor="#ceb732" style="width:33.33%;"></td>
                                </tr>
                            </table>
                            <!-- Brand Colors -->
                        </td>
                    </tr>
                    <tr data-element="freya-footer-tags" data-label="Footer Tags">
                        <td align="center" bgcolor="#FFFFFF" data-bgcolor="Footer BgColor">

                            <table data-inner-table border="0" align="center" cellpadding="0" cellspacing="0"
                                role="presentation" class="inner-table row container-padding" width="580"
                                style="width:580px;max-width:580px;">
                                <tr>
                                    <td height="20" style="font-size:20px;line-height:20px;"
                                        data-height="Spacing under brand colors">&nbsp;</td>
                                </tr>
                                <tr data-element="freya-footer-permission-reminder" data-label="Permission reminder">
                                    <td data-text-style="Paragraphs" align="left" class="center-text"
                                        style="font-family:Roboto, Arial,Helvetica,sans-serif;font-size:12px;line-height:28px;font-weight:400;font-style:normal;color:#333333;text-decoration:none;letter-spacing:0px;">
                                        <singleline>
                                            <div mc:edit data-text-edit>
                                               You received this email when you registered for The 2<sup>nd</sup> AIGIS 2025 event.
                                            </div>
                                        </singleline>
                                    </td>
                                </tr>
                                <tr data-element="freya-footer-tags" data-label="Tags">
                                    <td align="center">
                                        <table border="0" align="left" cellpadding="0" cellspacing="0"
                                            role="presentation" class="center-float">
                                            <tr class="center-on-mobile">
                                                <td data-element="freya-footer-unsubscribe" data-label="Unsubscribe"
                                                    data-text-style="Paragraphs" class="rwd-on-mobile center-text"
                                                    align="center"
                                                    style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;line-height:28px;font-weight:300;font-style:normal;color:#325288;text-decoration:none;letter-spacing:0px;">
                                                    <unsubscribe href="#" data-mergetag="Unsubscribe"
                                                        style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;font-weight:400;line-height:28px;color:#325288;text-decoration:none;">
                                                        Unsubscribe</unsubscribe>
                                                </td>
                                                <td data-element="freya-footer-gap-1" data-label="1st Gap"
                                                    class="rwd-on-mobile center-text" align="center" valign="middle">
                                                    <table border="0" align="center" cellpadding="0" cellspacing="0"
                                                        role="presentation">
                                                        <tr>
                                                            <td width="5"></td>
                                                            <td class="center-text" data-text-style="Paragraphs"
                                                                align="center"
                                                                style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;line-height:28px;font-weight:400;font-style:normal;color:#325288;text-decoration:none;letter-spacing:0px;">
                                                                /</td>
                                                            <td width="5"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td data-element="freya-footer-webversion" data-label="Web version"
                                                    data-text-style="Paragraphs" class="rwd-on-mobile center-text"
                                                    align="center"
                                                    style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;line-height:28px;font-weight:300;font-style:normal;color:#325288;text-decoration:none;letter-spacing:0px;">
                                                    <webversion href="#" data-mergetag="Web version"
                                                        style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;font-weight:400;line-height:28px;color:#325288;text-decoration:none;">
                                                        View on browser</webversion>
                                                </td>
                                                <td data-element="freya-footer-gap-2" data-label="2nd Gap"
                                                    class="rwd-on-mobile center-text" align="center" valign="middle">
                                                    <table border="0" align="center" cellpadding="0" cellspacing="0"
                                                        role="presentation">
                                                        <tr>
                                                            <td width="5"></td>
                                                            <td class="center-text" data-text-style="Paragraphs"
                                                                align="center"
                                                                style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;line-height:28px;font-weight:400;font-style:normal;color:#325288;text-decoration:none;letter-spacing:0px;">
                                                                /</td>
                                                            <td width="5"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td data-element="freya-footer-forward" data-label="Forward"
                                                    data-text-style="Paragraphs" class="rwd-on-mobile center-text"
                                                    align="center"
                                                    style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;line-height:28px;font-weight:300;font-style:normal;color:#325288;text-decoration:none;letter-spacing:0px;">
                                                    <forward href="#" data-mergetag="Forward"
                                                        style="font-family:IBM Plex Sans Thai Looped, Arial,Helvetica,sans-serif;font-size:12px;font-weight:400;line-height:28px;color:#325288;text-decoration:none;">
                                                        Forward</forward>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="30" style="font-size:30px;line-height:30px;"
                                        data-height="Spacing under tags">&nbsp;
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr class="hide-mobile">
            <td height="30" style="font-size:30px;line-height:30px;" data-height="Spacing bottom">&nbsp;
            </td>
        </tr>
        <!-- freya-footer -->
    </table>

    </td>
    </tr><!-- Outer-Table -->
    </table>

</body>

</html>
            
            
                            ';
            $mail->send();
        } catch (Exception $e) {
            return redirect('/invitation/' . $qrcode)->with("register-success", "Tetapi email gagal dikirim.");
        }

        return redirect('/invitation/' . $qrcode)
            ->with("register-success", "Terima kasih telah mendaftar!")
            ->with("register-email", $request->email); // <-- tambahkan ini
    }

    public function inviteExport()
    {
        $type = isset($_GET['type']) && $_GET['type'] != "" ? $_GET['type'] : "";
        $table = isset($_GET['table']) && $_GET['table'] != "" ? $_GET['table'] : "";

        return (new inviteExport)->type($type)->table($table)->download('Daftar Undangan.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
