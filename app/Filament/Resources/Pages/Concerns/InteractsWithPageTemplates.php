<?php

namespace App\Filament\Resources\Pages\Concerns;

use App\Models\Template;
use App\Support\PageTemplates;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

/**
 * Adds "Save as template" and "Use a template" header actions to the page
 * editor. Templates store a page's block layout (the Builder's `content`
 * state) so it can be reused on other pages. Everything is scoped to the
 * active Filament tenant — a site only ever sees its own templates.
 */
trait InteractsWithPageTemplates
{
    /**
     * @return array<int, Action>
     */
    protected function templateActions(): array
    {
        return [
            $this->saveAsTemplateAction(),
            $this->useTemplateAction(),
        ];
    }

    protected function currentBlocks(): array
    {
        return array_values(data_get($this->data, 'content') ?? []);
    }

    protected function templateQuery()
    {
        return Template::query()->where('site_id', Filament::getTenant()?->getKey());
    }

    protected function saveAsTemplateAction(): Action
    {
        return Action::make('saveAsTemplate')
            ->label('Save as template')
            ->icon('heroicon-o-bookmark-square')
            ->color('gray')
            ->visible(fn (): bool => Filament::getTenant() !== null)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(120)
                    ->rule(fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                        if ($this->templateQuery()->where('name', $value)->exists()) {
                            $fail('You already have a template with this name.');
                        }
                    }),
                TextInput::make('description')->maxLength(255),
            ])
            ->action(function (array $data): void {
                $blocks = $this->currentBlocks();

                if ($blocks === []) {
                    Notification::make()
                        ->warning()
                        ->title('Nothing to save')
                        ->body('Add at least one block before saving a template.')
                        ->send();

                    return;
                }

                $this->templateQuery()->create([
                    'site_id' => Filament::getTenant()?->getKey(),
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'content' => $blocks,
                ]);

                Notification::make()
                    ->success()
                    ->title('Template saved')
                    ->body('"'.$data['name'].'" can now be reused on any page.')
                    ->send();
            });
    }

    protected function useTemplateAction(): Action
    {
        return Action::make('useTemplate')
            ->label('Use a template')
            ->icon('heroicon-o-square-2-stack')
            ->color('gray')
            ->visible(fn (): bool => $this->templateQuery()->exists())
            ->schema([
                Select::make('template_id')
                    ->label('Template')
                    ->options(fn (): array => $this->templateQuery()->orderBy('name')->pluck('name', 'id')->all())
                    ->required()
                    ->native(false),
                Toggle::make('replace')
                    ->label('Replace the current content')
                    ->helperText('Off: the template is added below what you already have.')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $template = $this->templateQuery()->find($data['template_id']);

                if (! $template) {
                    return;
                }

                $this->data['content'] = PageTemplates::merge(
                    $this->currentBlocks(),
                    $template->content ?? [],
                    (bool) ($data['replace'] ?? false),
                );

                Notification::make()
                    ->success()
                    ->title('Template applied')
                    ->body('Review the blocks and save the page to keep them.')
                    ->send();
            });
    }
}
