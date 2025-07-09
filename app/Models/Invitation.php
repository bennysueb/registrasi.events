<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event; // Pastikan ini ada
use App\Models\Guest; // Pastikan ini ada


class Invitation extends Model
{
    use HasFactory;

    protected $table = "invitation";
    protected $fillable = [
        "id_guest",
        "qrcode_invitation",
        "rfid_number_invitation",
        "type_invitation",
        "information_invitation",
        "link_invitation",
        "image_qrcode_invitation",
        "send_email_invitation",
        "send_reminder_invitation",
        "checkin_invitation",
        "checkout_invitation",
    ];

    // Jika Anda tidak menggunakan kolom created_at dan updated_at
    // public $timestamps = false; // Ini perlu di-uncomment jika Anda tidak menggunakan timestamps

    // Relasi ke model Guest (pastikan hanya ada satu definisi)
    public function guest()
    {
        return $this->belongsTo(Guest::class, 'id_guest', 'id_guest');
    }

    // Relasi ke model Event (pastikan hanya ada satu definisi)
    public function event()
    {
        // Asumsi 'id_event' adalah foreign key di tabel 'invitation'
        // dan 'id_event' adalah primary key di tabel 'events'
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    // Accessor untuk link WhatsApp E-Ticket
    public function getWaLinkInvitationAttribute()
    {
        // Mendapatkan URL lengkap dari link_invitation yang tersimpan
        $fullLink = url($this->link_invitation);

        // Membangun pesan WhatsApp
        // Pastikan relasi 'guest' dimuat, misal dengan ->with('guest') saat query Invitation
        $message = "Halo " . $this->guest->name_guest . ", ini adalah E-Ticket Anda: " . $fullLink;

        // Menggunakan urlencode untuk keamanan URL WhatsApp
        return urlencode($message);
    }

    // Accessor untuk link WhatsApp Pengingat
    public function getWaReminderLinkInvitationAttribute()
    {
        // Mengambil data event. Ini akan melakukan query database setiap kali accessor ini dipanggil.
        // Jika Anda memuat relasi 'event' (misal: Invitation::with('event')->get()),
        // Anda bisa menggunakan $this->event->name_event langsung tanpa query ulang.
        $event = Event::where('id_event', 1)->first();

        // Mendapatkan URL lengkap dari link_invitation yang tersimpan
        $fullLink = url($this->link_invitation);

        // Membangun pesan pengingat WhatsApp
        // Gunakan operator null coalescing (??) untuk nilai default jika properti event kosong
        $message = 'Halo ' . $this->guest->name_guest . ',' . "\n\n" .
            'Kami mengingatkan Anda untuk hadir dalam acara berikut:' . "\n\n" .
            '📌 *' . ($event->name_event ?? 'Nama Acara') . "*\n" .
            '🏛️ Tempat: ' . ($event->place_event ?? '-') . "\n" .
            '📍 Lokasi: ' . ($event->location_event ?? '-') . "\n\n" .
            'Silakan cek E-Ticket Anda melalui tautan berikut:' . "\n" .
            $fullLink . "\n\n" . // Tautan lengkap di sini
            'Terima kasih dan sampai jumpa!';

        // Menggunakan urlencode untuk keamanan URL WhatsApp
        return urlencode($message);
    }
}
// Pastikan untuk mengimpor model Event dan Guest di bagian atas file ini