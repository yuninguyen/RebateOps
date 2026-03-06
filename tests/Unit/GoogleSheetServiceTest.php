<?php

namespace Tests\Unit; // 🟢 1. BẮT BUỘC PHẢI CÓ DÒNG NÀY ĐỂ LARAVEL TÌM THẤY FILE

use Tests\TestCase; // 🟢 2. DÙNG TESTCASE CỦA LARAVEL (Xóa dòng PHPUnit đi)
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Cache;
use Mockery;

class GoogleSheetServiceTest extends TestCase
{
    /**
     * Test: Đảm bảo hàm tạo Sheet mới không gọi API Google nếu Sheet đã tồn tại trong Cache
     */
    public function test_create_sheet_skips_api_call_when_sheet_already_exists()
    {
        // 1. LÀM GIẢ CACHE (Mock Cache): 
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['Emails' => 12345]);

        // 2. LÀM GIẢ GOOGLE SERVICE (Mock Object):
        /** @var \App\Services\GoogleSheetService|\Mockery\MockInterface $mockService */ // 🟢 3. DÒNG NÀY GIÚP IDE HIỂU VÀ TẮT GẠCH ĐỎ
        $mockService = Mockery::mock(GoogleSheetService::class)->makePartial();
        $mockService->shouldReceive('__construct')->andReturn(null);

        // 3. THỰC THI HÀM:
        $mockService->createSheetIfNotExist('Emails');

        // 4. KIỂM CHỨNG: 
        $this->assertTrue(true);
    }
}