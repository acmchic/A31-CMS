<?php

namespace Modules\ProductionManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProductionManagement\Models\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $materials = [
            // Phần I: Sơn trước cải tiến bệ phóng 5P73
            ['code' => 'VT001', 'name' => 'Dung môi butyl acetat', 'unit' => 'lít', 'description' => 'Dung môi butyl acetat'],
            ['code' => 'VT002', 'name' => 'Dung môi lau P850-1402', 'unit' => 'lít', 'description' => 'Dung môi lau P850-1402'],
            ['code' => 'VT003', 'name' => 'Sơn chống gỉ Anti-Corrosion Etch Primer/5L; F397/5L', 'unit' => 'lít', 'description' => 'Sơn chống gỉ Anti-Corrosion Etch Primer/5L; F397/5L'],
            ['code' => 'VT004', 'name' => 'Chất đóng rắn Reactive Hardener (For Etch Primer)/5L; F368/5L', 'unit' => 'lít', 'description' => 'Chất đóng rắn Reactive Hardener (For Etch Primer)/5L; F368/5L'],
            ['code' => 'VT005', 'name' => 'Sơn lót chống gỉ epoxy P565-895', 'unit' => 'lít', 'description' => 'Sơn lót chống gỉ epoxy P565-895'],
            ['code' => 'VT006', 'name' => 'Sơn lót bề mặt P565-888/3,5L', 'unit' => 'lít', 'description' => 'Sơn lót bề mặt P565-888/3,5L'],
            ['code' => 'VT007', 'name' => 'Chất đóng rắn Hardener, 2K P210-926/0.5L', 'unit' => 'lít', 'description' => 'Chất đóng rắn Hardener, 2K P210-926/0.5L'],
            ['code' => 'VT008', 'name' => 'Solvent P850-1492', 'unit' => 'lít', 'description' => 'Solvent P850-1492'],
            ['code' => 'VT009', 'name' => 'Sơn màu 2K Green P420-5747U', 'unit' => 'kg', 'description' => 'Sơn màu 2K Green P420-5747U'],
            ['code' => 'VT010', 'name' => 'Dung môi chống rỉ muối biển Thinner 91-92', 'unit' => 'lít', 'description' => 'Dung môi chống rỉ muối biển Thinner 91-92'],
            ['code' => 'VT011', 'name' => 'Sơn lót chống rỉ Epoxy màu ghi S.EP-NI; Đại Bàng', 'unit' => 'kg', 'description' => 'Sơn lót chống rỉ Epoxy màu ghi S.EP-NI; Đại Bàng'],
            ['code' => 'VT012', 'name' => 'Sơn Epoxy màu đen S.EP-P1, De-01; Đại Bàng', 'unit' => 'kg', 'description' => 'Sơn Epoxy màu đen S.EP-P1, De-01; Đại Bàng'],
            ['code' => 'VT013', 'name' => 'Sơn màu 2K White P420-9003', 'unit' => 'kg', 'description' => 'Sơn màu 2K White P420-9003'],
            ['code' => 'VT014', 'name' => 'Sơn màu 2K Red P420-7626', 'unit' => 'kg', 'description' => 'Sơn màu 2K Red P420-7626'],
            ['code' => 'VT015', 'name' => 'Dung môi pha sơn DMT3-EP; Đại Bàng', 'unit' => 'lít', 'description' => 'Dung môi pha sơn DMT3-EP; Đại Bàng'],
            ['code' => 'VT016', 'name' => 'Matic trét đắp, P551-1050/2KG', 'unit' => 'kg', 'description' => 'Matic trét đắp, P551-1050/2KG'],
            ['code' => 'VT017', 'name' => 'Băng dính giấy 4 AU-50 mm x 27 m', 'unit' => 'cuộn', 'description' => 'Băng dính giấy 4 AU-50 mm x 27 m'],
            ['code' => 'VT018', 'name' => 'Băng dính Film 3M Plastic 7021C, 900 mm x 20 m', 'unit' => 'cuộn', 'description' => 'Băng dính Film 3M Plastic 7021C, 900 mm x 20 m'],
            ['code' => 'VT019', 'name' => 'Lưới lọc sơn 350 mesh', 'unit' => 'm²', 'description' => 'Lưới lọc sơn 350 mesh'],
            ['code' => 'VT020', 'name' => 'Giấy ráp Abranet P320, (70x198) mm', 'unit' => 'tờ', 'description' => 'Giấy ráp Abranet P320, (70x198) mm'],
            ['code' => 'VT021', 'name' => 'Giấy Ráp Abranet Ø150 mm, P320', 'unit' => 'tờ', 'description' => 'Giấy Ráp Abranet Ø150 mm, P320'],
            ['code' => 'VT022', 'name' => 'Nhám xốp Goldflex P400, (115x125) mm', 'unit' => 'tờ', 'description' => 'Nhám xốp Goldflex P400, (115x125) mm'],
            ['code' => 'VT023', 'name' => 'Chổi quét sơn 3 cm', 'unit' => 'cái', 'description' => 'Chổi quét sơn 3 cm'],
            ['code' => 'VT024', 'name' => 'Chổi quét sơn 6,5 cm', 'unit' => 'cái', 'description' => 'Chổi quét sơn 6,5 cm'],
            ['code' => 'VT025', 'name' => 'Chổi quét sơn 2,5 cm', 'unit' => 'cái', 'description' => 'Chổi quét sơn 2,5 cm'],
            ['code' => 'VT026', 'name' => 'Keo NITE-1, 310 ml/tuýp', 'unit' => 'tuýp', 'description' => 'Keo NITE-1, 310 ml/tuýp'],
            ['code' => 'VT027', 'name' => 'Dầu A01', 'unit' => 'lít', 'description' => 'Dầu A01'],
            ['code' => 'VT028', 'name' => 'Dầu AK', 'unit' => 'lít', 'description' => 'Dầu AK'],
            ['code' => 'VT029', 'name' => 'Mỡ chịu nhiệt SOPEC NLG13', 'unit' => 'kg', 'description' => 'Mỡ chịu nhiệt SOPEC NLG13'],
            ['code' => 'VT030', 'name' => 'Giấy ráp nhật P100, (230 x 280) mm', 'unit' => 'tờ', 'description' => 'Giấy ráp nhật P100, (230 x 280) mm'],
            ['code' => 'VT031', 'name' => 'Giấy ráp nhật P150, (230 x 280) mm', 'unit' => 'tờ', 'description' => 'Giấy ráp nhật P150, (230 x 280) mm'],
            ['code' => 'VT032', 'name' => 'Giấy ráp Nhật P240, (230 x 280) mm', 'unit' => 'tờ', 'description' => 'Giấy ráp Nhật P240, (230 x 280) mm'],
            ['code' => 'VT033', 'name' => 'Giấy ráp Nhật P400, (230 x 280) mm', 'unit' => 'tờ', 'description' => 'Giấy ráp Nhật P400, (230 x 280) mm'],
            ['code' => 'VT034', 'name' => 'Hạt mài cạnh GH50', 'unit' => 'kg', 'description' => 'Hạt mài cạnh GH50'],
            ['code' => 'VT035', 'name' => 'Mặt nạ phòng độc 3M 6800', 'unit' => 'cái', 'description' => 'Mặt nạ phòng độc 3M 6800'],
            ['code' => 'VT036', 'name' => 'Bông lọc 5N11', 'unit' => 'cái', 'description' => 'Bông lọc 5N11'],
            ['code' => 'VT037', 'name' => 'Phin lọc than hoạt tính 3M 6001', 'unit' => 'cái', 'description' => 'Phin lọc than hoạt tính 3M 6001'],
            ['code' => 'VT038', 'name' => 'Kính bảo hộ 3M 334AF', 'unit' => 'cái', 'description' => 'Kính bảo hộ 3M 334AF'],
            ['code' => 'VT039', 'name' => 'Giấy ráp đĩa P36, (125 x22) mm', 'unit' => 'tờ', 'description' => 'Giấy ráp đĩa P36, (125 x22) mm'],
            ['code' => 'VT040', 'name' => 'Phớt đánh gỉ Ø150', 'unit' => 'cái', 'description' => 'Phớt đánh gỉ Ø150'],
            ['code' => 'VT041', 'name' => 'Chén cước sắt không ốc Ø100', 'unit' => 'cái', 'description' => 'Chén cước sắt không ốc Ø100'],
            ['code' => 'VT042', 'name' => 'Vải phin khổ 1 m', 'unit' => 'm', 'description' => 'Vải phin khổ 1 m'],
            ['code' => 'VT043', 'name' => 'Giẻ bảo quản', 'unit' => 'kg', 'description' => 'Giẻ bảo quản'],
            ['code' => 'VT044', 'name' => 'Khẩu trang vải', 'unit' => 'cái', 'description' => 'Khẩu trang vải'],
            ['code' => 'VT045', 'name' => 'Xà phòng OMO', 'unit' => 'kg', 'description' => 'Xà phòng OMO'],
            ['code' => 'VT046', 'name' => 'Găng tay sợi', 'unit' => 'đôi', 'description' => 'Găng tay sợi'],
            
            // Phần II: Sơn tích hợp bệ phóng 5P73-VT
            ['code' => 'VT047', 'name' => 'Dung môi butyl acetat', 'unit' => 'lít', 'description' => 'Dung môi butyl acetat (tích hợp)'],
            ['code' => 'VT048', 'name' => 'Dung môi lau P850-1402', 'unit' => 'lít', 'description' => 'Dung môi lau P850-1402 (tích hợp)'],
            ['code' => 'VT049', 'name' => 'Chất đóng rắn Hardener, 2K P210-926/0.5L', 'unit' => 'lít', 'description' => 'Chất đóng rắn Hardener, 2K P210-926/0.5L (tích hợp)'],
            ['code' => 'VT050', 'name' => 'Dung môi pha Solvent, P850-1492', 'unit' => 'lít', 'description' => 'Dung môi pha Solvent, P850-1492'],
            ['code' => 'VT051', 'name' => 'Sơn màu 2K Green P420-5747U', 'unit' => 'kg', 'description' => 'Sơn màu 2K Green P420-5747U (tích hợp)'],
            ['code' => 'VT052', 'name' => 'Sơn màu 2K Brown 497U', 'unit' => 'kg', 'description' => 'Sơn màu 2K Brown 497U'],
            ['code' => 'VT053', 'name' => 'Sơn màu 2K Green P420-7496U', 'unit' => 'kg', 'description' => 'Sơn màu 2K Green P420-7496U'],
            ['code' => 'VT054', 'name' => 'Sơn màu 2K Black P420-419C', 'unit' => 'kg', 'description' => 'Sơn màu 2K Black P420-419C'],
            ['code' => 'VT055', 'name' => 'Sơn màu 2K White P420-9003', 'unit' => 'kg', 'description' => 'Sơn màu 2K White P420-9003 (tích hợp)'],
            ['code' => 'VT056', 'name' => 'Sơn màu 2K Red P420-7626', 'unit' => 'kg', 'description' => 'Sơn màu 2K Red P420-7626 (tích hợp)'],
            ['code' => 'VT057', 'name' => 'Sơn bóng mờ Matt clearcoat PPG D8115', 'unit' => 'lít', 'description' => 'Sơn bóng mờ Matt clearcoat PPG D8115'],
            ['code' => 'VT058', 'name' => 'Chất đóng rắn PPG D841', 'unit' => 'lít', 'description' => 'Chất đóng rắn PPG D841'],
            ['code' => 'VT059', 'name' => 'Dung môi pha PPG D812 (thinner)', 'unit' => 'lít', 'description' => 'Dung môi pha PPG D812 (thinner)'],
            ['code' => 'VT060', 'name' => 'Sơn chống trầy Raptor Liner + đóng rắn D803', 'unit' => 'lít', 'description' => 'Sơn chống trầy Raptor Liner + đóng rắn D803'],
            ['code' => 'VT061', 'name' => 'Matic trét đắp, P551-1050/2KG', 'unit' => 'kg', 'description' => 'Matic trét đắp, P551-1050/2KG (tích hợp)'],
            ['code' => 'VT062', 'name' => 'Băng dính giấy 4 AU-50 mm x 27 m', 'unit' => 'cuộn', 'description' => 'Băng dính giấy 4 AU-50 mm x 27 m (tích hợp)'],
            ['code' => 'VT063', 'name' => 'Băng dính giấy 4 AU-20 mm x 50 m', 'unit' => 'cuộn', 'description' => 'Băng dính giấy 4 AU-20 mm x 50 m'],
            ['code' => 'VT064', 'name' => 'Băng dính chỉ 6 mm x 33 m, 3M471', 'unit' => 'cuộn', 'description' => 'Băng dính chỉ 6 mm x 33 m, 3M471'],
            ['code' => 'VT065', 'name' => 'Băng keo 1 mặt màng Nylon che bụi, chắn sơn 3M 7021 (550mm x 25m)', 'unit' => 'cuộn', 'description' => 'Băng keo 1 mặt màng Nylon che bụi, chắn sơn 3M 7021 (550mm x 25m)'],
            ['code' => 'VT066', 'name' => 'Bút lông Thiên Long WB-03', 'unit' => 'cái', 'description' => 'Bút lông Thiên Long WB-03'],
            ['code' => 'VT067', 'name' => 'Lưới lọc sơn 350 mesh', 'unit' => 'm²', 'description' => 'Lưới lọc sơn 350 mesh (tích hợp)'],
            ['code' => 'VT068', 'name' => 'Giấy nhám nước Kovax P1500', 'unit' => 'tờ', 'description' => 'Giấy nhám nước Kovax P1500'],
            ['code' => 'VT069', 'name' => 'Giấy ráp Abranet 70 x198 mm, P320', 'unit' => 'tờ', 'description' => 'Giấy ráp Abranet 70 x198 mm, P320'],
            ['code' => 'VT070', 'name' => 'Giấy Ráp Abranet tròn 150mm P150', 'unit' => 'tờ', 'description' => 'Giấy Ráp Abranet tròn 150mm P150'],
            ['code' => 'VT071', 'name' => 'Giấy Ráp Abranet tròn 150mm P320', 'unit' => 'tờ', 'description' => 'Giấy Ráp Abranet tròn 150mm P320'],
            ['code' => 'VT072', 'name' => 'Nhám xốp Goldflex 115 x125 mm P400', 'unit' => 'tờ', 'description' => 'Nhám xốp Goldflex 115 x125 mm P400'],
            ['code' => 'VT073', 'name' => 'Bộ cọ vẽ Oil Brush BMHS0031', 'unit' => 'bộ', 'description' => 'Bộ cọ vẽ Oil Brush BMHS0031'],
            ['code' => 'VT074', 'name' => 'Chổi quét sơn 5 cm', 'unit' => 'cái', 'description' => 'Chổi quét sơn 5 cm'],
            ['code' => 'VT075', 'name' => 'Xăng E5 RON92-II', 'unit' => 'lít', 'description' => 'Xăng E5 RON92-II'],
            ['code' => 'VT076', 'name' => 'Mỡ chịu nhiệt SOPEC NLG13', 'unit' => 'kg', 'description' => 'Mỡ chịu nhiệt SOPEC NLG13 (bổ sung)'],
            ['code' => 'VT077', 'name' => 'Giẻ bảo quản', 'unit' => 'kg', 'description' => 'Giẻ bảo quản (bổ sung)'],
            ['code' => 'VT078', 'name' => 'Khẩu trang vải', 'unit' => 'cái', 'description' => 'Khẩu trang vải (bổ sung)'],
            ['code' => 'VT079', 'name' => 'Xà phòng OMO', 'unit' => 'kg', 'description' => 'Xà phòng OMO (bổ sung)'],
            ['code' => 'VT080', 'name' => 'Găng tay sợi', 'unit' => 'đôi', 'description' => 'Găng tay sợi (bổ sung)'],
            ['code' => 'VT081', 'name' => 'Dao dọc giấy DELI', 'unit' => 'cái', 'description' => 'Dao dọc giấy DELI'],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['code' => $material['code']],
                [
                    'code' => $material['code'],
                    'ten_vat_tu' => $material['name'],
                    'don_vi_tinh' => $material['unit'],
                    'mo_ta' => $material['description'] ?? null,
                    'status' => 'active',
                    'can_import' => true,
                    'can_export' => true,
                    'min_stock_level' => 0.00,
                    'max_stock_level' => null,
                ]
            );
        }

        $this->command->info("✅ Đã tạo " . count($materials) . " vật tư!");
    }
}
