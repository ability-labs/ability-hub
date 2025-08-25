<?php

class LanguageSelectorTest extends \Tests\TestCase
{
    public function test_it_will_read_locale_configuration_from_session()
    {
        $this->markTestSkipped('feature to check');
        $app_locale = config('app.locale');

        $response = $this->get('/');

        $response->assertSessionHas('locale', $app_locale)
            ->assertSeeHtml('<html lang="'.$app_locale.'">');
    }

    public function test_it_will_switch_to_new_locale_after_posting_change()
    {
        $this->markTestSkipped('feature to check');
        app()->setLocale('fr');
        $new_locale = 'en';

        $update_response = $this->post(route('locale.update'), ['locale' => $new_locale]);
        $update_response->assertRedirect();

        $response = $this->get('/');
        $response->assertSessionHas('locale', $new_locale)
        ->assertSeeHtml('<html lang="'.$new_locale.'">');
    }
}
