<?php

namespace App\Livewire\CRM;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PipelineBoard extends Component
{
    use AuthorizesRequests;

    public ?string $leadId = null;

    public string $title = '';

    public string $email = '';

    public string $phone = '';

    public string $status = 'lead';

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['nullable', 'regex:/^\+?[\d\s\-\(\)]{7,20}$/'],
            'status' => 'required|string',
        ];
    }

    public bool $isEditing = false;

    public bool $showModal = false;

    /**
     * Get all leads for the authenticated user, grouped by status.
     *
     * @return Collection<string, Collection<int, Lead>>
     */
    #[Computed]
    public function leadsByStatus(): Collection
    {
        return Lead::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn (Lead $lead) => $lead->status->value);
    }

    /**
     * Get the statuses for the columns.
     *
     * @return array<int, LeadStatus>
     */
    #[Computed]
    public function statuses(): array
    {
        return LeadStatus::cases();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function addLead(string $status = 'lead')
    {
        $this->resetForm();
        $this->status = $status;
        $this->showModal = true;
    }

    public function editLead(string $id)
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('update', $lead);

        $this->leadId = $lead->id;
        $this->title = $lead->title;
        $this->email = $lead->email;
        $this->phone = $lead->phone ?? '';
        $this->status = $lead->status->value;

        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('modal-loaded');
    }

    public function saveLead()
    {
        $this->validate();

        if ($this->isEditing && $this->leadId) {
            $lead = Lead::findOrFail($this->leadId);
            $this->authorize('update', $lead);
            $lead->update([
                'title' => $this->title,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
            ]);
            Flux::toast(variant: 'success', heading: 'Lead updated', text: '"'.$this->title.'" has been saved.');
        } else {
            Lead::create([
                'user_id' => auth()->id(),
                'title' => $this->title,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
            ]);
            Flux::toast(variant: 'success', heading: 'Lead created', text: '"'.$this->title.'" has been added to the pipeline.');
        }

        $this->showModal = false;
        $this->dispatch('close-modal');
        $this->resetForm();
    }

    public function deleteLead(string $id)
    {
        $lead = Lead::findOrFail($id);

        $this->authorize('delete', $lead);

        $lead->delete();

        Flux::toast(variant: 'success', heading: 'Lead deleted', text: 'The lead has been permanently removed.');

        $this->dispatch('lead-deleted');
    }

    public function moveLeadToStatus(string $leadId, string $status, int $position = PHP_INT_MAX): void
    {
        $validStatus = LeadStatus::tryFrom($status);

        if (! $validStatus) {
            return;
        }

        $this->authorize('update', Lead::findOrFail($leadId));

        // All leads in the target column except the one being moved, in current order
        $ordered = Lead::where('user_id', auth()->id())
            ->where('status', $validStatus)
            ->where('id', '!=', $leadId)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->pluck('id')
            ->toArray();

        // Insert the dragged lead at the clamped position
        $position = max(0, min($position, count($ordered)));
        array_splice($ordered, $position, 0, [$leadId]);

        // Persist the new sort_order for every lead in this column
        foreach ($ordered as $index => $id) {
            Lead::where('id', $id)->where('user_id', auth()->id())
                ->update(['status' => $validStatus, 'sort_order' => $index]);
        }

        $this->dispatch('lead-highlight', leadId: $leadId);
    }

    public function resetForm()
    {
        $this->reset(['leadId', 'title', 'email', 'phone', 'status', 'isEditing']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.crm.pipeline-board');
    }
}
