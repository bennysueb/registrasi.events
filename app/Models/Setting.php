<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    // Sesuaikan dengan nama tabel Anda jika bukan 'settings'
    protected $table = 'setting';

    // Tentukan kolom-kolom yang bisa diisi (fillable)
    protected $fillable = [
        'name_app',
        'logo_app',
        'favicon_app',
        'color_bg_app',
        'image_bg_app',
        'image_bg_status',
        // tambahkan kolom lain yang relevan dari tabel settings Anda
    ];
}
