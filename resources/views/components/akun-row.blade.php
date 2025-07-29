@php
    $akunId = $akun->akun_id;
    $routeUpdate = route('admin.manajemen-data.akun.update', $akunId);
    $routeDestroy = route('admin.manajemen-data.akun.destroy', $akunId);
    $parentInfo = $akun->parent
        ? $akun->parent->kode_akun . ' - ' . $akun->parent->nama_akun
        : '-';
    $indentStyle = 'padding-left: ' . ($level * 20) . 'px;';
@endphp

<tr data-akun-id="{{ $akunId }}" data-original-kode_akun="{{ $akun->kode_akun }}" class="akun-row">
    <td data-field="kode_akun" >{{ $akun->kode_akun }}</td>
    <td data-field="nama_akun" >{{ $akun->nama_akun }}</td>

    <td data-field="tipe_akun" data-value="{{ $akun->tipe_akun }}">{{ $tipeAkunOptions[$akun->tipe_akun] ?? $akun->tipe_akun }}</td>

    <td data-field="is_header" data-value="{{ $akun->is_header }}">{{ $akun->is_header ? 'Ya' : 'Tidak' }}</td>

    <td data-field="parent_id" data-value="{{ $akun->parent_id }}" title="{{ $parentInfo }}">
        {{ $parentInfo }}
    </td>

    <td class="actions">
        <button class="btn btn-warning btn-xs edit-akun" data-id="{{ $akunId }}">
            Edit
        </button>

        <form id="delete-akun-form-{{ $akunId }}"
              action="{{ $routeDestroy }}"
              method="POST"
              style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="button"
                    class="btn btn-danger btn-xs"
                    data-toggle="modal"
                    data-target="#deleteConfirmModal"
                    data-form-id="delete-akun-form-{{ $akunId }}">
                Hapus
            </button>
        </form>

        <button class="btn btn-success btn-xs save-akun"
                data-id="{{ $akunId }}"
                style="display:none;">
            Simpan
        </button>

        <button class="btn btn-secondary btn-xs cancel-edit"
                data-id="{{ $akunId }}"
                style="display:none;">
            Batal
        </button>
    </td>
</tr>

@foreach ($akun->children->sortBy('kode_akun') as $child)
    <x-akun-row :akun="$child"
                :level="$level + 1"
                :tipe-akun-options="$tipeAkunOptions" />
@endforeach
