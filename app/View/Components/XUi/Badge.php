<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Badge extends Component
{
    public function __construct(
        public string $variant = 'sun',
        public string $size = 'md'
    ) {}

    public function render(): View
    {
        return view('components.x-ui.badge');
    }
}
