<?php

namespace App\Http\Controllers\Master;

use App\Models\CustomerLevel;
use App\Services\Master\CustomerLevelService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class CustomerLevelController extends BaseCoreController
{
    private $module = 'master.customer-level';

    public function __construct(protected CustomerLevelService $service) {}

    public function index(Request $request)
    {
        $customerLevels = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData'   => $customerLevels,

            'title'      => 'Customer Level',
            'subtitle'   => 'Data Customer Level',

            'module'     => $this->module,
            'rolesName'  => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Customer Level']],
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData'   => null,

            'title'      => 'Tambah Customer Level',
            'subtitle'   => 'Atur level customer marketplace',

            'action'     => route($this->module.'.store'),
            'module'     => $this->module,
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
            'formData'   => $customerLevel,

            'title'      => 'Edit Customer Level',
            'subtitle'   => 'Atur level customer marketplace',
            
            'action'     => route($this->module.'.update', $customerLevel->id),
            'module'     => $this->module,
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
}
