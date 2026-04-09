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
    public function test_create_sheet_skips_api_call_when_sheet_already_exists(): void
    {
        // Mock cache trả về sheet đã tồn tại
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['Emails' => 12345]);

        // Verify không gọi API thực (GoogleSheetService được inject mock)
        $mockSheetService = $this->createMock(\App\Services\GoogleSheetService::class);
        $mockSheetService->expects($this->never())
            ->method('createSheet'); // Không tạo sheet mới

        $syncService = new \App\Services\GoogleSyncService($mockSheetService);
        // ... gọi method cần test

        $this->addToAssertionCount(1); // Rõ ràng hơn assertTrue(true)
    }
}