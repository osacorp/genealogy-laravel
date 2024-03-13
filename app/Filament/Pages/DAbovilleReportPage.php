<?php

namespace App\Filament\Pages;

use App\Filament\Pages\CustomFilamentBasePage;
use Livewire\Livewire;

class DAbovilleReportPage extends CustomFilamentBasePage
{
    protected string $view = 'livewire.daboville-report';

//    public function render(): \Illuminate\Contracts\Support\Renderable
//    {
//        return \Livewire::mount(static::$view);
//    }

    public function mount(): void
    {
        Livewire::mount(static::$view);
    }
}
    protected ?string $title = 'DAboville Report';
    protected ?string $navigationIcon = 'heroicon-o-document-report';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon;
    }