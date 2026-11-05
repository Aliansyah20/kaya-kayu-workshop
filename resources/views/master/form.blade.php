@extends('layouts.app')

@section('title', ($record ? 'Edit ' : 'Tambah ') . $config['singular'])
@section('page-title', ($record ? 'Edit ' : 'Tambah ') . $config['singular'])
@section('page-subtitle', 'Lengkapi data ' . strtolower($config['singular']) . ' dengan benar')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $record ? 'Edit' : 'Tambah' }} {{ $config['singular'] }}</h2>
            <p class="kk-page-sub">Data ini akan langsung tersedia di pilihan form transaksi.</p>
        </div>
        <a href="{{ route('master.index', $resource) }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="kk-card">
        <div class="kk-card-body">
            <form method="POST" action="{{ $record ? route('master.update', [$resource, $record->id]) : route('master.store', $resource) }}">
                @csrf
                @if($record)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    @foreach($config['fields'] as $field)
                        @php
                            $name = $field['name'];
                            $value = old($name, $record?->{$name} ?? ($field['default'] ?? ''));
                            $col = $field['col'] ?? 'col-md-6';
                        @endphp

                        <div class="{{ $col }}">
                            @if(($field['type'] ?? 'text') === 'checkbox')
                                <div class="form-check form-switch mt-4 pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="{{ $name }}" name="{{ $name }}" value="1" {{ $value ? 'checked' : '' }}>
                                    <label class="form-check-label form-label mb-0" for="{{ $name }}">{{ $field['label'] }}</label>
                                </div>
                            @else
                                <label class="form-label" for="{{ $name }}">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-danger">*</span>@endif</label>

                                @if(($field['type'] ?? 'text') === 'textarea')
                                    <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}">{{ $value }}</textarea>
                                @elseif(($field['type'] ?? 'text') === 'select')
                                    <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" @if($field['required'] ?? false) required @endif>
                                        <option value="">— Pilih {{ $field['label'] }} —</option>
                                        @foreach($field['options'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="{{ $field['type'] ?? 'text' }}"
                                        class="form-control @error($name) is-invalid @enderror"
                                        id="{{ $name }}"
                                        name="{{ $name }}"
                                        value="{{ $value }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                        @if($field['required'] ?? false) required @endif
                                    >
                                @endif

                                @error($name)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(!empty($field['help']))
                                    <div class="form-text">{{ $field['help'] }}</div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="kk-divider"></div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master.index', $resource) }}" class="btn btn-outline-kk">Batal</a>
                    <button type="submit" class="btn btn-kk"><i class="bi bi-check2 me-1"></i> Simpan {{ $config['singular'] }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
