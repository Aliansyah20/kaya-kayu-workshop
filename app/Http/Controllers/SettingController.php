<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'Kaya Kayu Workshop'),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_email' => Setting::get('company_email', ''),
            'dp_percent' => Setting::get('dp_percent', 30),
            'grace_period_days' => Setting::get('grace_period_days', 3),
            'quick_price_per_m2' => Setting::get('quick_price_per_m2', 1000000),
            'invoice_notes' => Setting::get('invoice_notes', 'Terima kasih telah mempercayakan furniture Anda kepada Kaya Kayu Workshop.'),
        ];

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'company_address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email', 'max:150'],
            'dp_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:30'],
            'quick_price_per_m2' => ['required', 'numeric', 'min:0'],
            'invoice_notes' => ['nullable', 'string'],
        ]);

        Setting::set('company_name', $data['company_name'], 'string', 'company');
        Setting::set('company_address', $data['company_address'] ?? '', 'string', 'company');
        Setting::set('company_phone', $data['company_phone'] ?? '', 'string', 'company');
        Setting::set('company_email', $data['company_email'] ?? '', 'string', 'company');
        Setting::set('dp_percent', $data['dp_percent'], 'decimal', 'transaction');
        Setting::set('grace_period_days', $data['grace_period_days'], 'integer', 'transaction');
        Setting::set('quick_price_per_m2', $data['quick_price_per_m2'], 'decimal', 'transaction');
        Setting::set('invoice_notes', $data['invoice_notes'] ?? '', 'string', 'invoice');

        ActivityLogger::log('update', 'setting', null, 'Memperbarui pengaturan sistem.', [], $data);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
