<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\UptimeMonitor;
use Modules\CRM\Models\UptimeCheck;
use Modules\Core\Models\Server;
use Illuminate\Support\Facades\Http;

class UptimeController extends Controller
{
    public function index()
    {
        $monitors = UptimeMonitor::with('server')->orderBy('name')->get();

        $stats = [
            'total' => $monitors->count(),
            'up' => $monitors->where('is_up', true)->count(),
            'down' => $monitors->where('is_up', false)->count(),
            'unknown' => $monitors->where('is_up', null)->count(),
        ];

        return view('infra::uptime.index', compact('monitors', 'stats'));
    }

    public function create()
    {
        $servers = Server::orderBy('name')->get();
        return view('infra::uptime.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'type' => 'required|in:http,ping,tcp',
            'interval_seconds' => 'required|integer|min:10|max:3600',
            'server_id' => 'nullable|exists:servers,id',
        ]);

        $validated['is_active'] = true;

        UptimeMonitor::create($validated);

        return redirect()->route('infra.uptime.index')
            ->with('success', 'Monitor criado com sucesso.');
    }

    public function show($id)
    {
        $monitor = UptimeMonitor::with('server')->findOrFail($id);
        $checks = $monitor->checks()->orderByDesc('checked_at')->limit(50)->get();

        $uptime24h = $monitor->checks()
            ->where('checked_at', '>=', now()->subHours(24))
            ->count();
        $up24h = $monitor->checks()
            ->where('checked_at', '>=', now()->subHours(24))
            ->where('is_up', true)
            ->count();
        $uptimePercent = $uptime24h > 0 ? round($up24h / $uptime24h * 100, 2) : null;

        return view('infra::uptime.show', compact('monitor', 'checks', 'uptimePercent'));
    }

    public function edit($id)
    {
        $monitor = UptimeMonitor::findOrFail($id);
        $servers = Server::orderBy('name')->get();
        return view('infra::uptime.edit', compact('monitor', 'servers'));
    }

    public function update(Request $request, $id)
    {
        $monitor = UptimeMonitor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'type' => 'required|in:http,ping,tcp',
            'interval_seconds' => 'required|integer|min:10|max:3600',
            'server_id' => 'nullable|exists:servers,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $monitor->update($validated);

        return redirect()->route('infra.uptime.index')
            ->with('success', 'Monitor atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $monitor = UptimeMonitor::findOrFail($id);
        $monitor->checks()->delete();
        $monitor->delete();

        return redirect()->route('infra.uptime.index')
            ->with('success', 'Monitor removido com sucesso.');
    }

    public function check($id)
    {
        $monitor = UptimeMonitor::findOrFail($id);
        $start = microtime(true);

        try {
            $isUp = false;
            $error = null;

            if ($monitor->type === 'http') {
                $response = Http::timeout(5)->get("http://{$monitor->host}:{$monitor->port}");
                $isUp = $response->successful();
            } elseif ($monitor->type === 'tcp') {
                $fp = @fsockopen($monitor->host, $monitor->port, $errno, $errstr, 5);
                if ($fp) {
                    fclose($fp);
                    $isUp = true;
                } else {
                    $error = "{$errstr} ({$errno})";
                }
            } elseif ($monitor->type === 'ping') {
                $ping = @exec("ping -n 1 -w 3000 {$monitor->host} 2>&1", $output, $returnCode);
                $isUp = $returnCode === 0;
            }

            $responseTime = (int)((microtime(true) - $start) * 1000);

            $monitor->update([
                'is_up' => $isUp,
                'last_check_at' => now(),
                'response_time_ms' => $responseTime,
                'last_error' => $error,
            ]);

            UptimeCheck::create([
                'uptime_monitor_id' => $monitor->id,
                'is_up' => $isUp,
                'response_time_ms' => $responseTime,
                'error_message' => $error,
                'checked_at' => now(),
            ]);

            return back()->with('success', $isUp ? "Monitor {$monitor->name} esta UP" : "Monitor {$monitor->name} esta DOWN");

        } catch (\Exception $e) {
            $monitor->update([
                'is_up' => false,
                'last_check_at' => now(),
                'last_error' => $e->getMessage(),
            ]);

            UptimeCheck::create([
                'uptime_monitor_id' => $monitor->id,
                'is_up' => false,
                'error_message' => $e->getMessage(),
                'checked_at' => now(),
            ]);

            return back()->with('error', "Erro ao verificar {$monitor->name}: " . $e->getMessage());
        }
    }

    public function checkAll()
    {
        $monitors = UptimeMonitor::where('is_active', true)->get();

        foreach ($monitors as $monitor) {
            try {
                $start = microtime(true);
                $isUp = false;
                $error = null;

                if ($monitor->type === 'http') {
                    $response = Http::timeout(5)->get("http://{$monitor->host}:{$monitor->port}");
                    $isUp = $response->successful();
                } elseif ($monitor->type === 'tcp') {
                    $fp = @fsockopen($monitor->host, $monitor->port, $errno, $errstr, 5);
                    if ($fp) { fclose($fp); $isUp = true; } else { $error = "{$errstr} ({$errno})"; }
                } elseif ($monitor->type === 'ping') {
                    exec("ping -n 1 -w 3000 {$monitor->host} 2>&1", $output, $returnCode);
                    $isUp = $returnCode === 0;
                }

                $responseTime = (int)((microtime(true) - $start) * 1000);

                $monitor->update(['is_up' => $isUp, 'last_check_at' => now(), 'response_time_ms' => $responseTime, 'last_error' => $error]);

                UptimeCheck::create([
                    'uptime_monitor_id' => $monitor->id,
                    'is_up' => $isUp,
                    'response_time_ms' => $responseTime,
                    'error_message' => $error,
                    'checked_at' => now(),
                ]);
            } catch (\Exception $e) {
                $monitor->update(['is_up' => false, 'last_check_at' => now(), 'last_error' => $e->getMessage()]);
            }
        }

        return back()->with('success', "{$monitors->count()} monitores verificados.");
    }
}
