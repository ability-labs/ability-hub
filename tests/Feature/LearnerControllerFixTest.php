<?php

namespace Tests\Feature;

use App\Models\Learner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnerControllerFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_correctly_saves_weekly_minutes_as_zero_when_hours_is_empty_on_store()
    {
        $user = User::factory()->create();
        
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '2010-01-01',
            'gender' => 'male',
            'weekly_hours' => '',
        ];
        
        $response = $this->actingAs($user)->post(route('learners.store'), $payload);
        
        $response->assertRedirect(route('learners.index'));
        $this->assertEquals(0, Learner::first()->weekly_minutes);
    }

    public function test_it_correctly_resets_weekly_minutes_to_zero_when_cleared_on_update()
    {
        $user = User::factory()->create();
        $learner = Learner::factory()->create([
            'user_id' => $user->id,
            'weekly_minutes' => 600, // 10 hours
        ]);
        
        $payload = [
            'first_name' => $learner->first_name,
            'last_name' => $learner->last_name,
            'birth_date' => $learner->birth_date->format('Y-m-d'),
            'gender' => $learner->gender->value,
            'weekly_hours' => '', // Clear it
        ];
        
        $response = $this->actingAs($user)->put(route('learners.update', $learner), $payload);
        
        $response->assertRedirect(route('learners.index'));
        $this->assertEquals(0, $learner->fresh()->weekly_minutes);
    }
}
