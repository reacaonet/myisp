@extends('core::layouts.master')

@section('title', 'Novo Banner')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Novo Banner</h2>
        </div>
        <form method="POST" action="{{ route('core.banners.store') }}" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitulo</label>
                <textarea name="subtitle" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('subtitle') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge (selo do topo)</label>
                    <input type="text" name="badge" value="{{ old('badge') }}" placeholder="Ex: mais popular"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Destaque (texto em gradiente)</label>
                    <input type="text" name="highlight" value="{{ old('highlight') }}" placeholder="Parte do titulo em destaque"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Imagem do Banner</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-sm file:font-medium">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP ou GIF ate 4MB. Recomendado ~1280x720 (16:9).</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titulo do Botao</label>
                    <input type="text" name="link_label" value="{{ old('link_label') }}" placeholder="Ex: Contratar"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link do Botao (URL)</label>
                    <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Ativo
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('core.banners.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection