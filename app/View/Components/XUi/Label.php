<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Label extends Component
{
    public function __construct(
        public ?string $for = null,
        public bool $required = false
    ) {}

    public function render(): View
    {
        return view('components.x-ui.label');
    }
}
