<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ConfirmModal extends Component
{
    public $modalId;
    public $title;
    public $body;
    public $confirmButtonText;
    public $confirmButtonClass;
    public $actionFormId; // The ID of the form to be submitted on confirmation

    /**
     * Create a new component instance.
     *
     * @param string $modalId A unique ID for the modal (e.g., 'deleteConfirmModal')
     * @param string $title The title of the modal (e.g., 'Konfirmasi Hapus')
     * @param string $body The message body (e.g., 'Apakah Anda yakin ingin menghapus item ini?')
     * @param string $confirmButtonText Text for the confirm button (e.g., 'Hapus')
     * @param string $confirmButtonClass CSS class for the confirm button (e.g., 'btn-danger')
     * @param string $actionFormId The ID of the hidden form that performs the action
     */
    public function __construct(
        $modalId = 'confirmModal',
        $title = 'Konfirmasi',
        $body = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        $confirmButtonText = 'Konfirmasi',
        $confirmButtonClass = 'btn-primary',
        $actionFormId = 'actionForm' // Default ID for a generic action form
    ) {
        $this->modalId = $modalId;
        $this->title = $title;
        $this->body = $body;
        $this->confirmButtonText = $confirmButtonText;
        $this->confirmButtonClass = $confirmButtonClass;
        $this->actionFormId = $actionFormId;
    }

    public function render()
    {
        return view('components.confirm-modal');
    }
}
