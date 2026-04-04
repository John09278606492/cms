<?php

namespace App\Models\Concerns;

use App\Mason\BrickCollection;

trait HasMasonContent
{
    public function renderContent(): string
    {
        return mason(
            content: $this->content,
            bricks: BrickCollection::make(),
        )->toHtml();
    }
}
