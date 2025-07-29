<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AkunRow extends Component
{
    public $akun;
    public $level;
    public $tipeAkunOptions;

    public function __construct($akun, $level, $tipeAkunOptions)
    {
        $this->akun = $akun;
        $this->level = $level;
        $this->tipeAkunOptions = $tipeAkunOptions;
    }

    public function render()
    {
        return view('components.akun-row');
    }
}

