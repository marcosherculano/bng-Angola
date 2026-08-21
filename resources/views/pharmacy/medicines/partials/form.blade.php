@php($bngFormUid = optional($medicine)->id ?: 'new')

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input class="form-control form-control-lg" name="name" value="{{ old('name', optional($medicine)->name) }}" maxlength="200" required list="bngMedicineNameOptions" placeholder="Ex: Paracetamol 500mg" data-bng-suggest-input="names">
        <datalist id="bngMedicineNameOptions">
            @foreach (($suggestNames ?? []) as $v)
                <option value="{{ $v }}"></option>
            @endforeach
        </datalist>
    </div>

    <div class="col-md-3">
        <label class="form-label">Código de barras</label>
        <input class="form-control form-control-lg" name="barcode" value="{{ old('barcode', optional($medicine)->barcode) }}" maxlength="50" list="bngMedicineBarcodeOptions" placeholder="Ex: 5601234567890">
        <datalist id="bngMedicineBarcodeOptions">
            @foreach (($suggestBarcodes ?? []) as $v)
                <option value="{{ $v }}"></option>
            @endforeach
        </datalist>
    </div>

    <div class="col-md-3">
        <label class="form-label">Categoria</label>
        <input class="form-control form-control-lg" name="category" value="{{ old('category', optional($medicine)->category) }}" maxlength="100" placeholder="Ex: Analgésico" list="bngMedicineCategoryOptions" data-bng-suggest-input="categories">
        <datalist id="bngMedicineCategoryOptions">
            @foreach (($categories ?? []) as $v)
                <option value="{{ $v }}"></option>
            @endforeach
        </datalist>
    </div>

    <div class="col-12">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Informações úteis: dosagem, embalagem, observações...">{{ old('description', optional($medicine)->description) }}</textarea>
    </div>

    <div class="col-md-3">
        <label class="form-label">Preço (Kz)</label>
        <input class="form-control form-control-lg" type="number" name="price" value="{{ old('price', optional($medicine)->price) }}" min="0" step="0.01" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Stock</label>
        <input class="form-control form-control-lg" type="number" name="stock" value="{{ old('stock', optional($medicine)->stock) }}" min="0" step="1" required>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="requires_prescription" id="requires_prescription_{{ $bngFormUid }}" value="1" @checked(old('requires_prescription', optional($medicine)->requires_prescription) ? true : false)>
            <label class="form-check-label" for="requires_prescription_{{ $bngFormUid }}">Requer receita médica</label>
        </div>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_available" id="is_available_{{ $bngFormUid }}" value="1" @checked(old('is_available', optional($medicine)->is_available) ? true : false)>
            <label class="form-check-label" for="is_available_{{ $bngFormUid }}">Disponível para venda</label>
        </div>
    </div>
</div>
<hr class="my-4">
