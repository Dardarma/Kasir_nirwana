<label class="d-block">Nama satuan
    <input class="form-control" name="nama" value="{{ $unit?->nama }}" maxlength="50" required>
</label>
<label class="d-block">
    <input type="checkbox" name="is_satuan_terkecil" value="1" @checked($unit?->is_satuan_terkecil)>
    Satuan terkecil
</label>
<fieldset name="konversi" @disabled($unit?->is_satuan_terkecil)>
    <label class="d-block">Bergantung pada
        <select class="form-control" name="parent_id">
            <option value=""></option>
            @foreach ($units as $parent)
                @if ($parent->is_satuan_terkecil && $parent->id !== $unit?->id)
                    <option value="{{ $parent->id }}" @selected($unit?->parent_id == $parent->id)>{{ $parent->nama }}</option>
                @endif
            @endforeach
        </select>
    </label>
    <label class="d-block">Faktor konversi
        <input class="form-control" type="number" name="faktor" step="0.000001" min="0.000001" value="{{ $unit?->faktor }}">
    </label>
</fieldset>
