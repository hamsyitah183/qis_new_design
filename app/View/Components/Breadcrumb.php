<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    /**
     * Create a new component instance.
     */
    public $items;
    public $title;
    public $titleEn;
    public $titleBm;

    public function __construct($items = [], $title = null, $titleEn = null, $titleBm = null)
    {
        $this->items = $items;
        $this->title = $title ?? end($items)['label'] ?? '';
        $this->titleEn = $titleEn ?? $this->title;
        $this->titleBm = $titleBm ?? $this->title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
