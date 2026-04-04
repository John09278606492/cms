<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Collection;

class NavigationItem
{
    public Collection $children;

    protected ?Closure $isActiveUsing;

    public function __construct(
        public string $title,
        public ?string $url = null,
        array|Collection $children = [],
        public ?string $target = null,
        public ?string $rel = null,
        public ?string $classes = null,
        ?Closure $isActiveUsing = null,
    ) {
        $this->children = $children instanceof Collection ? $children : collect($children);
        $this->isActiveUsing = $isActiveUsing;
    }

    public static function make(string $title, ?string $url = null): static
    {
        return new static($title, $url);
    }

    public function children(array|Collection $children): static
    {
        $this->children = $children instanceof Collection ? $children : collect($children);

        return $this;
    }

    public function activeWhen(Closure $callback): static
    {
        $this->isActiveUsing = $callback;

        return $this;
    }

    public function isActive(?string $currentUrl = null): bool
    {
        if ($this->isActiveUsing instanceof Closure) {
            return (bool) ($this->isActiveUsing)($currentUrl, $this);
        }

        if (blank($this->url)) {
            return false;
        }

        $currentUrl ??= request()->url();
        $itemUrl = url($this->url);

        return rtrim($currentUrl, '/') === rtrim($itemUrl, '/');
    }

    public function isActiveOrHasActiveChild(?string $currentUrl = null): bool
    {
        $currentUrl ??= request()->url();

        if ($this->isActive($currentUrl)) {
            return true;
        }

        return $this->children->contains(
            fn (self $child): bool => $child->isActiveOrHasActiveChild($currentUrl),
        );
    }
}
