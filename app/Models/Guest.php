<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $table = "guest";
    protected $primaryKey = "id_guest";
    protected $fillable = [
        "name_guest",
        "email_guest",
        "phone_guest",
        "faculty_guest",
        "university_guest", // Tambahkan ini
        "nim_guest", // Tambahkan ini
        "signature_guest", // Tambahkan ini
    ];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class, 'id_guest', 'id_guest');
    }
}
