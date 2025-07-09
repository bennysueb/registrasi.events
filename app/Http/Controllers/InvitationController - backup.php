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
    if ($invt) {
      return view('link-guest.index', compact('invt', 'event'));
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
    return view('link-guest.sendMail', compact('invt', 'event'));
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
    $event = Event::where('id_event', 1)->first();
    $mail = new PHPMailer(true);
    try {
      $guestQrcode    = $_GET['guestQrcode'];
      $guestName      = $_GET['guestName'];
      $guestMail      = $_GET['guestMail'];
      if ($event->image_event != "") :
        $img = 'img/event/' . $event->image_event;
      else :
        $img = 'asset/front/default.png';
      endif;
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

      $mail->setFrom(env("MAIL_FROM_ADDRESS"), myEvent()->name_event);
      $mail->addAddress($guestMail, $guestName);

      $mail->AddEmbeddedImage(public_path('asset/tmp/bg.png'), 'bg', "bg");
      $mail->AddEmbeddedImage(public_path('img/qrCode/' . $guestQrcode . '.png'), 'qrcode', 'qrcode');
      $mail->AddEmbeddedImage(public_path($img), 'logo');

      //Content
      $mail->isHTML(true);                                  //Set email format to HTML
      $mail->Subject = myEvent()->type_event;
      $mail->Body    = $this->linkGuestEmail($guestQrcode)->render();

      $mail->send();
      $mail->SmtpClose();

      Invitation::where('qrcode_invitation', $guestQrcode)->update(['send_email_invitation' => 1]);

      $status = "success";
      $message = "Berhasil mengirim email";
    } catch (Exception $e) {
      // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      $status = "error";
      $message = "Gagal mengirim ke email";
    }
    return redirect("invite")->with($status, $message);
  }

  public function sendWhatsapp() {}

  public function index()
  {
    $invitations = Invitation::join('guest', 'guest.id_guest', '=', 'invitation.id_guest')
      ->orderBy('id_invitation', 'DESC')
      ->orderBy('name_guest', 'ASC')
      ->get();
    return view('invitation.index', compact('invitations'));
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
    return view('register.form');
  }

  public function guestRegisterProcess(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'salutation'  => 'required',
      'name'  => 'required',
      'email' => 'required|email',
      'phone' => 'required',
      'type_institution' => 'required',
      'institution' => 'required',
      'occupation' => 'required',
      'country' => 'required',
      'state' => 'required',
      'state' => 'required',
      'city' => 'required',

      'signature' => 'required', // Validasi tanda tangan
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    // Simpan data tamus
    $guest = Guest::create([
      "salutation_guest"        => $request->salutation,
      "name_guest"        => $request->name,
      "email_guest"       => $request->email,
      "phone_guest"       => $request->phone,
      "type_institution_guest"         => $request->type_institution,
      "media_type_guest"        => $request->media_type,
      "institution_guest"         => $request->institution,
      "occupation_guest"         => $request->occupation,
      "other_institution_guest"     => $request->other_institution,
      "day_1_guest"     => $request->day_1,
      "day_2_guest"     => $request->day_2,
      "day_3_guest"     => $request->day_3,
      "opening_ceremony_guest"     => $request->opening_ceremony,
      "exhibition_guest"     => $request->exhibition,
      "green_talks_guest"     => $request->green_talks,
      "green_concert_guest"     => $request->green_concert,
      "how_guest"     => $request->how,
      "country_guest"     => $request->country,
      "state_guest"     => $request->state,
      "city_guest"     => $request->city,
      "signature_guest"   => $request->signature, // Simpan tanda tangan Base64
      "created_by_guest"  => "register",
    ]);

    // Generate QR Code
    $qrcode = $this->generateCode();
    $this->qrcodeGenerator($qrcode);
    $invitation = Invitation::create([
      "id_guest"                => $guest->id_guest,
      "qrcode_invitation"       => $qrcode,
      "type_invitation"         => "Regular",
      "link_invitation"         => url('/invitation/' . $qrcode),
      "image_qrcode_invitation" => '/img/qrCode/' . $qrcode . ".png",
      "id_event"                => 1,
    ]);

    // Kirim Email Undangan
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host       = env("MAIL_HOST");
      $mail->SMTPAuth   = true;
      $mail->Username   = env("MAIL_USERNAME");
      $mail->Password   = env("MAIL_PASSWORD");
      $mail->SMTPSecure = env("MAIL_ENCRYPTION");
      $mail->Port       = env("MAIL_PORT");

      $mail->setFrom(env("MAIL_FROM_ADDRESS"), "AIGIS");
      $mail->addAddress($guest->email_guest, $guest->name_guest);
      $mail->isHTML(true);
      $mail->AddEmbeddedImage(public_path('img/qrCode/' . $qrcode . '.png'), 'qrcode', 'qrcode');
      $mail->Subject = "E-Ticket " . myEvent()->name_event;
      $mail->Body    = '
            <!DOCTYPE html
              PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">

            <head>
              <style type="text/css">
                @import url("https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap");
                @import url("https://fonts.googleapis.com/css2?family=Gantari:ital,wght@0,100..900;1,100..900&display=swap");


                html {
                  width: 100%;
                }

                p {
                  margin: 0 !important;
                }

                body {
                  -webkit-text-size-adjust: none;
                  -ms-text-size-adjust: none;
                  margin: 0;
                  padding: 0;
                  font-family: sans-serif !important;
                  background-color: #FFFFFF;
                }

                table {
                  border-spacing: 0;
                  table-layout: auto;
                  margin: 0 auto;
                }

                img {
                  display: block !important;
                  overflow: hidden !important;
                }

                a {
                  text-decoration: none;
                  color: unset;
                }

                .ReadMsgBody {
                  width: 100%;
                  background-color: #FFFFFF;
                }

                .ExternalClass {
                  width: 100%;
                  background-color: #FFFFFF;
                }

                .ExternalClass,
                .ExternalClass p,
                .ExternalClass span,
                .ExternalClass font,
                .ExternalClass td,
                .ExternalClass div {
                  line-height: 100%;
                }

                .yshortcuts a {
                  border-bottom: none !important;
                }

                .pad {
                  width: 92%;
                }

                @media only screen and (max-width: 673px) {
                  .res-pad {
                    width: 92%;
                    max-width: 92%;
                  }

                  .res-full {
                    width: 100%;
                    max-width: 100%;
                  }

                  .res-text-left {
                    text-align: left !important;
                  }

                  .res-text-right {
                    text-align: right !important;
                  }

                  .res-text-center {
                    text-align: center !important;
                  }

                  .res-float-left {
                    float: left !important;
                  }

                  .res-float-right {
                    float: right !important;
                  }

                  .res-float-unset {
                    float: unset !important;
                  }

                  .mc-fluid-img {
                    max-width: 100% !important;
                  }
                }

                @media only screen and (max-width: 770px) {
                  .res-rem-radius {
                    border-radius: 0 !important;
                  }

                  .margin-full {
                    width: 100%;
                    max-width: 100%;
                  }

                  .margin-pad {
                    width: 92%;
                    max-width: 92%;
                    max-width: 600px;
                  }
                }
              </style>
            </head>

            <body>
              <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#242B3B" width="100%">
                <tbody>
                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#282F41"
                        class="margin-full res-rem-radius" width="750" background="https://aigis-moi.id/dont_delete/bg-01.png"
                        style="
                            background-size: cover;
                            background-position: center center;
                            border-radius: 0px 0px 0px 0px;
                            margin-top: -1px;
                          ">
                        <tbody>
                          <tr>
                            <td>
                              <table border="0" cellpadding="0" cellspacing="0" align="center" class="margin-pad" width="600">
                                <tbody>
                                  <tr>
                                    <td height="52" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 0; padding-bottom: 14px">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr>
                                                    <td width="420" valign="top" style="vertical-align: top">
                                                      <table border="0" cellpadding="0" cellspacing="0" align="center"
                                                        class="res-full">
                                                        <tbody>
                                                          <tr>
                                                            <td>
                                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                                <tbody>
                                                                  <tr>
                                                                    <td>
                                                                      <img alt="Image" width="1080"
                                                                        src="https://aigis-moi.id/dont_delete/top_logo.png" style="
                                                                            font-size: 0;
                                                                            line-height: 0;
                                                                            width: 100%;
                                                                            display: block;
                                                                            max-width: 1080px;
                                                                            border-radius: 4px;
                                                                            border: 0;
                                                                          " />
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td height="25" style="font-size: 0; line-height: 0">
                                                                      &nbsp;
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td class="res-text-center" style="
                                                                          font-family: Gantari, sans-serif;
                                                                          font-weight: 600;
                                                                          font-size: 28px;
                                                                          line-height: 39px;
                                                                          letter-spacing: 0.4px;
                                                                          text-align: center;
                                                                          color: #fff7fc;
                                                                          word-break: break-word;
                                                                          padding-top: 3px;
                                                                          padding-bottom: 0;
                                                                        ">
                                                                      ' . myEvent()->name_event . '
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td height="25" style="font-size: 0; line-height: 0">
                                                                      &nbsp;
                                                                    </td>
                                                                  </tr>
                                                                  <tr>
                                                                    <td>
                                                                      <img alt="Image" width="1080"
                                                                        src="https://aigis-moi.id/dont_delete/agis_2.png" style="
                                                                            font-size: 0;
                                                                            line-height: 0;
                                                                            width: 100%;
                                                                            display: block;
                                                                            max-width: 1080px;
                                                                            border-radius: 4px;
                                                                            border: 0;
                                                                          " />
                                                                    </td>
                                                                  </tr>
                                                                </tbody>
                                                              </table>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>

                                  <tr>
                                    <td height="10" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>

                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>

              <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#242B3B" width="100%">
                <tbody>
                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#2C3347"
                        class="margin-full res-rem-radius" width="750" style="border-radius: 0px 0px 0px 0px; margin-top: -1px">
                        <tbody>
                          <tr>
                            <td>
                              <table border="0" cellpadding="0" cellspacing="0" align="center" class="margin-pad" width="600">
                                <tbody>
                                  <tr>
                                    <td height="60" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 15px;
                                          line-height: 22px;
                                          letter-spacing: 1px;
                                          text-align: center;
                                          color: #BBD131;
                                          word-break: break-word;
                                          font-weight: bold;
                                          padding-top: 0;
                                          padding-bottom: 3px;
                                        ">
                                      INFORMASI DATA
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 22px;
                                          line-height: 30px;
                                          letter-spacing: 0px;
                                          text-align: center;
                                          color: #fff7fc;
                                          word-break: break-word;
                                        ">
                                     KEHADIRAN PESERTA
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 11px; padding-bottom: 14px">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr>
                                                    <td height="0" style="
                                                          border-bottom: 4px solid
                                                            #BBD131;
                                                          border-radius: 3px;
                                                        " width="60"></td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 5px; padding-bottom: 5px">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
                                        <tbody>
                                          <tr>
                                            <td valign="top">
                                              <table border="0" cellpadding="0" cellspacing="0" width="236" align="left"
                                                class="res-full">
                                                <tbody>
                                                  <tr>
                                                    <td style="
                                                          padding-top: 0;
                                                          padding-bottom: 0;
                                                        ">
                                                      
                                                          <img alt="Image" width="236" src="cid:qrcode" style="
                                                            font-size: 0;
                                                            line-height: 0;
                                                            width: 100%;
                                                            display: block;
                                                            border-radius: 4px;
                                                            border: 0;
                                                          " />
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 18px;
                                                          line-height: 25px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                          padding-top: 8px;
                                                          padding-bottom: 0;
                                                        ">
                                                      YOUR QR CODE : ' . $qrcode . '
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 16px;
                                                          line-height: 22px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                          padding-top: 1px;
                                                          padding-bottom: 0;
                                                        ">
                                                      Tunjukkan kode QR ini kepada staf di meja registrasi
                                                    </td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                              <!-- [if (gte mso 9)|(IE)]></td><td><![endif] -->
                                              <table border="0" cellpadding="0" cellspacing="0" width="1" align="left"
                                                class="res-full">
                                                <tbody>
                                                  <tr>
                                                    <td height="12" style="font-size: 0; line-height: 0">
                                                      &nbsp;
                                                    </td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                              <!-- [if (gte mso 9)|(IE)]></td><td valign="top"><![endif] -->
                                              <table border="0" cellpadding="0" cellspacing="0" width="352" align="right"
                                                class="res-full">
                                                <tbody>
                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 12px;
                                                          line-height: 25px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                          padding-top: 0;
                                                          padding-bottom: 0;
                                                        ">
                                                      Kategori Undangan :
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 16px;
                                                          line-height: 22px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #CEB732;
                                                          word-break: break-word;
                                                          padding-top: 1px;
                                                          padding-bottom: 0;
                                                          text-transform: uppercase;
                                                        ">
                                                        ' . ($request->category === "invited_expert" ? "Invited Expert" : $request->category) . '
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td height="12" style="font-size: 0; line-height: 0">
                                                      &nbsp;
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 12px;
                                                          line-height: 25px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                          padding-top: 0;
                                                          padding-bottom: 0;
                                                        ">
                                                      Nama Lengkap :
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 16px;
                                                          line-height: 22px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #CEB732;
                                                          word-break: break-word;
                                                          padding-top: 1px;
                                                          padding-bottom: 0;
                                                        ">
                                                    ' . $request->name . '
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td height="12" style="font-size: 0; line-height: 0">
                                                      &nbsp;
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 12px;
                                                          line-height: 25px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                          padding-top: 0;
                                                          padding-bottom: 0;
                                                        ">
                                                      Instansi :
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td class="res-text-left" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 16px;
                                                          line-height: 22px;
                                                          letter-spacing: 0px;
                                                          text-align: left;
                                                          color: #CEB732;
                                                          word-break: break-word;
                                                          word-wrap: break-word;
                                                          padding-top: 1px;
                                                          padding-bottom: 0;
                                                        ">
                                                        ' . ($request->institution === "other" ? $request->other_institution : $request->institution) . '
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td height="28" style="font-size: 0; line-height: 0">
                                                      &nbsp;
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td>
                                                      <table border="0" cellpadding="0" cellspacing="0" align="center"
                                                        class="undefined" style="
                                                            overflow: hidden;
                                                            border-radius: 4px;
                                                            border: 2px solid #fff7fc;
                                                          ">
                                                        <tbody>
                                                          <tr>
                                                            <td style="
                                                                  text-align: center;
                                                                  padding: 8px 16px;
                                                                  line-height: 22px;
                                                                ">
                                                              <a href="' . url('/invitation/' . $qrcode) . '" style="
                                                                    font-family: Roboto, Arial;
                                                                    font-size: 16px;
                                                                    line-height: 22px;
                                                                    letter-spacing: 0px;
                                                                    color: #fff7fc;
                                                                    word-break: break-all;
                                                                    text-decoration: none;
                                                                    padding: 8px 0;
                                                                  ">LIHAT DETAIL</a>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>
                                                  </tr>



                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td height="60" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>

              <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#242B3B" width="100%">
                <tbody>
                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#282F41"
                        class="margin-full res-rem-radius" width="750" style="border-radius: 0px 0px 0px 0px; margin-top: -1px">
                        <tbody>
                          <tr>
                            <td>
                              <table border="0" cellpadding="0" cellspacing="0" align="center" class="margin-pad" width="600">
                                <tbody>
                                  <tr>
                                    <td height="67" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 20px;
                                          line-height: 24px;
                                          letter-spacing: 0.4px;
                                          text-align: center;
                                          color: #fff7fc;
                                          word-break: break-word;
                                          padding-top: 0;
                                          padding-bottom: 0;
                                        ">
                                      <span style="font-weight: normal;">Catat Tanggal dan Tempat</span>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td height="35" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 0; padding-bottom: 0">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr style="vertical-align: top">
                                                    <td align="center" style="
                                                          padding-top: 0;
                                                          padding-left: 0;
                                                          padding-right: 10px;
                                                        ">
                                                      <img alt="Icon" width="21" src="https://aigis-moi.id/dont_delete/calender.png"
                                                        style="
                                                            border: 0;
                                                            font-size: 0;
                                                            line-height: 0;
                                                            max-width: 21px;
                                                            display: block;
                                                          " />
                                                    </td>
                                                    <td class="res-text-center" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 22px;
                                                          font-weight: bold;
                                                          line-height: 26px;
                                                          letter-spacing: 0px;
                                                          text-align: center;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                        ">
                                                      Selasa, 29 APRIL 2025
                                                    </td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 10px; padding-bottom: 0">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr style="vertical-align: top">
                                                    <td align="center" style="
                                                          padding-top: 0;
                                                          padding-left: 0;
                                                          padding-right: 10px;
                                                        ">
                                                      <img alt="Icon" width="21" src="https://aigis-moi.id/dont_delete/location.png"
                                                        style="
                                                            border: 0;
                                                            font-size: 0;
                                                            line-height: 0;
                                                            max-width: 21px;
                                                            display: block;
                                                          " />
                                                    </td>
                                                    <td class="text-center" style="
                                                          font-family: Roboto, Arial;
                                                          font-size: 22px;
                                                          font-weight: bold;
                                                          line-height: 26px;
                                                          letter-spacing: 0px;
                                                          text-align: center;
                                                          color: #fff7fc;
                                                          word-break: break-word;
                                                        ">
                                                      ' . myEvent()->place_event . '
                                                    </td>
                                                  </tr>

                                                  <tr>
                                                    <td>
                                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="undefined"
                                                        style="
                                                            overflow: hidden;
                                                            border-radius: 4px;
                                                            border: 2px solid #fff7fc;
                                                            margin-top: 20px;
                                                          ">
                                                        <tbody>
                                                          <tr>
                                                            <td style="
                                                                  text-align: center;
                                                                  padding: 8px 16px;
                                                                  line-height: 22px;
                                                                ">
                                                              <a href="https://maps.app.goo.gl/BncFU11uVixthGFS6" target="_blank" style="
                                                                    font-family: Roboto, Arial;
                                                                    font-size: 16px;
                                                                    line-height: 22px;
                                                                    letter-spacing: 0px;
                                                                    color: #fff7fc;
                                                                    word-break: break-all;
                                                                    text-decoration: none;
                                                                    padding: 8px 0;
                                                                  ">LIHAT MAPS</a>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>
                                                  </tr>

                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td height="68" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>

              <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#242B3B" width="100%">
                <tbody>
                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#2C3347"
                        class="margin-full res-rem-radius" width="750" style="border-radius: 0px 0px 0px 0px; margin-top: -1px">
                        <tbody>
                          <tr>
                            <td>
                              <table border="0" cellpadding="0" cellspacing="0" align="center" class="margin-pad" width="600">
                                <tbody>
                                  <tr>
                                    <td height="64" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 15px;
                                          line-height: 22px;
                                          letter-spacing: 1px;
                                          text-align: center;
                                          color: #BBD131;
                                          word-break: break-word;
                                          font-weight: bold;
                                          padding-top: 0;
                                          padding-bottom: 3px;
                                        ">
                                      JOIN US
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 22px;
                                          line-height: 30px;
                                          letter-spacing: 0px;
                                          text-align: center;
                                          color: #fff7fc;
                                          word-break: break-word;
                                        ">
                                      THE 2<sup>nd</sup> AIGIS 2025
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 11px; padding-bottom: 16px">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr>
                                                    <td height="0" style="
                                                          border-bottom: 4px solid
                                                        #BBD131
                                                          border-radius: 3px;
                                                        " width="60"></td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                      <img alt="Image" width="488" src="https://aigis-moi.id/dont_delete/join.jpg" style="
                                        font-size: 0;
                                        line-height: 0;
                                        width: 100%;
                                        display: block;
                                        border-radius: 4px;
                                        border: 0;
                                      " />
                                    </td>
                                  </tr>

                                  <tr>
                                    <td style="padding-top: 45px; padding-bottom: 0">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center" class="undefined"
                                                style="
                                                    overflow: hidden;
                                                    border-radius: 4px;
                                                    border: 2px solid #fff7fc;
                                                  ">
                                                <tbody>
                                                  <tr>
                                                    <td style="
                                                          text-align: center;
                                                          padding: 8px 16px;
                                                          line-height: 22px;
                                                        ">
                                                      <a href="https://www.zlinks.id/aigis" target="_blank" style="
                                                            font-family: Roboto, Arial;
                                                            font-size: 16px;
                                                            line-height: 22px;
                                                            letter-spacing: 0px;
                                                            color: #fff7fc;
                                                            word-break: break-all;
                                                            text-decoration: none;
                                                            padding: 8px 0;
                                                          ">JOIN NOW</a>
                                                    </td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td height="70" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>

              <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#242B3B" width="100%">
                <tbody>
                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#282F41"
                        class="margin-full res-rem-radius" width="750" style="border-radius: 0px 0px 0px 0px; margin-top: -1px">
                        <tbody>
                          <tr>
                            <td>
                              <table border="0" cellpadding="0" cellspacing="0" align="center" class="margin-pad" width="600">
                                <tbody>
                                  <tr>
                                    <td height="50" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 16px;
                                          line-height: 22px;
                                          letter-spacing: 0px;
                                          text-align: center;
                                          color: #fff7fc;
                                          word-break: break-word;
                                          padding-top: 15px;
                                          padding-bottom: 0;
                                        ">
                                      Organized by
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 5px; padding-bottom: 5px">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr>
                                                    <td width="256" valign="top" style="vertical-align: top">
                                                      <table border="0" cellpadding="0" cellspacing="0" align="center"
                                                        class="res-full">
                                                        <tbody>
                                                          <tr>
                                                            <td>
                                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                                <tbody>
                                                                  <tr>
                                                                    <td>
                                                                      <img alt="Image" width="1256"
                                                                        src="https://aigis-moi.id/dont_delete/logo_gmsconsolidate_white.png"
                                                                        style="
                                                                            font-size: 0;
                                                                            line-height: 0;
                                                                            width: 100%;
                                                                            display: block;
                                                                            max-width: 1256px;
                                                                            border-radius: 4px;
                                                                            border: 0;
                                                                          " />
                                                                    </td>
                                                                  </tr>
                                                                </tbody>
                                                              </table>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="res-text-center" style="
                                          font-family: Roboto, Arial;
                                          font-size: 16px;
                                          line-height: 22px;
                                          letter-spacing: 0px;
                                          text-align: center;
                                          color: #fff7fc;
                                          word-break: break-word;
                                          padding-top: 15px;
                                          padding-bottom: 0;
                                        ">
                                      All Rights Reserved © 2025 AIGIS
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 11px; padding-bottom: 0">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr>
                                                    <td height="0" style="
                                                          border-bottom: 4px solid
                                                            rgb(71, 79, 122);
                                                          border-radius: 3px;
                                                        " width="60"></td>
                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>


                                  <tr>
                                    <td class="res-text-center" style="
                                          text-align: center;
                                          padding-top: 6px;
                                          padding-bottom: 0;
                                        ">
                                      <a style="
                                            font-family: Roboto, Arial;
                                            font-size: 16px;
                                            line-height: 22px;
                                            letter-spacing: 0px;
                                            color: #fff7fc;
                                            word-break: break-word;
                                            text-decoration: none;
                                          " href="">Follow me</a>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="padding-top: 10px; padding-bottom: 0">
                                      <table border="0" cellpadding="0" cellspacing="0" align="center" class="res-full">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                <tbody>
                                                  <tr>
                                                    <td>
                                                      <table>
                                                        <tbody>
                                                          <tr>
                                                            <td align="center">
                                                              <a href="https://www.instagram.com/aigis.event" target="_blank">
                                                                <img alt="Icon" width="40"
                                                                  src="https://aigis-moi.id/dont_delete/icon_ig.png" style="
                                                                    border: 0;
                                                                    font-size: 0;
                                                                    line-height: 0;
                                                                    max-width: 40px;
                                                                    display: block;
                                                                  " />
                                                              </a>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>
                                                    <td style="
                                                          width: 0;
                                                          font-size: 0;
                                                          line-height: 0;
                                                          padding-left: 7px;
                                                        ">
                                                      &nbsp;
                                                    </td>

                                                    <td>
                                                      <table>
                                                        <tbody>
                                                          <tr>
                                                            <td align="center">
                                                              <a href="https://www.youtube.com/@aigis-moi" target="_blank">
                                                                <img alt="Icon" width="40"
                                                                  src="https://aigis-moi.id/dont_delete/icon_yt.png" style="
                                                                    border: 0;
                                                                    font-size: 0;
                                                                    line-height: 0;
                                                                    max-width: 40px;
                                                                    display: block;
                                                                  " />
                                                              </a>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>

                                                    <td style="
                                                          width: 0;
                                                          font-size: 0;
                                                          line-height: 0;
                                                          padding-left: 7px;
                                                        ">
                                                      &nbsp;
                                                    </td>

                                                    <td>
                                                      <table>
                                                        <tbody>
                                                          <tr>
                                                            <td align="center">
                                                              <a href="https://www.aigis-moi.id" target="_blank">
                                                                <img alt="Icon" width="40"
                                                                  src="https://aigis-moi.id/dont_delete/icon_web.png" style="
                                                                    border: 0;
                                                                    font-size: 0;
                                                                    line-height: 0;
                                                                    max-width: 40px;
                                                                    display: block;
                                                                  " />
                                                              </a>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>

                                                    <td style="
                                                          width: 0;
                                                          font-size: 0;
                                                          line-height: 0;
                                                          padding-left: 7px;
                                                        ">
                                                      &nbsp;
                                                    </td>

                                                    <td>
                                                      <table>
                                                        <tbody>
                                                          <tr>
                                                            <td align="center">
                                                              <a href="https://www.zlinks.id/aigis" target="_blank">
                                                                <img alt="Icon" width="40"
                                                                  src="https://aigis-moi.id/dont_delete/icon_zl.png" style="
                                                                    border: 0;
                                                                    font-size: 0;
                                                                    line-height: 0;
                                                                    max-width: 40px;
                                                                    display: block;
                                                                  " />
                                                              </a>
                                                            </td>
                                                          </tr>
                                                        </tbody>
                                                      </table>
                                                    </td>

                                                  </tr>
                                                </tbody>
                                              </table>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td height="74" style="font-size: 0; line-height: 0">
                                      &nbsp;
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </body>

            </html>
            
            
                            ';
      $mail->send();
    } catch (Exception $e) {
      return redirect('/invitation/' . $qrcode)->with("register-success", "Tetapi email gagal dikirim.");
    }

    return redirect('/invitation/' . $qrcode)->with("register-success", "Thank you, your registration has been successful. E-tickets will be sent to your email $request->email Please check your inbox / spam.");
  }

  public function inviteExport()
  {
    $type = isset($_GET['type']) && $_GET['type'] != "" ? $_GET['type'] : "";
    $table = isset($_GET['table']) && $_GET['table'] != "" ? $_GET['table'] : "";

    return (new inviteExport)->type($type)->table($table)->download('Daftar Undangan.xlsx', \Maatwebsite\Excel\Excel::XLSX);
  }
}
