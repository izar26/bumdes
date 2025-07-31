<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{-- Default title, will be overridden by JS if data-title is present --}}
                    {{ $title ?? 'Konfirmasi Aksi' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Default body, will be overridden by JS if data-body is present --}}
                {{ $body ?? 'Apakah Anda yakin ingin melakukan aksi ini?' }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn {{ $confirmButtonClass ?? 'btn-primary' }}" id="confirmModalButton">
                    {{ $confirmButtonText ?? 'Konfirmasi' }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // This script ensures the modal's content is updated dynamically
    $(document).ready(function() {
        $('#{{ $modalId }}').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var formId = button.data('form-id');
            var modalTitle = button.data('title'); // New: get title from data attribute
            var modalBody = button.data('body');
            var confirmButtonText = button.data('button-text');
            var confirmButtonClass = button.data('button-class');

            var modal = $(this);

            if (modalTitle) {
                modal.find('.modal-title').text(modalTitle);
            } else {
                modal.find('.modal-title').text('Konfirmasi Aksi'); // Fallback
            }

            modal.find('.modal-body').text(modalBody);
            var confirmButton = modal.find('#confirmModalButton');
            confirmButton.text(confirmButtonText);
            confirmButton.removeClass().addClass('btn').addClass(confirmButtonClass);

            confirmButton.off('click').on('click', function() {
                $('#' + formId).submit();
            });
        });
    });
</script>
@endpush
