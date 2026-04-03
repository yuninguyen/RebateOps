<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\ClearValuesRequest;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        try {
            // Láº¥y cáº¥u hÃ¬nh ID
            $this->spreadsheetId = config('services.google.spreadsheet_id');

            // ðŸŸ¢ FIX: DÃ¹ng storage_path Ä‘á»ƒ luÃ´n táº¡o ra Ä‘Æ°á»ng dáº«n tuyá»‡t Ä‘á»‘i (Absolute Path)
            // DÃ¹ cáº¥u hÃ¬nh lÃ  gÃ¬, ta Ã©p nÃ³ chá»c tháº³ng vÃ o thÆ° má»¥c storage/app/
            $authPath = storage_path('app/google-auth.json');

            // Validate configuration
            if (empty($this->spreadsheetId)) {
                throw new \Exception('Google Spreadsheet ID is not configured.');
            }

            if (!file_exists($authPath)) {
                throw new \Exception("Google service account file not found at: " . $authPath);
            }

            // Initialize Google Client
            $this->client = new Client();
            $this->client->setApplicationName(config('app.name', 'RebateOps'));
            $this->client->addScope(Sheets::SPREADSHEETS);
            $this->client->setAuthConfig($authPath);
            $this->client->setAccessType('offline');
            $this->service = new Sheets($this->client);
        } catch (\Exception $e) {
            Log::error('Failed to initialize GoogleSheetService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Google Sheets service initialization failed');
        }
    }

    /**
     * ÄÃ³ng bÄƒng hÃ ng Ä‘áº§u tiÃªn vÃ  Ä‘á»‹nh dáº¡ng in Ä‘áº­m tiÃªu Ä‘á»
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
            // 1. ÄÃ³ng bÄƒng 1 hÃ ng Ä‘áº§u tiÃªn
            new \Google\Service\Sheets\Request([
                'updateSheetProperties' => [
                    'properties' => [
                        'sheetId' => $sheetId,
                        'gridProperties' => ['frozenRowCount' => 1],
                    ],
                    'fields' => 'gridProperties.frozenRowCount',
                ],
            ]),
            // 2. In Ä‘áº­m hÃ ng tiÃªu Ä‘á» (A1:AC)
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
                            'backgroundColor' => ['red' => 0.9, 'green' => 0.9, 'blue' => 0.9], // MÃ u xÃ¡m nháº¡t
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
     * HÃ m phá»¥ trá»£: Tá»± Ä‘á»™ng láº¥y tÃªn Tab Ä‘áº§u tiÃªn trong file Google Sheet
     */
    private function getFirstSheetName()
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        return $spreadsheet->getSheets()[0]->getProperties()->getTitle();
    }

    // ==========================================
    // CHIá»€U 1: WEB -> SHEET (ThÃªm má»™t dÃ²ng má»›i)
    // ==========================================
    public function appendRow(array $data, ?string $sheetName = null)
    {
        try {
            $targetSheet = $sheetName ?? $this->getFirstSheetName();

            $values = [$data];
            $body = new ValueRange(['values' => $values]);
            $params = ['valueInputOption' => 'RAW'];

            $result = $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                "'{$targetSheet}'!A1",
                $body,
                $params
            );

            Log::info('Successfully appended row to Google Sheets', [
                'sheet' => $targetSheet,
                'rows_updated' => $result->getUpdates()->getUpdatedRows()
            ]);

            return $result;
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Sheets API Error - Append Row', [
                'error' => $e->getMessage(),
                'data' => $data,
                'sheet' => $sheetName
            ]);
            throw new \Exception('Failed to append data to Google Sheets: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in appendRow', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    // ==========================================
    // TÃNH NÄ‚NG Má»šI: ThÃªm NHIá»€U dÃ²ng cÃ¹ng lÃºc (DÃ¹ng cho Bulk Action Filament)
    // ==========================================
    public function appendMultipleRows(array $dataRows, ?string $sheetName = null)
    {
        try {
            $targetSheet = $sheetName ?? $this->getFirstSheetName();

            $body = new ValueRange(['values' => $dataRows]);
            $params = ['valueInputOption' => 'RAW'];

            $result = $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                "'{$targetSheet}'!A1",
                $body,
                $params
            );

            Log::info('Successfully appended multiple rows to Google Sheets', [
                'sheet' => $targetSheet,
                'row_count' => count($dataRows),
                'rows_updated' => $result->getUpdates()->getUpdatedRows()
            ]);

            return $result;
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Sheets API Error - Append Multiple Rows', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'row_count' => count($dataRows),
                'sheet' => $sheetName
            ]);
            throw new \Exception('Failed to append multiple rows to Google Sheets: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in appendMultipleRows', [
                'error' => $e->getMessage(),
                'row_count' => count($dataRows)
            ]);
            throw $e;
        }
    }

    // ==========================================
    // Cáº¬P NHáº¬T SHEET (Ghi Ä‘Ã¨ láº¡i toÃ n bá»™)
    // ==========================================
    public function updateSheet(array $values, $range = 'A1:AC', ?string $sheetName = null)
    {
        try {
            $targetSheet = trim($sheetName ?? $this->getFirstSheetName());
            $safeSheetName = "'" . str_replace("'", "''", $targetSheet) . "'";

            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId,
                "{$safeSheetName}!A1:AC",
                new \Google\Service\Sheets\ClearValuesRequest()
            );

            $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
            $fullRange = "{$safeSheetName}!{$range}";

            return $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $fullRange,
                $body,
                ['valueInputOption' => 'RAW']
            );
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Sheets API Error - Update Sheet', [
                'error' => $e->getMessage(),
                'range' => $range,
                'sheet' => $sheetName
            ]);
            throw new \Exception('Failed to update data in Google Sheets: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in updateSheet', [
                'error' => $e->getMessage(),
                'sheet' => $sheetName
            ]);
            throw $e;
        }
    }

    // ==========================================
    // TÃNH NÄ‚NG: UPSERT (Tá»± Ä‘á»™ng tÃ¬m ID Ä‘á»ƒ Update hoáº·c Append náº¿u má»›i)
    // ==========================================
    public function upsertRows(array $dataRows, ?string $sheetName = null, ?array $headers = null)
    {
        try {
            $targetSheet = $sheetName ?? $this->getFirstSheetName();
            $safeSheetName = "'" . str_replace("'", "''", $targetSheet) . "'";

            $existingIds = $this->readSheet('A1:AC', $targetSheet);

            $idMap = [];
            if (!empty($existingIds)) {
                foreach ($existingIds as $index => $row) {
                    if (isset($row[0]) && trim($row[0]) !== '') {
                        $idMap[(string)$row[0]] = $index + 1;
                    }
                }
            }

            $updateData = [];
            $appendData = [];

            foreach ($dataRows as $rowData) {
                $row = array_values((array)$rowData);
                $id = (string)$row[0];

                if (isset($idMap[$id])) {
                    $rowNumber = $idMap[$id];
                    $updateData[] = new \Google\Service\Sheets\ValueRange([
                        'range' => "{$safeSheetName}!A{$rowNumber}",
                        'values' => [$row]
                    ]);
                } else {
                    $appendData[] = $row;
                }
            }

            if (!empty($updateData)) {
                $batchRequest = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                    'valueInputOption' => 'RAW',
                    'data' => $updateData
                ]);
                $this->service->spreadsheets_values->batchUpdate($this->spreadsheetId, $batchRequest);
            }

            // ðŸŸ¢ FIX BUG THIáº¾U HEADER: 
            // Náº¿u tab má»›i hoÃ n toÃ n (idMap rá»—ng), chÃ¨n Header lÃªn Ä‘áº§u máº£ng dá»¯ liá»‡u chuáº©n bá»‹ Ä‘áº©y lÃªn
            if (!empty($headers) && empty($idMap)) {
                array_unshift($appendData, $headers);
            }

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
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Sheets API Error - Upsert Rows', [
                'error' => $e->getMessage(),
                'row_count' => count($dataRows),
                'sheet' => $sheetName
            ]);
            throw new \Exception('Failed to upsert data to Google Sheets: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in upsertRows', [
                'error' => $e->getMessage(),
                'sheet' => $sheetName
            ]);
            throw $e;
        }
    }

    // ==========================================
    // TÃNH NÄ‚NG Äá»ŠNH Dáº NG: Ã‰p kiá»ƒu hiá»ƒn thá»‹ chá»¯ thÃ nh "Clip" (Cáº¯t bá»›t)
    // ==========================================
    public function formatColumnsAsClip(string $sheetName, int $startColIndex, int $endColIndex)
    {
        // ðŸŸ¢ FIX N+1: Láº¥y ID tá»« Cache cá»±c nhanh
        $sheetId = $this->getSheetIdByName($sheetName);
        if ($sheetId === null) return;

        // 2. Táº¡o Request Ã©p Ä‘á»‹nh dáº¡ng WrapStrategy thÃ nh CLIP
        $requests = [
            new \Google\Service\Sheets\Request([
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startColumnIndex' => $startColIndex, // Cá»™t báº¯t Ä‘áº§u (TÃ­nh tá»« 0)
                        'endColumnIndex' => $endColIndex,     // Cá»™t káº¿t thÃºc (Exclusive)
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

        // 3. Thá»±c thi lá»‡nh Format
        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }

    // ==========================================
    // TÃNH NÄ‚NG Äá»ŠNH Dáº NG: TÃ¬m Ä‘Ãºng dÃ²ng Ä‘Ã³ vÃ  XÃ³a bá» hoÃ n toÃ n
    // ==========================================
    public function deleteRowsByIds(array $ids, ?string $sheetName = null)
    {
        if (empty($ids)) return;

        try {
            $targetSheet = $sheetName ?? $this->getFirstSheetName();

            // ðŸŸ¢ FIX N+1: Láº¥y ID tá»« Cache cá»±c nhanh
            $sheetId = $this->getSheetIdByName($targetSheet);
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

            // Xáº¿p giáº£m dáº§n Ä‘á»ƒ khi xÃ³a dÃ²ng dÆ°á»›i khÃ´ng lÃ m thay Ä‘á»•i index cá»§a dÃ²ng trÃªn
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

            $result = $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);

            Log::info('Successfully deleted rows from Google Sheets', [
                'sheet' => $targetSheet,
                'deleted_count' => count($indicesToDelete)
            ]);

            return $result;
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Sheets API Error - Delete Rows', [
                'error' => $e->getMessage(),
                'ids_to_delete' => $ids,
                'sheet' => $sheetName
            ]);
            throw new \Exception('Failed to delete rows from Google Sheets: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in deleteRowsByIds', [
                'error' => $e->getMessage(),
                'ids_to_delete' => $ids,
                'sheet' => $sheetName
            ]);
            throw $e;
        }
    }

    // ==========================================
    // CHIá»€U 2: SHEET -> WEB (Äá»c dá»¯ liá»‡u)
    // ==========================================
    public function readSheet($range = 'A2:AC', ?string $sheetName = null)
    {
        try {
            $targetSheet = $sheetName ?? $this->getFirstSheetName();
            $fullRange = "'{$targetSheet}'!{$range}";

            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $fullRange);
            $values = $response->getValues();

            Log::info('Successfully read data from Google Sheets', [
                'sheet' => $targetSheet,
                'range' => $range,
                'row_count' => count($values ?? [])
            ]);

            return $values ?: [];
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Sheets API Error - Read Sheet', [
                'error' => $e->getMessage(),
                'sheet' => $sheetName,
                'range' => $range
            ]);
            throw new \Exception('Failed to read data from Google Sheets: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in readSheet', [
                'error' => $e->getMessage(),
                'sheet' => $sheetName,
                'range' => $range
            ]);
            throw $e;
        }
    }

    // ==========================================


    // Tráº£ vá» Service gá»‘c Ä‘á»ƒ gá»i lá»‡nh BatchUpdate
    public function getService()
    {
        return $this->service;
    }

    // Tráº£ vá» ID cá»§a Spreadsheet Ä‘ang dÃ¹ng
    public function getSpreadsheetId()
    {
        return $this->spreadsheetId;
    }

    // HÃ m kiá»ƒm tra vÃ  tá»± táº¡o Tab náº¿u chÆ°a cÃ³
    public function createSheetIfNotExist(string $sheetName)
    {
        // ðŸŸ¢ Äá»c tá»« Cache thay vÃ¬ gá»i API
        $sheets = $this->getCachedSheetInfo();

        if (array_key_exists($sheetName, $sheets)) {
            return; // ÄÃ£ tá»“n táº¡i, thoÃ¡t ra
        }

        // Náº¿u chÆ°a cÃ³, gá»­i lá»‡nh táº¡o má»›i
        $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [
                new \Google\Service\Sheets\Request([
                    'addSheet' => ['properties' => ['title' => $sheetName]]
                ])
            ]
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);

        // ðŸŸ¢ QUAN TRá»ŒNG: Vá»«a táº¡o Tab má»›i xong thÃ¬ pháº£i Ä‘áº­p bá» Cache cÅ© Ä‘á»ƒ nÃ³ láº¥y láº¡i danh sÃ¡ch má»›i
        \Illuminate\Support\Facades\Cache::forget('sheet_info_' . $this->spreadsheetId);
    }

    /**
     * Tá»± Ä‘á»™ng tÃ´ mÃ u cáº£ hÃ ng dá»±a trÃªn giÃ¡ trá»‹ cá»§a cá»™t Status
     * TÃ´ mÃ u theo quy táº¯c linh hoáº¡t
     * $rules: Máº£ng chá»©a [ 'TÃªn tráº¡ng thÃ¡i' => [mÃ u RGB] ]
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
     * ðŸŸ¢ HÃ€M Má»šI: Láº¥y danh sÃ¡ch Sheet lÆ°u vÃ o Cache 60 phÃºt
     */
    private function getCachedSheetInfo()
    {
        $cacheKey = 'sheet_info_' . $this->spreadsheetId;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            $info = [];
            foreach ($spreadsheet->getSheets() as $sheet) {
                // LÆ°u thÃ nh máº£ng: ['TÃªn Tab' => ID_Cá»§a_Tab]
                $info[$sheet->getProperties()->getTitle()] = $sheet->getProperties()->getSheetId();
            }
            return $info;
        });
    }

    /**
     * ðŸŸ¢ Láº¥y ID tá»« Cache thay vÃ¬ gá»i API liÃªn tá»¥c
     */
    private function getSheetIdByName(string $sheetName)
    {
        $sheets = $this->getCachedSheetInfo();

        if (!isset($sheets[$sheetName])) {
            throw new \Exception("Tab not found: {$sheetName}");
        }

        return $sheets[$sheetName];
    }

    // --- Káº¾T THÃšC ---

    // ==========================================
}
