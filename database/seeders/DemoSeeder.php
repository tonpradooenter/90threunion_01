<?php

namespace Database\Seeders;

use App\Models\BookablePlace;
use App\Models\Product;
use App\Models\Zone;
use App\Models\DiningMenu;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DiningMenu::firstOrCreate(['name' => 'ชุดอาหารมาตรฐาน (ตัวอย่าง)'], ['description' => 'รายการอาหารตัวอย่างสำหรับทดสอบ ผู้ดูแลแก้ไขก่อนใช้งานจริง', 'is_active' => true, 'sort_order' => 1]);
        DiningMenu::firstOrCreate(['name' => 'ชุดอาหารมังสวิรัติ (ตัวอย่าง)'], ['description' => 'ตัวเลือกตัวอย่างสำหรับทดสอบ แจ้งข้อจำกัดอาหารเพิ่มเติมในช่องหมายเหตุ', 'is_active' => true, 'sort_order' => 2]);
        foreach ([
            ['name' => 'เสื้อโปโล 90 ปี', 'description' => 'เสื้อที่ระลึกงานคืนสู่เหย้า สีกรมท่า ใส่สบาย', 'price_satang' => 39000, 'stock_on_hand' => 40],
            ['name' => 'แก้วเก็บความเย็น', 'description' => 'เก็บความทรงจำดี ๆ ทุกครั้งที่ยกแก้ว', 'price_satang' => 25000, 'stock_on_hand' => 30],
            ['name' => 'กระเป๋าผ้า SISAT', 'description' => 'กระเป๋าผ้าสำหรับใช้ทุกวัน ลายครบรอบ 90 ปี', 'price_satang' => 19000, 'stock_on_hand' => 50],
        ] as $item) {
            Product::firstOrCreate(['name' => $item['name']], $item + ['kind' => 'souvenir', 'is_active' => true]);
        }

        $colors = ['A'=>'#e5a900','B'=>'#279e4e','C'=>'#db334b','D'=>'#ed7b34','E'=>'#2676d2','F'=>'#d841b4','G'=>'#18aeb8','H'=>'#53678e','I'=>'#64b85d','J'=>'#8551cf'];
        foreach (range('A', 'J') as $index => $zone) {
            Zone::firstOrCreate(['code' => $zone], ['price_satang' => 350000 + (9 - $index) * 25000, 'is_active' => $index < 2, 'color' => $colors[$zone], 'sort_order' => $index + 1]);
            for ($number = 1; $number <= 60; $number++) {
                BookablePlace::firstOrCreate(['kind' => 'table', 'zone' => $zone, 'label' => sprintf('%02d', $number)], ['capacity' => 8, 'is_active' => true]);
            }
        }
    }
}
