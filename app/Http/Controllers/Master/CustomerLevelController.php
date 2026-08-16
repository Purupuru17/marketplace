<?php

namespace App\Http\Controllers\Master;

use App\Models\CustomerLevel;
use App\Services\Master\CustomerLevelService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerLevelController extends BaseCoreController
{
    private $module = 'master.customer-level';

    public function __construct(protected CustomerLevelService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'sortable' => true, 'searchable' => true, 'align' => 'left'],
            ['key' => 'minimum_points', 'label' => 'Min Poin', 'sortable' => true, 'align' => 'right'],
            ['key' => 'benefit', 'label' => 'Benefit', 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Customer Level',
            'subtitle' => 'Data Customer Level',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Customer Level']],

            'columns' => $columns,
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,

            'title' => 'Tambah Customer Level',
            'subtitle' => 'Atur level customer marketplace',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Customer Level', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'minimum_points' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'benefit' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Customer level berhasil ditambahkan.');
    }

    public function edit(CustomerLevel $customerLevel)
    {
        $compact = [
            'formData' => $customerLevel,

            'title' => 'Edit Customer Level',
            'subtitle' => 'Atur level customer marketplace',

            'action' => route($this->module.'.update', $customerLevel->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Customer Level', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, CustomerLevel $customerLevel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'minimum_points' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'benefit' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->update($customerLevel, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Customer level berhasil diperbarui.');
    }

    public function destroy(CustomerLevel $customerLevel)
    {
        $this->service->delete($customerLevel);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Customer level berhasil dihapus.');
    }

    public function ajax(Request $request)
    {
        $type = $request->input('type');
        $source = $request->input('source');

        return match ($type) {
            'table' => match ($source) {
                'index' => $this->tableIndex($request),
                default => response()->json(['status' => 'error', 'message' => 'Sumber data tidak valid.'], 400),
            },
            default => response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400),
        };
    }

    private function tableIndex(Request $request)
    {
        return DataTableService::process(
            $request,
            CustomerLevel::query(),
            ['name'],
            null,
            function (CustomerLevel $item) {
                return [
                    'id' => $item->id,
                    'name' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">Urutan: '.$item->sort_order.'</p>',
                    'name_plain' => $item->name,
                    'minimum_points' => number_format($item->minimum_points, 0, ',', '.'),
                    'benefit' => Str::limit($item->benefit ?? '-', 40),
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'minimum_points', 'sort_order', 'status']
        );
    }
}
