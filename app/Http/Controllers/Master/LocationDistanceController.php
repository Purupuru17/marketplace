<?php

namespace App\Http\Controllers\Master;

use App\Models\LocationDistance;
use App\Services\Master\LocationDistanceService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use Illuminate\Http\Request;

class LocationDistanceController extends BaseCoreController
{
    private $module = 'master.location-distance';

    public function __construct(protected LocationDistanceService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'route', 'label' => 'Rute', 'html' => true, 'align' => 'left'],
            ['key' => 'distance_km', 'label' => 'Jarak (km)', 'sortable' => true, 'html' => true, 'align' => 'right'],
        ];

        $compact = [
            'title' => 'Jarak Antar Node',
            'subtitle' => 'Data Jarak Antar Node',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Jarak Antar Node']],

            'columns' => $columns,
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'nodeOptions' => $this->service->nodeOptions(),

            'title' => 'Tambah Jarak Antar Node',
            'subtitle' => 'Jarak antar node untuk grafik ongkir',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Jarak Antar Node', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin_node_id' => ['required', 'uuid', 'exists:location_nodes,id'],
            'destination_node_id' => ['required', 'uuid', 'exists:location_nodes,id', 'different:origin_node_id'],
            'distance_km' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
        ]);

        if ($this->service->pairExists($validated['origin_node_id'], $validated['destination_node_id'])) {
            return back()
                ->withErrors(['destination_node_id' => 'Jarak antar kedua node ini sudah tercatat.'])
                ->withInput();
        }

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Jarak antar node berhasil ditambahkan.');
    }

    public function edit(LocationDistance $locationDistance)
    {
        $locationDistance->load(['origin', 'destination']);

        $compact = [
            'formData' => $locationDistance,
            'nodeOptions' => $this->service->nodeOptions(),

            'title' => 'Edit Jarak Antar Node',
            'subtitle' => 'Jarak antar node untuk grafik ongkir',

            'action' => route($this->module.'.update', $locationDistance->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Jarak Antar Node', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, LocationDistance $locationDistance)
    {
        $validated = $request->validate([
            'origin_node_id' => ['required', 'uuid', 'exists:location_nodes,id'],
            'destination_node_id' => ['required', 'uuid', 'exists:location_nodes,id', 'different:origin_node_id'],
            'distance_km' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
        ]);

        if ($this->service->pairExists($validated['origin_node_id'], $validated['destination_node_id'], $locationDistance->id)) {
            return back()
                ->withErrors(['destination_node_id' => 'Jarak antar kedua node ini sudah tercatat.'])
                ->withInput();
        }

        $this->service->update($locationDistance, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Jarak antar node berhasil diperbarui.');
    }

    public function destroy(LocationDistance $locationDistance)
    {
        $this->service->delete($locationDistance);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Jarak antar node berhasil dihapus.');
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
            LocationDistance::with(['origin', 'destination']),
            [
                fn ($query, $search) => $query->orWhereHas('origin', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('destination', fn ($q) => $q->where('name', 'like', '%'.$search.'%')),
            ],
            null,
            function (LocationDistance $item) {
                $routeName = $item->origin->name.' - '.$item->destination->name;

                return [
                    'id' => $item->id,
                    'route' => '<div class="flex items-center gap-3">'
                        .'<span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">'.e($item->origin->name).'</span>'
                        .svg('heroicon-o-arrow-right', 'h-4 w-4 text-gray-400')->toHtml()
                        .'<span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">'.e($item->destination->name).'</span>'
                        .'</div>',
                    'route_plain' => $routeName,
                    'name_plain' => $routeName,
                    'distance_km' => '<span class="font-semibold text-gray-900 dark:text-white">'.$item->distance_km.' km</span>',
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['distance_km']
        );
    }
}
