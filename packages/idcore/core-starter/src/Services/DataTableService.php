<?php

namespace IdCore\CoreStarter\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTableService
{
    /**
     * Memproses Request Ajax DataTables secara Server-Side.
     *
     * @param  callable(Builder):void|null  $extraFilters
     * @param  callable(mixed):array|null  $formatter
     */
    public static function process(
        Request $request, 
        Builder $query, 
        array $searchableColumns, 
        ?callable $extraFilters = null, 
        ?callable $formatter = null
        ): JsonResponse {
            
        // 1. Eksekusi Extra Filters jika tersedia
        if (is_callable($extraFilters)) {
            $extraFilters($query);
        }

        // 2. Hitung Total Data Asli (Sebelum Filtering)
        $recordsTotal = $query->clone()->count();

        // 3. Global Search (Aman dari SQL Injection via Parameter Binding)
        $searchValue = trim((string) $request->input('search.value', ''));
        if (! empty($searchValue) && mb_strlen($searchValue) >= 3) {
            $query->where(function ($q) use ($searchableColumns, $searchValue) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'LIKE', '%'.$searchValue.'%');
                }
            });
        }

        // 4. Search Per Kolom (Individual Column Search)
        $columns = $request->input('columns', []);
        foreach ($columns as $columnDto) {
            $columnName = $columnDto['data'] ?? '';
            $columnSearch = $columnDto['search']['value'] ?? '';

            if (! empty($columnSearch) && mb_strlen($columnSearch) >= 3 && in_array($columnName, $searchableColumns)) {
                $query->where($columnName, 'LIKE', '%'.$columnSearch.'%');
            }
        }

        // 5. Hitung Total Data Setelah Difilter
        $recordsFiltered = $query->clone()->count();

        // 6. Multi-Column Sorting Dinamis
        $orders = $request->input('order', []);
        if (! empty($orders)) {
            foreach ($orders as $order) {
                $columnIndex = $order['column'];
                $columnName = $columns[$columnIndex]['data'] ?? null;
                $direction = $order['dir'] === 'desc' ? 'desc' : 'asc';

                if ($columnName && in_array($columnName, $searchableColumns)) {
                    $query->orderBy($columnName, $direction);
                }
            }
        }

        // 7. Pagination Server-Side via Limit & Offset
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        // Proteksi limit negatif DataTables (-1 berarti tampilkan semua data)
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->get();

        // 8. Transformasi Baris via Formatter jika disediakan
        if (is_callable($formatter)) {
            $data = $data->map($formatter)->values();
        }

        // 9. Standar Respons JSON DataTables
        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }


     /**
     * Versi cursor pagination — untuk tabel besar (ratusan ribu+ row).
     * Tidak ada COUNT(*) & tidak ada OFFSET, jadi stabil di kedalaman berapa pun.
     * Trade-off: frontend gak bisa nampilin nomor halaman total, cuma next/prev.
     *
     * @param  callable(Builder):void|null  $extraFilters
     * @param  callable(mixed):array|null  $formatter
     */
    public static function processCursor(
        Request $request,
        Builder $query,
        array $searchableColumns,
        ?callable $extraFilters = null,
        ?callable $formatter = null
    ): JsonResponse {
        // 1. Extra filters (sama kayak process())
        if (is_callable($extraFilters)) {
            $extraFilters($query);
        }

        // 2. Search — FULLTEXT, bukan LIKE %value% (hindari leading wildcard scan)
        $searchValue = trim((string) $request->input('search', ''));
        if ($searchValue !== '') {
            if (mb_strlen($searchValue) >= 3) {
                // Keyword pendek: FULLTEXT skip ini, fallback ke LIKE
                $query->where(function ($q) use ($searchableColumns, $searchValue) {
                    foreach ($searchableColumns as $column) {
                        $q->orWhere($column, 'LIKE', '%'.$searchValue.'%'); // prefix match, bukan %keyword% — index masih kepake kalau ada index biasa di kolom itu
                    }
                });
            } else {
                $columnsStr = implode(',', $searchableColumns);
                $boolean = '+'.str_replace(' ', '* +', $searchValue).'*';
                $query->whereRaw("MATCH({$columnsStr}) AGAINST (? IN BOOLEAN MODE)", [$boolean]);
            }
        }

        // 3. Sorting — whitelist kolom, wajib ada tiebreaker unik (id) biar cursor stabil
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_dir', 'desc'));
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'desc';

        if ($sortBy !== 'id' && ! in_array($sortBy, $searchableColumns)) {
            $sortBy = 'id';
        }

        $query->orderBy($sortBy, $sortDir);
        if ($sortBy !== 'id') {
            $query->orderBy('id', $sortDir);
        }

        // 4. Cursor pagination — Laravel otomatis baca param 'cursor' dari request
        $perPage = (int) $request->input('per_page', 20);
        $perPage = min(max($perPage, 1), 100); // guard batas atas

        $result = $query->cursorPaginate($perPage);

        // 5. Transformasi row via formatter (sama pola kayak process())
        $data = $result->getCollection();
        if (is_callable($formatter)) {
            $data = $data->map($formatter)->values();
        }

        // 6. Respons — tanpa recordsTotal/recordsFiltered (gak ada COUNT sama sekali)
        return response()->json([
            'data' => $data,
            'next_cursor' => $result->nextCursor()?->encode(),
            'prev_cursor' => $result->previousCursor()?->encode(),
            'has_more' => $result->hasMorePages(),
            'per_page' => $perPage,
        ]);
    }
}
