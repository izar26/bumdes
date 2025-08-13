<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{ $title ?? 'Konfirmasi Aksi' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ $body ?? 'Apakah Anda yakin ingin melakukan aksi ini?' }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>

                {{-- REVISI 1: Buat ID tombol ini menjadi dinamis --}}
                <button type="button" class="btn {{ $confirmButtonClass ?? 'btn-primary' }}" id="{{ $modalId }}-confirmButton">
                    {{ $confirmButtonText ?? 'Konfirmasi' }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        $('#{{ $modalId }}').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var formId = button.data('form-id');
            var modalTitle = button.data('title');
            var modalBody = button.data('body');
            var confirmButtonText = button.data('button-text');
            var confirmButtonClass = button.data('button-class');
            var modal = $(this);

            if (modalTitle) {
                modal.find('.modal-title').text(modalTitle);
            }
            if(modalBody) {
                modal.find('.modal-body').text(modalBody);
            }

            {{-- REVISI 2: Cari tombol berdasarkan ID yang dinamis --}}
            var confirmButton = modal.find('#{{ $modalId }}-confirmButton');

            confirmButton.text(confirmButtonText || 'Konfirmasi');
            // Hapus semua class btn-* lalu tambahkan class yang baru
            confirmButton.removeClass (function (index, className) {
                return (className.match (/(^|\s)btn-\S+/g) || []).join(' ');
            }).addClass(confirmButtonClass || 'btn-primary');

            // Hapus event listener sebelumnya dan tambahkan yang baru
            confirmButton.off('click').on('click', function() {
                $('#' + formId).submit();
            });
        });
    });
</script>
@endpush
