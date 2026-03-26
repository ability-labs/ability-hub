<?php

namespace Tests\Feature\Components;

use App\View\Components\Dashboard\StatCard;
use App\View\Components\Dashboard\ChartCard;
use Tests\TestCase;

class DashboardComponentsTest extends TestCase
{
    public function test_stat_card_component_renders_correctly()
    {
        $component = new StatCard(
            label: 'Test Label',
            count: '123',
            icon: 'academic-cap',
            color: 'blue'
        );

        $view = $component->render();
        $html = view($view->getName(), array_merge($view->getData(), ['slot' => '']))->render();

        $this->assertStringContainsString('Test Label', $html);
        $this->assertStringContainsString('123', $html);
        $this->assertStringContainsString('bg-blue-100', $html);
    }

    public function test_chart_card_component_renders_correctly()
    {
        $component = new ChartCard(
            title: 'Test Chart',
            subtitle: 'Test Subtitle',
            id: 'testChart',
            isLarge: true
        );

        $view = $component->render();
        $html = view($view->getName(), array_merge($view->getData(), ['slot' => '']))->render();

        $this->assertStringContainsString('Test Chart', $html);
        $this->assertStringContainsString('Test Subtitle', $html);
        $this->assertStringContainsString('id="testChart"', $html);
        $this->assertStringContainsString('lg:col-span-2', $html);
    }
}
