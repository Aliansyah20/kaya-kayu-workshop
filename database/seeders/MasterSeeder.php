<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ComplexityLevel;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\WoodGrade;
use App\Models\WoodType;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Meja', 'type' => 'product', 'description' => 'Meja makan, kerja, kopi, dan sejenisnya.'],
            ['name' => 'Kursi', 'type' => 'product', 'description' => 'Kursi makan, kerja, dan santai.'],
            ['name' => 'Lemari', 'type' => 'product', 'description' => 'Lemari pakaian, buku, dan penyimpanan.'],
            ['name' => 'Tempat Tidur', 'type' => 'product', 'description' => 'Ranjang dan headboard.'],
            ['name' => 'Custom', 'type' => 'product', 'description' => 'Pesanan khusus di luar katalog utama.'],
            ['name' => 'Kayu Solid', 'type' => 'material', 'description' => 'Papan, balok, dan material kayu utama.'],
            ['name' => 'Finishing', 'type' => 'material', 'description' => 'Cat, pelitur, dan bahan finishing.'],
            ['name' => 'Hardware', 'type' => 'material', 'description' => 'Sekrup, engsel, handle, dan logam lainnya.'],
            ['name' => 'Consumable', 'type' => 'material', 'description' => 'Lem, amplas, dan bahan habis pakai.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['type' => $category['type'], 'name' => $category['name']],
                ['description' => $category['description'], 'is_active' => true]
            );
        }

        $units = [
            ['code' => 'pcs', 'name' => 'Pieces', 'type' => 'count'],
            ['code' => 'm2', 'name' => 'Meter Persegi', 'type' => 'area'],
            ['code' => 'm3', 'name' => 'Meter Kubik', 'type' => 'volume'],
            ['code' => 'kg', 'name' => 'Kilogram', 'type' => 'weight'],
            ['code' => 'L', 'name' => 'Liter', 'type' => 'volume'],
            ['code' => 'lembar', 'name' => 'Lembar', 'type' => 'count'],
            ['code' => 'batang', 'name' => 'Batang', 'type' => 'count'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['code' => $unit['code']], $unit + ['is_active' => true]);
        }

        $woodTypes = [
            ['code' => 'JATI', 'name' => 'Jati', 'price_factor' => 1.35, 'description' => 'Kuat, awet, dan bernilai tinggi.'],
            ['code' => 'MAHONI', 'name' => 'Mahoni', 'price_factor' => 1.10, 'description' => 'Serat halus dan mudah dibentuk.'],
            ['code' => 'SONO', 'name' => 'Sono', 'price_factor' => 0.95, 'description' => 'Pilihan ekonomis untuk produksi cepat.'],
            ['code' => 'MERBAU', 'name' => 'Merbau', 'price_factor' => 1.20, 'description' => 'Keras dan tahan lembap.'],
        ];

        foreach ($woodTypes as $woodType) {
            WoodType::updateOrCreate(['code' => $woodType['code']], $woodType + ['is_active' => true]);
        }

        $woodGrades = [
            ['code' => 'PREM', 'name' => 'Premium', 'price_factor' => 1.20, 'description' => 'Bebas mata kayu besar, finishing rapi.'],
            ['code' => 'STD', 'name' => 'Standar', 'price_factor' => 1.00, 'description' => 'Kualitas produksi normal.'],
            ['code' => 'EKO', 'name' => 'Ekonomis', 'price_factor' => 0.85, 'description' => 'Ada cacat alami yang masih bisa ditutup finishing.'],
        ];

        foreach ($woodGrades as $woodGrade) {
            WoodGrade::updateOrCreate(['code' => $woodGrade['code']], $woodGrade + ['is_active' => true]);
        }

        $complexities = [
            ['code' => 'SIMPLE', 'name' => 'Sederhana', 'factor' => 0.90, 'description' => 'Bentuk dasar tanpa detail ukir.'],
            ['code' => 'SEDANG', 'name' => 'Sedang', 'factor' => 1.00, 'description' => 'Detail standar Kaya Kayu.'],
            ['code' => 'RUMPIT', 'name' => 'Rumit', 'factor' => 1.25, 'description' => 'Banyak sambungan dan detail profil.'],
            ['code' => 'SANGAT', 'name' => 'Sangat Rumit', 'factor' => 1.50, 'description' => 'Ukiran, lengkung, dan pekerjaan khusus.'],
        ];

        foreach ($complexities as $complexity) {
            ComplexityLevel::updateOrCreate(['code' => $complexity['code']], $complexity + ['is_active' => true]);
        }

        $suppliers = [
            [
                'code' => 'SUP-001',
                'name' => 'CV Sumber Kayu Jaya',
                'contact_person' => 'Pak Hendra',
                'phone' => '081234567890',
                'email' => 'sales@sumberkayujaya.test',
                'address' => 'Jl. Raya Industri Kayu No. 12, Jepara',
                'notes' => 'Supplier papan jati dan mahoni.',
            ],
            [
                'code' => 'SUP-002',
                'name' => 'Toko Finishing Makmur',
                'contact_person' => 'Bu Wati',
                'phone' => '082345678901',
                'email' => 'finishingmakmur@test.test',
                'address' => 'Jl. Pahlawan No. 45, Kudus',
                'notes' => 'Cat, pelitur, lem, dan amplas.',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['code' => $supplier['code']], $supplier + ['is_active' => true]);
        }

        $customers = [
            ['code' => 'CUS-001', 'name' => 'Bu Sari', 'phone' => '081111111111', 'email' => 'sari@example.test', 'address' => 'Perumahan Griya Asri Blok C2', 'customer_type' => 'retail'],
            ['code' => 'CUS-002', 'name' => 'Pak Budi', 'phone' => '082222222222', 'email' => 'budi@example.test', 'address' => 'Jl. Melati No. 8', 'customer_type' => 'project'],
            ['code' => 'CUS-003', 'name' => 'Restoran Rasa', 'phone' => '083333333333', 'email' => 'resto.rasa@example.test', 'address' => 'Jl. Kuliner No. 21', 'customer_type' => 'project'],
            ['code' => 'CUS-004', 'name' => 'Ibu Lina', 'phone' => '084444444444', 'email' => 'lina@example.test', 'address' => 'Jl. Kenanga No. 3', 'customer_type' => 'retail'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['code' => $customer['code']], $customer + ['is_active' => true]);
        }

        $woodCategory = Category::where('type', 'material')->where('name', 'Kayu Solid')->first();
        $finishingCategory = Category::where('type', 'material')->where('name', 'Finishing')->first();
        $hardwareCategory = Category::where('type', 'material')->where('name', 'Hardware')->first();
        $consumableCategory = Category::where('type', 'material')->where('name', 'Consumable')->first();

        $jati = WoodType::where('code', 'JATI')->first();
        $mahoni = WoodType::where('code', 'MAHONI')->first();
        $sono = WoodType::where('code', 'SONO')->first();
        $premium = WoodGrade::where('code', 'PREM')->first();
        $standard = WoodGrade::where('code', 'STD')->first();
        $economy = WoodGrade::where('code', 'EKO')->first();

        $materials = [
            ['code' => 'KAYU-JATI-2', 'name' => 'Papan Jati 2 cm', 'category_id' => $woodCategory?->id, 'material_type' => 'wood', 'wood_type_id' => $jati?->id, 'wood_grade_id' => $premium?->id, 'unit_id' => Unit::where('code', 'lembar')->value('id'), 'thickness_mm' => 20, 'width_mm' => 200, 'length_mm' => 2000, 'min_stock_qty' => 10, 'purchase_price' => 450000, 'location' => 'Rak A1'],
            ['code' => 'KAYU-MAHONI-2', 'name' => 'Papan Mahoni 2 cm', 'category_id' => $woodCategory?->id, 'material_type' => 'wood', 'wood_type_id' => $mahoni?->id, 'wood_grade_id' => $standard?->id, 'unit_id' => Unit::where('code', 'lembar')->value('id'), 'thickness_mm' => 20, 'width_mm' => 200, 'length_mm' => 2000, 'min_stock_qty' => 8, 'purchase_price' => 380000, 'location' => 'Rak A2'],
            ['code' => 'KAYU-SONO-2', 'name' => 'Papan Sono 2 cm', 'category_id' => $woodCategory?->id, 'material_type' => 'wood', 'wood_type_id' => $sono?->id, 'wood_grade_id' => $economy?->id, 'unit_id' => Unit::where('code', 'lembar')->value('id'), 'thickness_mm' => 20, 'width_mm' => 200, 'length_mm' => 2000, 'min_stock_qty' => 6, 'purchase_price' => 260000, 'location' => 'Rak A3'],
            ['code' => 'FIN-HLS', 'name' => 'Cat HLS Natural', 'category_id' => $finishingCategory?->id, 'material_type' => 'finishing', 'unit_id' => Unit::where('code', 'L')->value('id'), 'min_stock_qty' => 5, 'purchase_price' => 85000, 'location' => 'Rak B1'],
            ['code' => 'FIN-PELITUR', 'name' => 'Pelitur Clear', 'category_id' => $finishingCategory?->id, 'material_type' => 'finishing', 'unit_id' => Unit::where('code', 'L')->value('id'), 'min_stock_qty' => 3, 'purchase_price' => 95000, 'location' => 'Rak B2'],
            ['code' => 'HW-SEKRUP', 'name' => 'Sekrup Kayu 4 cm', 'category_id' => $hardwareCategory?->id, 'material_type' => 'hardware', 'unit_id' => Unit::where('code', 'pcs')->value('id'), 'min_stock_qty' => 100, 'purchase_price' => 500, 'location' => 'Rak C1'],
            ['code' => 'HW-ENGSEL', 'name' => 'Engsel Pintu', 'category_id' => $hardwareCategory?->id, 'material_type' => 'hardware', 'unit_id' => Unit::where('code', 'pcs')->value('id'), 'min_stock_qty' => 20, 'purchase_price' => 15000, 'location' => 'Rak C2'],
            ['code' => 'CON-LEM', 'name' => 'Lem Kayu', 'category_id' => $consumableCategory?->id, 'material_type' => 'consumable', 'unit_id' => Unit::where('code', 'kg')->value('id'), 'min_stock_qty' => 3, 'purchase_price' => 45000, 'location' => 'Rak D1'],
            ['code' => 'CON-AMPLAS', 'name' => 'Amplas 120', 'category_id' => $consumableCategory?->id, 'material_type' => 'consumable', 'unit_id' => Unit::where('code', 'lembar')->value('id'), 'min_stock_qty' => 20, 'purchase_price' => 5000, 'location' => 'Rak D2'],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(['code' => $material['code']], $material + ['stock_qty' => 0, 'is_active' => true]);
        }

        $settings = [
            ['key' => 'company_name', 'value' => 'Kaya Kayu Workshop', 'type' => 'string', 'group' => 'company', 'description' => 'Nama workshop pada invoice.'],
            ['key' => 'company_address', 'value' => 'Jl. Raya Mebel No. 18, Jepara', 'type' => 'string', 'group' => 'company', 'description' => 'Alamat workshop.'],
            ['key' => 'company_phone', 'value' => '0812-3456-7890', 'type' => 'string', 'group' => 'company', 'description' => 'Telepon workshop.'],
            ['key' => 'company_email', 'value' => 'halo@kayakayu.test', 'type' => 'string', 'group' => 'company', 'description' => 'Email workshop.'],
            ['key' => 'dp_percent', 'value' => '30', 'type' => 'decimal', 'group' => 'transaction', 'description' => 'Persentase DP.'],
            ['key' => 'grace_period_days', 'value' => '3', 'type' => 'integer', 'group' => 'transaction', 'description' => 'Tenggang pelunasan setelah tenggat.'],
            ['key' => 'quick_price_per_m2', 'value' => '1000000', 'type' => 'decimal', 'group' => 'transaction', 'description' => 'Harga cepat per m2.'],
            ['key' => 'invoice_notes', 'value' => 'Terima kasih telah mempercayakan furniture Anda kepada Kaya Kayu Workshop.', 'type' => 'string', 'group' => 'invoice', 'description' => 'Catatan invoice.'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
