<div class="flex flex-col gap-6">
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">{{ $feedback->title }}</h1>
            <x-filament::badge :color="$feedback->status->getColor()" :icon="$feedback->status->getIcon()">{{ $feedback->status->getLabel() }}</x-filament::badge>
        </div>
        <p class="mt-1 text-sm text-gray-500">
            {{ $feedback->reference() }} · sent {{ $feedback->created_at->format('j M Y') }}
            @if ($feedback->is_anonymous) · shown to staff as "{{ $feedback->alias }}" @endif
        </p>
    </div>

    <x-filament::section>
        <x-feedback::partials.thread :feedback="$feedback" :comments="$comments" viewer="submitter" />
    </x-filament::section>

    @if ($feedback->canBeRepliedTo())
        <form wire:submit="reply" class="flex flex-col gap-3">
            <textarea
                wire:model="body"
                rows="4"
                maxlength="5000"
                placeholder="Write a reply"
                class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20"
            ></textarea>
            @error('body') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror

            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">Send reply</x-filament::button>
            </div>
        </form>
    @else
        <p class="rounded-xl bg-gray-100 p-4 text-center text-sm text-gray-600">This conversation is closed. Send new feedback if there is something else.</p>
    @endif
</div>
