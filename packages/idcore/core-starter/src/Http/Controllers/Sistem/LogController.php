<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use App\Models\User;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Models\ActivityLog;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;

class LogController extends BaseCoreController
{
    private $module = 'sistem.log';

    protected static function resourceName(): string
    {
        return 'log';
    }

    public function index()
    {
        $columns = [
            ['key' => 'created_at', 'label' => 'Waktu', 'sortable' => true],
            ['key' => 'user', 'label' => 'User', 'html' => true],
            ['key' => 'event', 'label' => 'Event', 'sortable' => true, 'html' => true],
            ['key' => 'ip', 'label' => 'IP', 'sortable' => true],
            ['key' => 'device', 'label' => 'Device'],
            ['key' => 'description', 'label' => 'Keterangan', 'align' => 'left'],
        ];

        $compact = [
            'users' => User::orderBy('name')->get(['id', 'name']),

            'title' => 'Activity Log',
            'subtitle' => 'Riwayat aktivitas pengguna',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], [ucwords($this->resourceName())]],

            'columns' => $columns,
        ];

        return view('idcore::'.$this->module.'.index', $compact);
    }

    public function destroy(ActivityLog $log)
    {
        $log->delete();

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Log berhasil dihapus.');
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
        $badgeMap = [
            'login' => 'blue',
            'logout' => 'gray',
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
        ];

        return DataTableService::process(
            $request,
            ActivityLog::query()->with('user')->orderByDesc('created_at'),
            [
                fn ($query, $search) => $query->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%'.$search.'%')),
            ],
            function ($query) use ($request) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->input('user_id'));
                }
                if ($request->filled('event')) {
                    $query->where('event', $request->input('event'));
                }
            },
            function (ActivityLog $log) use ($badgeMap) {
                $deviceInfo = '';
                if ($log->user_agent) {
                    $ua = $log->user_agent;
                    $browser = 'Unknown';
                    $os = 'Unknown';

                    if (stripos($ua, 'Firefox') !== false) {
                        $browser = 'Firefox';
                    } elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false) {
                        $browser = 'Chrome';
                    } elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
                        $browser = 'Safari';
                    } elseif (stripos($ua, 'Edg') !== false) {
                        $browser = 'Edge';
                    } elseif (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident') !== false) {
                        $browser = 'IE';
                    }

                    if (stripos($ua, 'Windows') !== false) {
                        $os = 'Windows';
                    } elseif (stripos($ua, 'Mac') !== false) {
                        $os = 'macOS';
                    } elseif (stripos($ua, 'Linux') !== false) {
                        $os = 'Linux';
                    } elseif (stripos($ua, 'Android') !== false) {
                        $os = 'Android';
                    } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
                        $os = 'iOS';
                    }

                    $deviceInfo = $browser.' / '.$os;
                }

                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at->format('d M Y H:i:s'),
                    'user' => $log->user
                        ? '<p class="font-semibold text-gray-900 dark:text-white">'.e($log->user->name).'</p>'
                            .'<p class="text-xs text-gray-500 dark:text-gray-400">'.e($log->user->email).'</p>'
                        : '<span class="text-gray-400">-</span>',
                    'event' => Render::badge($badgeMap[$log->event] ?? 'gray', ucfirst($log->event)),
                    'event_plain' => $log->event,
                    'ip' => $log->ip_address ?? '-',
                    'device' => $deviceInfo
                        ? '<span class="text-xs text-gray-700 dark:text-gray-300">'.e($deviceInfo).'</span>'
                        : '<span class="text-gray-400">-</span>',
                    'description' => '<p class="whitespace-pre-line text-gray-700 dark:text-gray-300">'.e($log->description ?? '-').'</p>',
                    'name_plain' => $log->description,
                    
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $log->id) : null,
                    'edit_url' => null,
                ];
            },
            ['created_at', 'user', 'event']
        );
    }
}
