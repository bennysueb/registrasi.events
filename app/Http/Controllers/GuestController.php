<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rule;

class GuestController extends Controller
{
    public function index()
    {
        $guests = Guest::orderBy('name_guest', 'ASC')
            ->get();
        return view('guest.index', compact('guests'));
    }

    public function create()
    {
        return view('guest.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'nim'           => 'required|unique:guest,nim_guest',
            'faculty'       => 'required',
            'university'    => 'required',
            'email'         => 'required|email',
            'phone'         => 'required',
            // 'address'       => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        Guest::create([
            "name_guest"            => $request->name,
            "email_guest"           => $request->email,
            "phone_guest"           => $request->phone,
            "faculty_guest"         => $request->faculty,
            "university_guest"     => $request->university,
            "nim_guest"             => $request->nim,
            "created_by_guest"      => "admin",
        ]);


        return redirect('/guest')->with('success', "Berhasil menambah data");
    }

    public function edit($id)
    {
        $guest = Guest::where('id_guest', $id)->first();
        return view('guest.edit', compact('guest'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'nim'         => [
                'required',
                Rule::unique('guest', 'nim_guest')->ignore($id, 'id_guest')
            ],
            'email'         => 'required|email',
            'phone'         => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        Guest::where('id_guest', $id)->update([
            "name_guest"            => $request->name,
            "email_guest"           => $request->email,
            "phone_guest"           => $request->phone,
            "faculty_guest"         => $request->faculty,
            "university_guest"     => $request->university,
            "nim_guest"             => $request->nim,
            "created_by_guest"      => "admin",
        ]);

        return redirect('/guest')->with('success', "Berhasil mengedit data");
    }


    public function delete(Request $request)
    {
        $invitation = Invitation::where('id_guest', $request->id_guest)->get();
        foreach ($invitation as $key => $value) {
            if (file_exists(public_path('img/qrCode/' . $value->qrcode_invitation . ".png"))) {
                unlink(public_path('img/qrCode/' . $value->qrcode_invitation . ".png"));
            }
            if (file_exists(public_path('img/scan/scan-in/' . $value->qrcode_invitation . ".jpeg"))) {
                unlink(public_path('img/scan/scan-in/' . $value->qrcode_invitation . ".jpeg"));
            }
            if (file_exists(public_path('img/scan/scan-out/' . $value->qrcode_invitation . ".jpeg"))) {
                unlink(public_path('img/scan/scan-out/' . $value->qrcode_invitation . ".jpeg"));
            }
        }
        Guest::where('id_guest', $request->id_guest)->delete();
        return redirect('guest')->with('success', "Berhasil menghapus data");
    }
}
