@extends('core::layouts.master')

@section('title', 'Editar Banner')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar Banner</h2>
        </div>
        <form method="POST" action="{{ route('core.banners.update', $banner) }}" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                <input type="text" name="title" value="{{ old('title', $banner->title) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitulo</label>
                <textarea name="subtitle" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('subtitle', $banner->subtitle) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge (selo do topo)</label>
                    <input type="text" name="badge" value="{{ old('badge', $banner->badge) }}" placeholder="Ex: mais popular"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Destaque (texto em gradiente)</label>
                    <input type="text" name="highlight" value="{{ old('highlight', $banner->highlight) }}" placeholder="Parte do titulo em destaque"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Estilo do texto</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cor do Titulo</label>
                        <div class="flex items-center gap-3">
                            <input type="color" value="{{ old('title_color', $banner->title_color ?: '#ffffff') }}" data-color-target="input[name='title_color']"
                                   class="w-11 h-9 rounded-lg border border-gray-300 p-1 bg-white cursor-pointer">
                            <input type="text" name="title_color" value="{{ old('title_color', $banner->title_color) }}" placeholder="#ffffff"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono" data-color-mirror>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tamanho do Titulo (px)</label>
                        <input type="number" name="title_font_size" value="{{ old('title_font_size', $banner->title_font_size) }}" min="16" max="120" placeholder="Padrao"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cor do Subtitulo</label>
                        <div class="flex items-center gap-3">
                            <input type="color" value="{{ old('subtitle_color', $banner->subtitle_color ?: '#e2e8f0') }}" data-color-target="input[name='subtitle_color']"
                                   class="w-11 h-9 rounded-lg border border-gray-300 p-1 bg-white cursor-pointer">
                            <input type="text" name="subtitle_color" value="{{ old('subtitle_color', $banner->subtitle_color) }}" placeholder="#e2e8f0"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono" data-color-mirror>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tamanho do Subtitulo (px)</label>
                        <input type="number" name="subtitle_font_size" value="{{ old('subtitle_font_size', $banner->subtitle_font_size) }}" min="12" max="64" placeholder="Padrao"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-400">Deixe em branco para usar o padrao do tema.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Imagem do Banner</label>
                @if($banner->image)
                <div class="flex items-center gap-4 mb-2">
                    <img src="{{ asset('storage/'.$banner->image) }}" alt="{{ $banner->title }}" class="h-20 w-36 object-cover rounded-lg border border-gray-200">
                    <span class="text-xs text-gray-400">Imagem atual. Envie outra para substituir.</span>
                </div>
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-sm file:font-medium @error('image') border-red-500 @enderror">
                @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP ou GIF ate 16MB. Recomendado ~1280x720 (16:9).</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titulo do Botao</label>
                    <input type="text" name="link_label" value="{{ old('link_label', $banner->link_label) }}" placeholder="Ex: Contratar"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link do Botao (URL)</label>
                    <input type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}" placeholder="https://..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordem de Exibicao</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Ativo
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('core.banners.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[type="color"][data-color-target]').forEach(function (picker) {
    var text = document.querySelector(picker.dataset.colorTarget);
    if (!text) return;
    picker.addEventListener('input', function () { text.value = picker.value; });
    text.addEventListener('input', function () {
        var v = text.value.trim();
        if (/^#[0-9a-fA-F]{3,8}$/.test(v)) picker.value = v;
    });
});
</script>
@endpush