@props(['supplier' => null])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
        <input type="text" name="name" value="{{ old('name', $supplier?->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Razao Social</label>
        <input type="text" name="company_name" value="{{ old('company_name', $supplier?->company_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ/CPF</label>
        <input type="text" name="document" value="{{ old('document', $supplier?->document) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier?->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $supplier?->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pessoa de Contato</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier?->contact_person) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
        <input type="text" name="category" value="{{ old('category', $supplier?->category) }}" list="categories" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Material, Equipamento, Servico">
        <datalist id="categories">
            <option value="Material">
            <option value="Equipamento">
            <option value="Servico">
            <option value="Infraestrutura">
            <option value="Software">
        </datalist>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $supplier?->notes) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $supplier?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
            <span class="text-sm text-gray-700">Fornecedor ativo</span>
        </label>
    </div>
</div>
<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('crm.suppliers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
</div>
