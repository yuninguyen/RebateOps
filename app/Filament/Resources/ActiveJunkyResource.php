<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActiveJunkyResource\Pages;
use App\Filament\Resources\ActiveJunkyResource\RelationManagers;
use App\Models\Account;
use DateTime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActiveJunkyResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Quản lý tài nguyên';
    protected static ?string $navigationLabel = 'Active Junky';
    protected static ?string $navigationParentItem = 'Accounts';
    protected static ?int $navigationSort = 4;
    // Thêm dòng này để thu gọn menu bên trái, nhường chỗ cho bảng
    protected static bool $isScopedToTenant = false;
    // THÊM DÒNG NÀY: Đổi đường dẫn URL từ /Active Junky thành /Active Junky
    protected static ?string $slug = 'active-junky';

    // HÀM LỌC DỮ LIỆU: Chỉ lấy tài khoản của Active Junky
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('platform', 'Active Junky');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Active Junky Account Details')
                    ->schema([
                        // Sử dụng lại các trường từ AccountResource
                        Forms\Components\Hidden::make('platform')
                            ->default('Active Junky'),

                        Forms\Components\Select::make('email_id')
                            ->label('Email Address')
                            ->placeholder('Chọn email đã tạo trước đó')
                            ->relationship('email', 'email') // Liên kết với bảng email
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([ // Nút (+) tạo nhanh Email mới
                                Forms\Components\TextInput::make('email')->email()->required(),
                                Forms\Components\TextInput::make('email_password')->required(),
                                Forms\Components\TextInput::make('recovery_email')->email(),
                                Forms\Components\TextInput::make('two_factor_code')
                                    ->label('2FA Code / Recovery Code')
                                    ->placeholder('Nhập mã 2FA hoặc mã khôi phục...'),
                                Forms\Components\Select::make('provider')
                                    ->label('Email Provider')
                                    ->placeholder('Ví dụ: Gmail, Outlook...')
                                    ->options([
                                        'gmail' => 'Gmail',
                                        'outlook' => 'Outlook',
                                        'yahoo' => 'Yahoo',
                                        'aol' => 'AOL',
                                        'other' => 'Other',
                                    ]),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Active',
                                        'disabled' => 'Disabled',
                                        'suspended' => 'Suspended',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->native(false),

                                // ĐÂY LÀ CỘT NOTE BẠN YÊU CẦU
                                Forms\Components\Textarea::make('email_note')
                                    ->label('Email Note')
                                    ->placeholder('Lưu ý riêng cho email này...'),
                            ]),

                        Forms\Components\TextInput::make('password')
                            ->password() // Tự động ẩn mật khẩu khi nhập
                            ->revealable() // Thêm icon con mắt để bấm xem
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->multiple() // Cho phép chọn nhiều cái cùng lúc
                            ->options([
                                'active' => 'Active',
                                'used' => 'In Use',
                                'banned' => 'Banned',
                                'not_linked' => 'Chưa link PayPal',
                                'limited' => 'PayPal Limited',
                                'linked' => 'Linked PayPal',
                                'unlinked' => 'Unlinked PayPal',
                                'no_paypal_needed' => 'Không cần link PayPal',
                            ])
                            ->searchable()
                            ->preload()
                            ->native(false) // Dùng giao diện hiện đại của Filament
                            ->required(),

                        // --- CHÈN ĐOẠN MÃ CỦA BẠN VÀO ĐÂY ---
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Holder')
                            ->searchable()
                            ->preload(),

                        //Thêm ngày tạo tài khoản
                        Forms\Components\DatePicker::make('created_at')
                            ->label('Date Created')
                            ->placeholder('Chọn ngày tạo account dd/mm/yyyy')
                            ->displayFormat('d/m/Y') // Định dạng hiển thị khi nhập
                            ->format('Y-m-d') // Định dạng chuẩn để lưu vào MySQL
                            ->native(true) // Dùng giao diện hiện đại của Filament
                            ->dehydrated(true) // Đảm bảo trường này được gửi về backend
                            ->required()
                            ->live(),  // Đồng bộ dữ liệu ngay lập tức,

                        //Thêm ngày linked paypal
                        Forms\Components\DatePicker::make('updated_at')
                            ->label('Date Linked PayPal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d') // Định dạng chuẩn để lưu vào MySQL
                            ->native(true)
                            ->placeholder('Chọn ngày linked PayPal dd/mm/yyyy'),

                        Forms\Components\Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Nhập lưu ý đặc biệt cho tài khoản này...')
                            ->columnSpanFull(), // Chiếm trọn 2 cột nếu bạn đang chia grid
                        // -----------------------------------
                    ])
                    ->columns(2) // Chia làm 2 cột cho đẹp
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Hiển thị nền tảng (Rakuten, PayPal...)
                // Ẩn cột Platform khỏi giao diện bảng để tăng diện tích
                // Không khai báo TextColumn cho platform ở đây.
                //TextColumn::make('platform')
                //->label('Platform')
                //->alignment(Alignment::Center)
                //->searchable(),

                // HIỂN THỊ EMAIL TỪ BẢNG LIÊN KẾT
                // Lấy từ quan hệ email() trong Model Account
                TextColumn::make('email.email')
                    ->label('Email Address')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Copied email to clipboard!')
                    ->html()
                    ->formatStateUsing(function (Account $record): string {
                        $email = $record->email?->email ?? 'N/A';
                        $pass = $record->email?->email_password ?? 'N/A';
                        $rec = $record->email?->recovery_email ?? 'None';
                        $twoFA = $record->email?->two_factor_code ?? 'No Code';
                        $emailNote = $record->email?->note ?? 'None'; // Lấy Note từ bảng Email

                        $html = "<div style= 'text-align: left; line-height: 1.6;'>";
                        $html .= "<div class='text-base font-bold text-gray-700' style='font-size: 13px !important; line-height: 1.6;'>{$email}</div>";
                        $html .= "<div class='text-sm text-gray-600' style='font-size: 11px !important; line-height: 1.6;'><span>Email Password:</span> {$pass}</div>";
                        $html .= "<div class='text-sm text-gray-600' style='font-size: 11px !important; line-height: 1.6;'><span>Email Recovery:</span> {$rec}</div>";
                        $html .= "<div class='text-sm text-gray-600' style='font-size: 11px !important; line-height: 1.6;'><span class='font-medium'>2FA:</span> {$twoFA}</div>";

                        if ($emailNote) {
                            $html .= "<div class='text-sm text-blue-500 mt-1 border-t border-gray-100 pt-1' style='font-size: 11px !important; max-width: 500px;'>";
                            $html .= "<span class='font-bold not-italic text-blue-500'>Email Note:</span> {$emailNote}";
                            $html .= "</div>";
                        }
                        $html .= "</div>";

                        return $html;
                    }),

                // Hiển thị Password và cho phép Click để Copy
                TextColumn::make('password')
                    ->label('Password Platform')
                    ->alignment(Alignment::Center)
                    ->width('100px')
                    ->copyable() // Nhân viên click vào là copy được ngay
                    ->copyMessage('Đã sao chép mật khẩu')
                    ->copyMessageDuration(1500),

                // Hiển thị trạng thái bằng Badge màu xám tối giản
                TextColumn::make('status')
                    ->badge()
                    ->alignment(Alignment::Center)
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'gray',    // Màu xám nhạt
                        'used'   => 'info',    // Màu xanh dương nhạt
                        'no_paypal_needed' => 'warning', // Màu xanh dương đậm
                        'not_linked' => 'warning', // Màu vàng nhạt
                        'linked' => 'success', // Màu xanh lá nhạt
                        'limited' => 'danger',   // Màu đỏ đậm
                        'unlinked' => 'warning', // Màu vàng nhạt
                        'banned' => 'danger',  // Màu đỏ đậm
                        default  => 'gray',
                    })
                    ->separator(',') // Hiển thị các nhãn cách nhau bằng dấu phẩy
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'used'   => 'In Use', // Đổi nhãn used thành In Use cho rõ nghĩa hơn
                        'limited' => 'PayPal Limited', // Đổi riêng nhãn limited 
                        'linked'  => 'Linked PayPal', // Đổi nhãn linked cho rõ ràng
                        'unlinked'  => 'Unlinked PayPal', // Đổi nhãn unlinked
                        'not_linked' => 'Chưa link PayPal', // Đổi nhãn not_linked
                        'no_paypal_needed' => 'Không cần link PayPal', // Đổi nhãn no_paypal_needed
                        default   => ucfirst($state), // Các nhãn khác chỉ viết hoa chữ cái đầu
                    })                    ->tooltip(function (Tables\Columns\TextColumn $column, Account $record): string {
                        // Lấy mảng các trạng thái hiện có của tài khoản
                        $statuses = is_array($record->status) ? $record->status : [$record->status];
                        $explanations = [
                            'active' => 'Tài khoản mới tạo, sẵn sàng để gán cho Holder.',
                            'used' => 'Tài khoản đang trong quá trình sử dụng.',
                            'limited' => 'PayPal bị giới hạn (Limited), cần gỡ hoặc kiểm tra.',
                            'banned' => 'Tài khoản đã bị khóa, không thể sử dụng tiếp.',
                            'linked' => 'Đã liên kết thành công với PayPal Account.',
                            'unlinked' => 'Đã gỡ liên kết PayPal khỏi tài khoản này.',
                            'not_linked' => 'Tài khoản chưa được liên kết với PayPal.',
                            'no_paypal_needed' => 'Không yêu link PayPal.',
                        ];

                        // Duyệt qua các trạng thái hiện có và lấy giải thích tương ứng
                        return collect($statuses)
                            ->map(fn($s) => ($explanations[$s] ?? 'Trạng thái hệ thống'))
                            ->join("\n"); // Các giải thích sẽ xuống dòng nếu có nhiều badge

                    }),

                //Hiển thị ghi chú (note) nếu có, dưới dạng chữ nhỏ màu xanh dương
                TextColumn::make('note')
                    ->label('Note')
                    ->alignment(Alignment::Center)
                    ->color('blue')
                    ->size('sm')
                    ->limit(30) // TỰ ĐỘNG HIỆN ... nếu quá 30 ký tự
    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
        $state = $column->getState();
        if (strlen($state) <= 30) {
            return null;
        }
        return $state; // Hiện đầy đủ nội dung khi rà chuột vào
    })
    ->wrap(false), // Không cho xuống dòng để giữ hàng lối thẳng đẹp

                // Hiển thị người đang giữ tài khoản
                TextColumn::make('user.name')
                    ->label('Holder')
                    ->placeholder('N/A')
                    ->alignment(Alignment::Center),

                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime('d/m/Y') // Sửa lỗi formatdateTime không tồn tại
                    ->alignment(Alignment::Center)
                    ->width('120px')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Date Linked PayPal')
                    ->dateTime('d/m/Y') // Sửa lỗi formatdateTime không tồn tại
                    ->alignment(Alignment::Center)
                    ->width('120px')
                    ->sortable()
                    ->placeholder('-') // Thêm placeholder nếu chưa có ngày linked,
            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),

                    Tables\Actions\Action::make('copy_full_info')
                        ->label('Copy Full Info')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->action(function ($record, $livewire) {

                            //  Khai báo Header
                            $header = "Email Address | Email Password | Recovery Email | 2FA Code | Email Note | Platform | Password Platform | Status | Account Platform Note | Holder | Date Created | Date Linked PayPal |";
                            
                            // Lấy thông tin từ Email gốc
                            $email = $record->email?->email ?? 'N/A';
                            $emailPass = $record->email?->email_password ?? 'N/A';
                            $recovery = $record->email?->recovery_email ?? 'None';
                            $twoFA = $record->email?->two_factor_code ?? 'No Code';
                            $emailNote = $record->email?->note ?? 'None';

                            // Lấy thông tin từ Account (Platform)
                            $platform = $record->platform ?? 'Rakuten';
                            $platformPass = $record->password ?? 'N/A';
                            $status = $record->status ? implode(', ', (array)$record->status) : 'None';
                            $statusLabels = [
                                'active' => 'Active',
                                'used' => 'In Use',
                                'limited' => 'PayPal Limited',
                                'banned' => 'Banned',
                                'linked' => 'Linked PayPal',
                                'unlinked' => 'Unlinked PayPal',
                                'not_linked' => 'Chưa link PayPal',
                                'no_paypal_needed' => 'Không cần link PayPal',
                            ];
                            // Chuyển mảng status (nếu có nhiều status) thành chuỗi nhãn đẹp
                            $currentStatuses = is_array($record->status) ? $record->status : explode(',', (string)$record->status);

                            $formattedStatus = collect($currentStatuses)
                                ->map(function ($s) use ($statusLabels) {
                                    $key = trim($s); // Loại bỏ khoảng trắng thừa nếu có
                                    return $statusLabels[$key] ?? ucfirst($key);
                                })
                                ->join(', '); // Kết quả: "Active, Chưa link PayPal"

                            $holder = $record->user?->name ?? 'N/A';
                            $note = $record->note ?? 'None';
                            $dateCreated = $record->created_at ? $record->created_at->format('d/m/Y') : 'N/A';

                            // Định dạng Ngày Linked PayPal (Nếu trống thì hiện "N/A")
                            $dateLinked = $record->updated_at ? $record->updated_at->format('d/m/Y') : 'N/A';

                            // ĐỊNH DẠNG 1: Tất cả trên 1 dòng (Ngăn cách bằng dấu |)
                            $singleLine = "{$email} | {$emailPass} | {$recovery} | {$twoFA} | {$emailNote} | {$record->platform} | {$record->password} | {$formattedStatus} | {$note} | {$holder} | {$dateCreated} | {$dateLinked} |\n";
                            $finalSingleLine = $header . "\n" . $singleLine; // Kết hợp header và data

                            // ĐỊNH DẠNG 2: Chia thành nhiều dòng chi tiết
                            $multiLine = "Email Address: {$email}\n" .
                                "Email Password: {$emailPass}\n" .
                                "Recovery Email: {$recovery}\n" .
                                "2FA Code: {$twoFA}\n" .
                                "Email Note: {$emailNote}\n" .
                                "--------------------------\n" .
                                "Platform: {$record->platform}\n" .
                                "Password Platform: {$record->password}\n" .
                                "Status: {$formattedStatus}\n" .
                                "Account Platform Note: {$note}\n" .
                                "Holder: {$holder}\n" .
                                "Date Created: " . ($record->created_at ? $record->created_at->format('d/m/Y') : 'N/A') . "\n" .
                                "Date Linked PayPal: " . ($record->updated_at ? $record->updated_at->format('d/m/Y') : 'N/A');

                            // Gộp cả 2 định dạng vào 1 lần copy
                            $info = $finalSingleLine . "\n\n" . $multiLine;

                            // Gửi sự kiện để JavaScript thực hiện copy
                            $livewire->dispatch('copy-to-clipboard', text: $info);

                            \Filament\Notifications\Notification::make()
                                ->title('Copied full account info to clipboard!')
                                ->success()
                                ->send();
                        }),
                ])
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\ExportBulkAction::make()
                        ->exporter(\App\Filament\Exports\AccountExporter::class)
                        ->label('Export Selected')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('copy_account_selected')
                        ->label('Copy Account Selected')
                        ->icon('heroicon-m-clipboard-document-list')
                        ->color('warning')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, $livewire) {
                            // 1. Tạo hàng tiêu đề (Header)
                            $header = "Email Address | Email Password | Recovery Email | 2FA Code | Email Note | Platform | Password Platform | Status | Account Platform Note | Holder | Date Created | Date Linked PayPal |";
                            $output = $header . "\n";

                            foreach ($records as $record) {
                                // 2. Thu thập dữ liệu từ quan hệ Email và Account
                                $email = $record->email?->email ?? 'N/A';
                                $emailPass = $record->email?->email_password ?? 'N/A';
                                $recovery = $record->email?->recovery_email ?? 'None';
                                $twoFA = $record->email?->two_factor_code ?? 'No Code';
                                $emailNote = $record->email?->note ?? 'None'; // Email Note từ bảng Email

                                $platform = $record->platform ?? 'N/A';
                                $passPlatform = $record->password ?? 'N/A';

                                $status = $record->status ? implode(', ', (array)$record->status) : 'None';
                                $statusLabels = [
                                    'active' => 'Active',
                                    'used' => 'In Use',
                                    'limited' => 'PayPal Limited',
                                    'banned' => 'Banned',
                                    'linked' => 'Linked PayPal',
                                    'unlinked' => 'Unlinked PayPal',
                                    'not_linked' => 'Chưa link PayPal',
                                    'no_paypal_needed' => 'Không cần link PayPal',
                                ];

                                // XỬ LÝ STATUS CHUẨN: Chuyển "used, linked" thành "In Use, Linked PayPal"
                                $currentStatuses = is_array($record->status) ? $record->status : [$record->status];
                                $formattedStatus = collect($currentStatuses)
                                    ->map(fn($s) => $statusLabels[$s] ?? ucfirst($s))
                                    ->join(', '); // Đổi từ | sang dấu phẩy ở đây

                                $accNote = $record->note ?? 'None'; // Account Note
                                $holder = $record->user?->name ?? 'N/A';
                                $dateCreated = $record->created_at ? $record->created_at->format('d/m/Y') : 'N/A';

                                // 2. Xử lý Ngày (Nếu trống hiện "N/A")
                                $dateLinked = $record->updated_at ? $record->updated_at->format('d/m/Y') : 'N/A';

                                // 3. Gộp thành một dòng dữ liệu
                                $output .= "{$email} | {$emailPass} | {$recovery} | {$twoFA} | {$emailNote} | {$platform} | {$passPlatform} | {$formattedStatus} | {$accNote} | {$holder} | {$dateCreated} | {$dateLinked} |\n";
                            }

                            // Gửi lệnh copy tới trình duyệt
                            $livewire->dispatch('copy-to-clipboard', text: $output);

                            // Thông báo thành công
                            \Filament\Notifications\Notification::make()
                                ->title('Copied ' . $records->count() . ' accounts info to clipboard!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(), // Tự động bỏ chọn sau khi copy xong

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActiveJunkies::route('/'),
            'create' => Pages\CreateActiveJunky::route('/create'),
            'edit' => Pages\EditActiveJunky::route('/{record}/edit'),
        ];
    }
}
