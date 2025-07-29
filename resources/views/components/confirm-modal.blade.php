<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ $body }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn {{ $confirmButtonClass }}" id="{{ $modalId }}-confirm-btn" data-form-id="{{ $actionFormId }}">
                    {{ $confirmButtonText }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('{{ $modalId }}-confirm-btn').addEventListener('click', function () {
            const formId = this.getAttribute('data-form-id');
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            }
            // Close the modal after submission
            $('#{{ $modalId }}').modal('hide');
        });

        // Event listener for buttons that trigger this modal
        // These buttons will need a data-toggle="modal" and data-target="#{{ $modalId }}"
        // AND a data-form-id attribute pointing to the specific form to submit.
        document.querySelectorAll('[data-toggle="modal"][data-target="#{{ $modalId }}"]').forEach(button => {
            button.addEventListener('click', function() {
                const formToSubmitId = this.getAttribute('data-form-id');
                // Set the form ID on the modal's confirm button
                document.getElementById('{{ $modalId }}-confirm-btn').setAttribute('data-form-id', formToSubmitId);
            });
        });
    });
</script>
@endpush
