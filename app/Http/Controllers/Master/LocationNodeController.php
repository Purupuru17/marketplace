<?php

namespace App\Http\Controllers\Master;

use App\Models\LocationNode;
use App\Services\Master\LocationNodeService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;

class LocationNodeController extends BaseCoreController
{
    private $module = 'master.location-node';

    public function __construct(protected LocationNodeService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'sortable' => true, 'searchable' => true, 'html' => true, 'align' => 'left'],
            ['key' => 'lat', 'label' => 'Latitude', 'sortable' => true, 'align' => 'right'],
            ['key' => 'lng', 'label' => 'Longitude', 'sortable' => true, 'align' => 'right'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Node Lokasi',
            'subtitle' => 'Data Node Lokasi',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Node Lokasi']],

            'columns' => $columns,
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,

            'title' => 'Tambah Node Lokasi',
            'subtitle' => 'Titik lokasi untuk perhitungan ongkir',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Node Lokasi', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Node lokasi berhasil ditambahkan.');
    }

    public function edit(LocationNode $locationNode)
    {
        $compact = [
            'formData' => $locationNode,

            'title' => 'Edit Node Lokasi',
            'subtitle' => 'Titik lokasi untuk perhitungan ongkir',

            'action' => route($this->module.'.update', $locationNode->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Node Lokasi', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, LocationNode $locationNode)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->update($locationNode, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Node lokasi berhasil diperbarui.');
    }

    public function destroy(LocationNode $locationNode)
    {
        $this->service->delete($locationNode);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Node lokasi berhasil dihapus.');
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
            LocationNode::query(),
            ['name'],
            null,
            function (LocationNode $item) {
                return [
                    'id' => $item->id,
                    'name' => '<div class="flex items-center gap-3">'
                        .'<div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">'
                        .svg('heroicon-o-map-pin', 'h-5 w-5')->toHtml()
                        .'</div>'
                        .'<div><p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p></div>'
                        .'</div>',
                    'name_plain' => $item->name,
                    'lat' => $item->lat ?? '-',
                    'lng' => $item->lng ?? '-',
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'lat', 'lng', 'status']
        );
    }
}
