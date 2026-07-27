@props(['coupon' => null, 'servers' => collect()])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Codigo *</label>
        <input type="text" name="code" value="{{ old('code', $coupon?->code) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono" placeholder="Ex: HOTSPOT-ABC123">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Perfil</label>
        <input type="text" name="profile" value="{{ old('profile', $coupon?->profile) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: default, premium">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Duracao (horas) *</label>
        <input type="number" name="duration_hours" value="{{ old('duration_hours', $coupon?->duration_hours ?? 24) }}" min="1" max="720" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) *</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $coupon?->price ?? 0) }}" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Servidor</label>
        <select name="server_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Nenhum</option>
            @foreach($servers as $server)
            <option value="{{ $server->id }}" {{ old('server_id', $coupon?->server_id) == $server->id ? 'selected' : '' }}>{{ $server->name }}</option>
            @endforeach
        </select>
    </div>
    @if(!$coupon)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade (lote)</label>
        <input type="number" name="quantity" value="1" min="1" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <p class="text-xs text-gray-400 mt-1">Deixe 1 para criar apenas um cupom</p>
    </div>
    @endif
</div>
<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('infra.hotspot-coupons.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
</div>
