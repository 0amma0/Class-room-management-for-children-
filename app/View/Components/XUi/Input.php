<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Input extends Component
{
    public function __construct(
        public string $type = 'text',
        public ?string $name = null,
        public ?string $id = null,
        public ?string $value = null,
        public ?string $placeholder = null,
        public bool $required = false,
        public bool $disabled = false
    ) {}

    public function render(): View
    {
        return view('components.x-ui.input');
    }
}
