@props(['monitor' => null, 'servers' => collect()])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
        <input type="text" name="name" value="{{ old('name', $monitor?->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Host/IP *</label>
        <input type="text" name="host" value="{{ old('host', $monitor?->host) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="ex: 192.168.1.1">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Porta *</label>
        <input type="number" name="port" value="{{ old('port', $monitor?->port ?? 80) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
        <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="ping" {{ old('type', $monitor?->type) === 'ping' ? 'selected' : '' }}>Ping (ICMP)</option>
            <option value="http" {{ old('type', $monitor?->type) === 'http' ? 'selected' : '' }}>HTTP</option>
            <option value="tcp" {{ old('type', $monitor?->type) === 'tcp' ? 'selected' : '' }}>TCP</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo (segundos) *</label>
        <input type="number" name="interval_seconds" value="{{ old('interval_seconds', $monitor?->interval_seconds ?? 60) }}" min="10" max="3600" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik</label>
        <select name="server_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Nenhum</option>
            @foreach($servers as $server)
            <option value="{{ $server->id }}" {{ old('server_id', $monitor?->server_id) == $server->id ? 'selected' : '' }}>{{ $server->name }}</option>
            @endforeach
        </select>
    </div>
    @if($monitor)
    <div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $monitor->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
            <span class="text-sm text-gray-700">Monitor ativo</span>
        </label>
    </div>
    @endif
</div>
<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('crm.uptime.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
</div>
