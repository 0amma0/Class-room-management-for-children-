<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Alert extends Component
{
    public function __construct(
        public string $variant = 'ok',
        public ?string $title = null
    ) {}

    public function render(): View
    {
        return view('components.x-ui.alert');
    }
}
