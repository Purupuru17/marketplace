<?php

namespace IdCore\CoreStarter\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class DataTableService
{
    /**
     * Memproses Request Ajax DataTables secara Server-Side.
     */
    public static function process(Request $request, Builder $query, array $searchableColumns, ?callable $extraFilters = null): JsonResponse
    {
        // 1. Eksekusi Extra Filters jika tersedia
        if (is_callable($extraFilters)) {
            $extraFilters($query);
        }

        // 2. Hitung Total Data Asli (Sebelum Filtering)
        $recordsTotal = $query->clone()->count();

        // 3. Global Search (Aman dari SQL Injection via Parameter Binding)
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchableColumns, $searchValue) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $searchValue . '%');
                }
            });
        }

        // 4. Search Per Kolom (Individual Column Search)
        $columns = $request->input('columns', []);
        foreach ($columns as $columnDto) {
            $columnName = $columnDto['data'] ?? '';
            $columnSearch = $columnDto['search']['value'] ?? '';

            if (!empty($columnSearch) && in_array($columnName, $searchableColumns)) {
                $query->where($columnName, 'LIKE', '%' . $columnSearch . '%');
            }
        }

        // 5. Hitung Total Data Setelah Difilter
        $recordsFiltered = $query->clone()->count();

        // 6. Multi-Column Sorting Dinamis
        $orders = $request->input('order', []);
        if (!empty($orders)) {
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

        // 8. Standar Respons JSON DataTables
        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }
}
