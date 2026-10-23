<?php

namespace App\View\Components\XUi;

use Illuminate\View\Component;
use Illuminate\View\View;

class Card extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
        public string $padding = 'p-6',
        public ?string $badge = null
    ) {}

    public function render(): View
    {
        return view('components.x-ui.card');
    }
}
