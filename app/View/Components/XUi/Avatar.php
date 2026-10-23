<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Avatar extends Component
{
    public function __construct(
        public string $type = 'bangs',
        public string $size = 'md'
    ) {}

    public function render(): View
    {
        return view('components.x-ui.avatar');
    }
}
