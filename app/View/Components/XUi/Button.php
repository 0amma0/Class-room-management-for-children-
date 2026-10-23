<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Button extends Component
{
    public function __construct(
        public string $variant = 'primary',
        public string $size = 'md',
        public ?string $href = null,
        public string $type = 'button',
        public ?string $icon = null
    ) {}

    public function render(): View
    {
        return view('components.x-ui.button');
    }
}
