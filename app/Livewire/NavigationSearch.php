<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavigationSearch extends Component
{
    public string $search = '';

    #[Computed]
    public function navigationItems(): array
    {
        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return [];
        }

        $items = [];

        foreach ($panel->getNavigation() as $navigationGroup) {
            $groupLabel = (string) $navigationGroup->getLabel();

            foreach ($navigationGroup->getItems() as $item) {
                if (! $item->isVisible()) {
                    continue;
                }

                $items[] = [
                    'label' => (string) $item->getLabel(),
                    'url' => $item->getUrl(),
                    'icon' => $item->getIcon(),
                    'group' => $groupLabel,
                ];
            }
        }

        return $items;
    }

    #[Computed]
    public function filteredItems(): array
    {
        $search = trim(mb_strtolower($this->search));

        if ($search === '') {
            return [];
        }

        return collect($this->navigationItems)
            ->filter(function (array $item) use ($search) {
                return str_contains(mb_strtolower($item['label']), $search)
                    || str_contains(mb_strtolower($item['group']), $search);
            })
            ->take(10)
            ->values()
            ->all();
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.navigation-search');
    }
}
