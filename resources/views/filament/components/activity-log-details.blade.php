<div class="p-4 space-y-4">
    @php
        $properties = $properties ?? [];
        $old = $properties['old'] ?? null;
        $new = $properties['attributes'] ?? null;
    @endphp

    @if($old)
        <div>
            <h3 class="text-xs font-bold text-danger-600 uppercase tracking-wider mb-2 flex items-center gap-1">
                <x-heroicon-m-minus-circle class="w-4 h-4" />
                Old Data (Trước khi sửa)
            </h3>
            <div class="bg-danger-50 p-3 rounded-lg border border-danger-100 overflow-x-auto dark:bg-danger-900/20 dark:border-danger-800">
                <pre class="text-xs text-danger-700 dark:text-danger-400 font-mono">@json($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
            </div>
        </div>
    @endif

    @if($new)
        <div>
            <h3 class="text-xs font-bold text-success-600 uppercase tracking-wider mb-2 flex items-center gap-1">
                <x-heroicon-m-plus-circle class="w-4 h-4" />
                New Data / Changes (Sau khi sửa)
            </h3>
            <div class="bg-success-50 p-3 rounded-lg border border-success-100 overflow-x-auto dark:bg-success-900/20 dark:border-success-800">
                <pre class="text-xs text-success-700 dark:text-success-400 font-mono">@json($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
            </div>
        </div>
    @endif
    
    @if(!$old && !$new && !empty($properties))
         <div>
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Properties</h3>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 overflow-x-auto dark:bg-gray-900/50 dark:border-gray-800">
                <pre class="text-xs text-gray-700 dark:text-gray-400 font-mono">@json($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
            </div>
        </div>
    @endif

    @if(empty($properties))
        <div class="text-center py-4 text-gray-400 text-sm">
            No detailed properties recorded for this action.
        </div>
    @endif
</div>
