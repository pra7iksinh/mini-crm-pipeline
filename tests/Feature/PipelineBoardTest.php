<?php

use App\Enums\LeadStatus;
use App\Livewire\CRM\PipelineBoard;
use App\Models\Lead;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ---------------------------------------------------------------------------
// Rendering
// ---------------------------------------------------------------------------

it('renders without errors', function () {
    Livewire::test(PipelineBoard::class)->assertOk();
});

it('shows the authenticated user\'s leads', function () {
    Lead::factory()->for($this->user)->create(['title' => 'My Lead']);

    Livewire::test(PipelineBoard::class)->assertSee('My Lead');
});

it('does not show another user\'s leads', function () {
    Lead::factory()->create(['title' => 'Their Lead']);

    Livewire::test(PipelineBoard::class)->assertDontSee('Their Lead');
});

// ---------------------------------------------------------------------------
// Adding leads
// ---------------------------------------------------------------------------

it('opens the add modal with the correct status pre-selected', function () {
    Livewire::test(PipelineBoard::class)
        ->call('addLead', 'contacted')
        ->assertSet('showModal', true)
        ->assertSet('status', 'contacted');
});

it('creates a lead with valid data', function () {
    Livewire::test(PipelineBoard::class)
        ->set('title', 'Acme Corp')
        ->set('email', 'acme@example.com')
        ->set('status', 'lead')
        ->call('saveLead');

    expect(
        Lead::where('user_id', $this->user->id)->where('title', 'Acme Corp')->exists()
    )->toBeTrue();
});

it('closes the modal and resets the form after saving', function () {
    Livewire::test(PipelineBoard::class)
        ->set('title', 'Acme Corp')
        ->set('email', 'acme@example.com')
        ->set('status', 'lead')
        ->call('saveLead')
        ->assertSet('showModal', false)
        ->assertSet('title', '')
        ->assertSet('email', '');
});

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

it('requires title and email', function () {
    Livewire::test(PipelineBoard::class)
        ->call('saveLead')
        ->assertHasErrors(['title' => 'required', 'email' => 'required']);
});

it('rejects an invalid email format', function () {
    Livewire::test(PipelineBoard::class)
        ->set('title', 'Test')
        ->set('email', 'not-an-email')
        ->call('saveLead')
        ->assertHasErrors(['email' => 'email']);
});

it('rejects a phone number with invalid characters', function () {
    Livewire::test(PipelineBoard::class)
        ->set('title', 'Test')
        ->set('email', 'test@example.com')
        ->set('phone', 'abc-xyz')
        ->call('saveLead')
        ->assertHasErrors(['phone']);
});

// ---------------------------------------------------------------------------
// Editing leads
// ---------------------------------------------------------------------------

it('populates the form when editing a lead', function () {
    $lead = Lead::factory()->for($this->user)->create([
        'title' => 'Edit Me',
        'email' => 'edit@example.com',
        'status' => LeadStatus::CONTACTED,
    ]);

    Livewire::test(PipelineBoard::class)
        ->call('editLead', $lead->id)
        ->assertSet('title', 'Edit Me')
        ->assertSet('email', 'edit@example.com')
        ->assertSet('status', 'contacted')
        ->assertSet('isEditing', true)
        ->assertSet('showModal', true);
});

it('updates an existing lead', function () {
    $lead = Lead::factory()->for($this->user)->create(['title' => 'Old Title']);

    Livewire::test(PipelineBoard::class)
        ->call('editLead', $lead->id)
        ->set('title', 'New Title')
        ->call('saveLead');

    expect($lead->fresh()->title)->toBe('New Title');
});

// ---------------------------------------------------------------------------
// Deleting leads
// ---------------------------------------------------------------------------

it('deletes the owner\'s own lead', function () {
    $lead = Lead::factory()->for($this->user)->create();

    Livewire::test(PipelineBoard::class)->call('deleteLead', $lead->id);

    expect(Lead::find($lead->id))->toBeNull();
});

it('dispatches lead-deleted after deletion', function () {
    $lead = Lead::factory()->for($this->user)->create();

    Livewire::test(PipelineBoard::class)
        ->call('deleteLead', $lead->id)
        ->assertDispatched('lead-deleted');
});

// ---------------------------------------------------------------------------
// Moving leads
// ---------------------------------------------------------------------------

it('moves a lead to a different status column', function () {
    $lead = Lead::factory()->for($this->user)->create(['status' => LeadStatus::LEAD]);

    Livewire::test(PipelineBoard::class)
        ->call('moveLeadToStatus', $lead->id, 'won');

    expect($lead->fresh()->status)->toBe(LeadStatus::WON);
});

it('places a moved lead at the requested position', function () {
    $a = Lead::factory()->for($this->user)->create(['status' => LeadStatus::CONTACTED, 'sort_order' => 0]);
    $b = Lead::factory()->for($this->user)->create(['status' => LeadStatus::CONTACTED, 'sort_order' => 1]);
    $lead = Lead::factory()->for($this->user)->create(['status' => LeadStatus::LEAD]);

    // Move $lead into CONTACTED at index 1 (between $a and $b)
    Livewire::test(PipelineBoard::class)
        ->call('moveLeadToStatus', $lead->id, 'contacted', 1);

    expect($a->fresh()->sort_order)->toBe(0)
        ->and($lead->fresh()->sort_order)->toBe(1)
        ->and($b->fresh()->sort_order)->toBe(2);
});

it('ignores an invalid status value', function () {
    $lead = Lead::factory()->for($this->user)->create(['status' => LeadStatus::LEAD]);

    Livewire::test(PipelineBoard::class)
        ->call('moveLeadToStatus', $lead->id, 'nonexistent');

    expect($lead->fresh()->status)->toBe(LeadStatus::LEAD);
});

// ---------------------------------------------------------------------------
// Policy — unit-level ownership rules
// ---------------------------------------------------------------------------

it('grants the owner update and delete access', function () {
    $lead = Lead::factory()->for($this->user)->create();

    expect($this->user->can('update', $lead))->toBeTrue()
        ->and($this->user->can('delete', $lead))->toBeTrue();
});

it('denies update and delete access to non-owners', function () {
    $lead = Lead::factory()->create(); // belongs to a different user

    expect($this->user->can('update', $lead))->toBeFalse()
        ->and($this->user->can('delete', $lead))->toBeFalse();
});

// ---------------------------------------------------------------------------
// Policy — enforced via the component (side-effect assertions)
// ---------------------------------------------------------------------------

it('does not delete another user\'s lead', function () {
    $lead = Lead::factory()->create();

    Livewire::test(PipelineBoard::class)->call('deleteLead', $lead->id);

    expect(Lead::find($lead->id))->not->toBeNull();
});

it('does not overwrite another user\'s lead on save', function () {
    $lead = Lead::factory()->create(['title' => 'Original']);

    Livewire::test(PipelineBoard::class)
        ->set('leadId', $lead->id)
        ->set('isEditing', true)
        ->set('title', 'Hacked')
        ->set('email', 'hacked@example.com')
        ->call('saveLead');

    expect($lead->fresh()->title)->toBe('Original');
});

it('does not move another user\'s lead', function () {
    $lead = Lead::factory()->create(['status' => LeadStatus::LEAD]);

    Livewire::test(PipelineBoard::class)->call('moveLeadToStatus', $lead->id, 'won');

    expect($lead->fresh()->status)->toBe(LeadStatus::LEAD);
});
