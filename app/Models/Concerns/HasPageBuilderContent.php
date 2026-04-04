<?php

namespace App\Models\Concerns;

use Crumbls\Layup\Concerns\HasLayupContent;
use Crumbls\Layup\Support\LayupContent;

trait HasPageBuilderContent
{
    use HasLayupContent;

    public function renderContent(): string
    {
        return (new LayupContent($this->content))->toHtml();
    }
}
