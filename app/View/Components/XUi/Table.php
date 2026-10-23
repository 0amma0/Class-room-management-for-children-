<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Table extends Component
{
    public function __construct(
        public array $headers = []
    ) {}

    public function render(): View
    {
        return view('components.x-ui.table');
    }
}
