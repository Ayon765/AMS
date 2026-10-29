<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    private const KEYS = ['company_name', 'address', 'phone', 'email', 'website', 'currency', 'invoice_footer'];

    public function edit()
    {
        $s = [];
        foreach (self::KEYS as $k) {
            $s[$k] = Setting::val($k, '');
        }

        return view('settings.company', ['s' => $s]);
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'company_name'   => 'required|string|max:255',
            'address'        => 'nullable|string|max:500',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'website'        => 'nullable|string|max:255',
            'currency'       => 'nullable|string|max:10',
            'invoice_footer' => 'nullable|string|max:500',
        ]);

        foreach ($data as $k => $v) {
            Setting::put($k, $v);
        }

        return back()->with('ok', 'Company profile updated.');
    }
}
