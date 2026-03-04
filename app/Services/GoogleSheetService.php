<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\ClearValuesRequest;

class GoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId = '1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4'; // Thay ID Google Sheet vào đây

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/google-auth.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
    }

    /**
     * Đóng băng hàng đầu tiên và định dạng in đậm tiêu đề
     */
    public function freezeAndFormatHeader(string $sheetName)
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        $sheetId = null;
        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() == $sheetName) {
                $sheetId = $sheet->getProperties()->getSheetId();
                break;
            }
        }

        if ($sheetId === null) return;

        $requests = [
            // 1. Đóng băng 1 hàng đầu tiên
            new \Google\Service\Sheets\Request([
                'updateSheetProperties' => [
                    'properties' => [
                        'sheetId' => $sheetId,
                        'gridProperties' => ['frozenRowCount' => 1],
                    ],
                    'fields' => 'gridProperties.frozenRowCount',
                ],
            ]),
            // 2. In đậm hàng tiêu đề (A1:AC1)
            new \Google\Service\Sheets\Request([
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 0,
                        'endRowIndex' => 1,
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'textFormat' => ['bold' => true],
                            'backgroundColor' => ['red' => 0.9, 'green' => 0.9, 'blue' => 0.9], // Màu xám nhạt
                        ]
                    ],
                    'fields' => 'userEnteredFormat(textFormat,backgroundColor)'
                ]
            ])
        ];

        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest(['requests' => $requests]);
        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }

    /**
     * Hàm phụ trợ: Tự động lấy tên Tab đầu tiên trong file Google Sheet
     */
    private function getFirstSheetName()
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        return $spreadsheet->getSheets()[0]->getProperties()->getTitle();
    }

    // ==========================================
    // CHIỀU 1: WEB -> SHEET (Thêm một dòng mới)
    // Đã thêm tham số $sheetName (Nếu không truyền, tự động lấy Sheet đầu tiên)
    // ==========================================
    public function appendRow(array $data, ?string $sheetName = null)
    {
        // Nếu không chỉ định tên Sheet, lấy mặc định tab đầu tiên để code cũ không bị lỗi
        $targetSheet = $sheetName ?? $this->getFirstSheetName();

        $values = [$data];
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];

        return $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            "'{$targetSheet}'!A1",
            $body,
            $params
        );
    }

    // ==========================================
    // TÍNH NĂNG MỚI: Thêm NHIỀU dòng cùng lúc (Dùng cho Bulk Action Filament)
    // ==========================================
    public function appendMultipleRows(array $dataRows, ?string $sheetName = null)
    {
        $targetSheet = $sheetName ?? $this->getFirstSheetName();

        $body = new ValueRange(['values' => $dataRows]);
        $params = ['valueInputOption' => 'RAW'];

        return $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            "'{$targetSheet}'!A1",
            $body,
            $params
        );
    }

    // ==========================================
    // CẬP NHẬT SHEET (Ghi đè lại toàn bộ)
    // ==========================================
    public function updateSheet(array $values, $range = 'A1:AC', ?string $sheetName = null)
    {
        // 1. Xác định tên Sheet và làm sạch khoảng trắng
        $targetSheet = trim($sheetName ?? $this->getFirstSheetName());

        try {
            // 2. Bọc tên Sheet trong dấu nháy đơn (VÍ DỤ: 'Payout_Logs'!A1:Z1000)
            // Việc bọc nháy đơn giúp fix lỗi "Unable to parse range" cực kỳ hiệu quả
            $safeSheetName = "'" . str_replace("'", "''", $targetSheet) . "'";

            // 3. Xóa dữ liệu cũ của đúng cái Tab đó
            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId,
                "{$safeSheetName}!A1:AC1000",
                new \Google\Service\Sheets\ClearValuesRequest()
            );

            // 4. Chuẩn bị dữ liệu để ghi
            $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);

            // 5. Ráp tên Sheet đã bọc nháy vào Range mới
            $fullRange = "{$safeSheetName}!{$range}";

            return $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $fullRange,
                $body,
                ['valueInputOption' => 'RAW']
            );
        } catch (\Exception $e) {
            // Log lỗi chi tiết để kiểm tra
            \Log::error("Google Sheets API Error: " . $e->getMessage() . " | Sheet: " . $targetSheet);
            throw $e;
        }
    }

    // ==========================================
    // TÍNH NĂNG: UPSERT (Tự động tìm ID để Update hoặc Append nếu mới)
    // Áp dụng cho mọi loại Resource (Account, Tracker, Payout...)
    // ==========================================
    public function upsertRows(array $dataRows, ?string $sheetName = null)
    {
        $targetSheet = $sheetName ?? $this->getFirstSheetName();
        $safeSheetName = "'" . str_replace("'", "''", $targetSheet) . "'";

        // 1. Đọc cột A để lấy danh sách ID đang có trên Sheet
        $existingIds = $this->readSheet('A1:AC', $targetSheet);

        $idMap = [];
        if (!empty($existingIds)) {
            foreach ($existingIds as $index => $row) {
                if (isset($row[0]) && trim($row[0]) !== '') {
                    // Tạo bản đồ: [ID => Số hàng trên Google Sheet]
                    $idMap[(string)$row[0]] = $index + 1;
                }
            }
        }

        $updateData = [];
        $appendData = [];

        // 2. Phân loại: Dòng nào cần Update, dòng nào cần Thêm mới
        foreach ($dataRows as $rowData) {
            $row = array_values((array)$rowData);
            $id = (string)$row[0]; // ID luôn nằm ở cột đầu tiên

            if (isset($idMap[$id])) {
                // Nếu ID đã tồn tại -> Update đúng hàng đó
                $rowNumber = $idMap[$id];
                $updateData[] = new \Google\Service\Sheets\ValueRange([
                    'range' => "{$safeSheetName}!A{$rowNumber}",
                    'values' => [$row]
                ]);
            } else {
                // Nếu ID chưa có -> Thêm vào danh sách Append
                $appendData[] = $row;
            }
        }

        // 3. Thực hiện Update hàng loạt (Batch Update) - Cực nhanh và tiết kiệm API
        if (!empty($updateData)) {
            $batchRequest = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data' => $updateData
            ]);
            $this->service->spreadsheets_values->batchUpdate($this->spreadsheetId, $batchRequest);
        }

        // 4. Thực hiện Append các dòng mới vào cuối Sheet
        if (!empty($appendData)) {
            $body = new \Google\Service\Sheets\ValueRange(['values' => $appendData]);
            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                "{$safeSheetName}!A1",
                $body,
                ['valueInputOption' => 'RAW']
            );
        }

        return [
            'updated' => count($updateData),
            'appended' => count($appendData)
        ];
    }

    // ==========================================
    // TÍNH NĂNG ĐỊNH DẠNG: Ép kiểu hiển thị chữ thành "Clip" (Cắt bớt)
    // ==========================================
    public function formatColumnsAsClip(string $sheetName, int $startColIndex, int $endColIndex)
    {
        // 1. Lấy Sheet ID (ID dạng số của tab hiện tại, khác với Spreadsheet ID)
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        $sheetId = null;
        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() == $sheetName) {
                $sheetId = $sheet->getProperties()->getSheetId();
                break;
            }
        }

        if ($sheetId === null) return;

        // 2. Tạo Request ép định dạng WrapStrategy thành CLIP
        $requests = [
            new \Google\Service\Sheets\Request([
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startColumnIndex' => $startColIndex, // Cột bắt đầu (Tính từ 0)
                        'endColumnIndex' => $endColIndex,     // Cột kết thúc (Exclusive)
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'wrapStrategy' => 'CLIP'
                        ]
                    ],
                    'fields' => 'userEnteredFormat.wrapStrategy'
                ]
            ])
        ];

        // 3. Thực thi lệnh Format
        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }

    // ==========================================
    // TÍNH NĂNG ĐỊNH DẠNG: Tìm đúng dòng đó và Xóa bỏ hoàn toàn
    // ==========================================
    public function deleteRowsByIds(array $ids, ?string $sheetName = null)
    {
        if (empty($ids)) return;
        $targetSheet = $sheetName ?? $this->getFirstSheetName();

        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        $sheetId = null;
        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() == $targetSheet) {
                $sheetId = $sheet->getProperties()->getSheetId();
                break;
            }
        }

        if ($sheetId === null) return;

        $existingData = $this->readSheet('A1:AC', $targetSheet);
        $indicesToDelete = [];

        if (!empty($existingData)) {
            foreach ($existingData as $index => $row) {
                if (isset($row[0]) && in_array((string)$row[0], $ids)) {
                    $indicesToDelete[] = $index;
                }
            }
        }

        if (empty($indicesToDelete)) return;

        rsort($indicesToDelete);

        $requests = [];
        foreach ($indicesToDelete as $rowIndex) {
            $requests[] = new \Google\Service\Sheets\Request([
                'deleteDimension' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'ROWS',
                        'startIndex' => $rowIndex,
                        'endIndex' => $rowIndex + 1
                    ]
                ]
            ]);
        }

        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        return $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }

    // ==========================================
    // CHIỀU 2: SHEET -> WEB (Đọc dữ liệu)
    // ==========================================
    public function readSheet($range = 'A2:AC', ?string $sheetName = null)
    {
        try {
            $targetSheet = $sheetName ?? $this->getFirstSheetName();
            $fullRange = "'{$targetSheet}'!{$range}";

            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $fullRange);
            $values = $response->getValues();

            return $values ?: [];
        } catch (\Exception $e) {
            \Log::error("Google Sheet Error: " . $e->getMessage());
            return null;
        }
    }

    // Trả về Service gốc để gọi lệnh BatchUpdate
    public function getService()
    {
        return $this->service;
    }

    // Trả về ID của Spreadsheet đang dùng
    public function getSpreadsheetId()
    {
        return $this->spreadsheetId;
    }

    // Hàm kiểm tra và tự tạo Tab nếu chưa có
    public function createSheetIfNotExist(string $sheetName)
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        $sheets = $spreadsheet->getSheets();

        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                return; // Đã tồn tại, thoát ra
            }
        }

        // Nếu chưa có, gửi lệnh tạo mới
        $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [
                'addSheet' => [
                    'properties' => ['title' => $sheetName]
                ]
            ]
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);
    }

    /**
     * Tự động tô màu cả hàng dựa trên giá trị của cột Status
     */
    /**
     * Tô màu theo quy tắc linh hoạt
     * $rules: Mảng chứa [ 'Tên trạng thái' => [màu RGB] ]
     */
    public function applyFormattingWithRules(string $sheetName, int $statusColIndex, array $rules)
    {
        $sheetId = $this->getSheetIdByName($sheetName);
        $colLetter = chr(65 + $statusColIndex);

        $requests = [];
        foreach ($rules as $status => $color) {
            $requests[] = new \Google\Service\Sheets\Request([
                'addConditionalFormatRule' => [
                    'rule' => [
                        'ranges' => [['sheetId' => $sheetId, 'startRowIndex' => 1]],
                        'booleanRule' => [
                            'condition' => [
                                'type' => 'CUSTOM_FORMULA',
                                'values' => [['userEnteredValue' => "=$" . $colLetter . "2=\"$status\""]]
                            ],
                            'format' => ['backgroundColor' => $color]
                        ]
                    ],
                    'index' => 0
                ]
            ]);
        }

        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest(['requests' => $requests]);
        return $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }

    /**
     * Hàm phụ trợ lấy ID số của Tab (SheetId) từ tên Tab
     */
    private function getSheetIdByName(string $sheetName)
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                return $sheet->getProperties()->getSheetId();
            }
        }
        throw new \Exception("Không tìm thấy Tab: {$sheetName}");
    }

    // --- KẾT THÚC ---

    // ==========================================
}
