@extends('core::layouts.master')

@section('title', 'Editar Local')

@section('content')
<div class="max-w-xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Local</h2>

    <form method="POST" action="{{ route('crm.stock-locations.update', $location) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input type="text" name="name" value="{{ old('name', $location->name) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
            <select name="type" id="type" required class="w-full border rounded-lg px-3 py-2">
                <option value="deposit" {{ old('type', $location->type) == 'deposit' ? 'selected' : '' }}>Depósito</option>
                <option value="technician" {{ old('type', $location->type) == 'technician' ? 'selected' : '' }}>Técnico</option>
            </select>
        </div>
        <div id="user_id_div" style="display: {{ old('type', $location->type) == 'technician' ? 'block' : 'none' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">Técnico *</label>
            <select name="user_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">Selecione...</option>
                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ old('user_id', $location->user_id) == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                @endforeach
            </select>
            @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Atualizar</button>
            <a href="{{ route('crm.stock-locations.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('type').addEventListener('change', function() {
        document.getElementById('user_id_div').style.display = this.value === 'technician' ? 'block' : 'none';
    });
</script>
@endpush
@endsection
