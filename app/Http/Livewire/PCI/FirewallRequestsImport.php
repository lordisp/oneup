<?php

namespace App\Http\Livewire\PCI;

use App\Events\ImportNewFirewallRequestsEvent;
use App\Jobs\ServiceNow\ImportFirewallRequestJob;
use App\Providers\AppServiceProvider;
use App\Rules\FirewallRequestsRule;
use App\Traits\Converter;
use App\Traits\ValidationRules;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class FirewallRequestsImport extends Component
{
    use Converter, ValidationRules, WithFileUploads;

    public $attachments = [];

    public ?array $batch = null;

    protected function rules()
    {
        return [
            'attachments' => 'required',
            'attachments.*' => Rule::forEach(fn () => [
                'required', new FirewallRequestsRule(),
            ]),
        ];
    }

    public function mount()
    {
        if (Gate::denies('serviceNow-firewallRequests-import')) {
            $this->redirect(AppServiceProvider::HOME);
        }
    }

    public function updatedAttachments()
    {
        $this->validate();
    }

    public function save()
    {
        $validatedData = $this->validate();

        foreach (is_array($validatedData) ? Arr::first($validatedData) : $validatedData as $attachment) {

            $file = json_decode(file_get_contents($attachment->path()), true);

            $jobs[] = $this->import($file);
        }

        if (! empty($jobs)) {

            $jobs = Arr::flatten($jobs);

            if (! empty($jobs)) {

                $batch = Bus::batch($jobs);

                event(new ImportNewFirewallRequestsEvent(auth()->user(), $batch));

                $this->event($batch->jobs->count().' requests are dispatched for the import. You will be notified, once the import has completed.', 'success');

            } else {
                $this->event('Nothing to do!', 'warning');
            }
        }
    }

    protected function import($file): array
    {
        foreach ($file as $value) {
            $jobs[] = new ImportFirewallRequestJob(auth()->user(), $value);
        }

        $this->reset('attachments');

        return $jobs ?? [];
    }

    public function render()
    {
        return view('livewire.p-c-i.firewall-requests-import', [
            'notifications' => auth()
                ->user()
                ->notifications
                ->pluck('data.message'),
        ]);
    }
}
