<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardGreeting extends Widget
{
    protected string $view = 'filament.widgets.dashboard-greeting';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -2;
}
