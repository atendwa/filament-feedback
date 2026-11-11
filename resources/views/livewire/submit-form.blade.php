<div class="flex flex-col gap-6">
    @if ($submitted)
        <x-filament::section icon="heroicon-o-check-circle" icon-color="success">
            <x-slot name="heading">Thank you, we have your feedback</x-slot>

            <div class="flex flex-col gap-4 text-sm leading-6">
                <p>Reference <strong>{{ $reference }}</strong>.</p>

                @if ($threadUrl)
                    <div class="rounded-xl bg-primary-50 p-4 ring-1 ring-primary-600/10 dark:bg-primary-950">
                        <p class="font-medium">Your private link</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Use it to read our replies and write back. Anyone holding the link can open the conversation, so keep it safe.
                            @if ($emailed)
                                We have also emailed it to you.
                            @else
                                We have not emailed it, so save it now.
                            @endif
                        </p>
                        <a href="{{ $threadUrl }}" class="mt-3 block break-all text-primary-600 underline">{{ $threadUrl }}</a>
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">You chose not to receive replies, so there is no link. Our team will still read what you wrote.</p>
                @endif

                <div>
                    <x-filament::button wire:click="again" color="gray" outlined>Send more feedback</x-filament::button>
                </div>
            </div>
        </x-filament::section>
    @else
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Tell us what you think</h1>
            <p class="mt-1 text-sm text-gray-500">Suggestions, problems and compliments are all welcome. It takes a minute.</p>
        </div>

        <form wire:submit="submit" class="flex flex-col gap-6">
            <x-filament::section>
                <div class="flex flex-col gap-5">
                    <div class="grid gap-5 sm:grid-cols-3">
                        <label class="flex flex-col gap-1.5 sm:col-span-1">
                            <span class="text-sm font-medium">Topic</span>
                            <x-filament::input.wrapper :valid="! $errors->has('category')">
                                <x-filament::input.select wire:model="category">
                                    <option value="">Choose one</option>
                                    @foreach ($categories as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </label>

                        <label class="flex flex-col gap-1.5 sm:col-span-2">
                            <span class="text-sm font-medium">Subject <span class="text-danger-600">*</span></span>
                            <x-filament::input.wrapper :valid="! $errors->has('title')">
                                <x-filament::input type="text" wire:model="title" maxlength="120" placeholder="In a few words" />
                            </x-filament::input.wrapper>
                            @error('title') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <label class="flex flex-col gap-1.5">
                        <span class="text-sm font-medium">Your feedback <span class="text-danger-600">*</span></span>
                        <textarea
                            wire:model="message"
                            rows="6"
                            maxlength="5000"
                            placeholder="What happened, or what would you like to see?"
                            class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20"
                        ></textarea>
                        @error('message') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </label>
                </div>
            </x-filament::section>

            <x-filament::section icon="heroicon-o-eye-slash">
                <x-slot name="heading">Who can see this?</x-slot>

                <div class="flex flex-col gap-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label @class(['flex cursor-pointer flex-col gap-1 rounded-xl p-4 ring-1 transition', 'bg-primary-50 ring-2 ring-primary-600' => $is_anonymous !== '1', 'ring-gray-950/10 hover:bg-gray-50' => $is_anonymous === '1'])>
                            <span class="flex items-center gap-2 text-sm font-medium">
                                <input type="radio" wire:model.live="is_anonymous" value="0" class="text-primary-600" />
                                Share my name
                            </span>
                            <span class="text-xs text-gray-500">The team sees who wrote it and can answer you directly.</span>
                        </label>

                        <label @class(['flex cursor-pointer flex-col gap-1 rounded-xl p-4 ring-1 transition', 'bg-primary-50 ring-2 ring-primary-600' => $is_anonymous === '1', 'ring-gray-950/10 hover:bg-gray-50' => $is_anonymous !== '1'])>
                            <span class="flex items-center gap-2 text-sm font-medium">
                                <input type="radio" wire:model.live="is_anonymous" value="1" class="text-primary-600" />
                                Stay anonymous
                            </span>
                            <span class="text-xs text-gray-500">The team sees a nickname instead of your name.</span>
                        </label>
                    </div>

                    @if ($is_anonymous === '1')
                        <div class="flex gap-3 rounded-xl bg-primary-50 p-4 text-xs leading-5 text-primary-900 ring-1 ring-primary-600/20 dark:bg-primary-950 dark:text-primary-100">
                            <x-filament::icon icon="heroicon-o-information-circle" class="size-5 shrink-0" />
                            <p>
                                Anonymous means hidden from the people who read feedback, not impossible to trace.
                                @auth
                                    Because you are signed in, your account is recorded privately so we can stop abuse. Only a system administrator with direct database access could look it up.
                                @else
                                    We keep no name, but a system administrator could still see technical details such as when it was sent.
                                @endauth
                            </p>
                        </div>
                    @elseif (! auth()->check())
                        <label class="flex flex-col gap-1.5">
                            <span class="text-sm font-medium">Your name <span class="text-gray-400">(optional)</span></span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="name" maxlength="120" />
                            </x-filament::input.wrapper>
                        </label>
                    @endif
                </div>
            </x-filament::section>

            <x-filament::section icon="heroicon-o-envelope">
                <x-slot name="heading">Do you want a reply?</x-slot>

                <div class="flex flex-col gap-4">
                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" wire:model.live="wants_replies" class="mt-1 rounded text-primary-600" />
                        <span>
                            <span class="font-medium">Yes, let me follow up and read replies</span>
                            <span class="block text-xs text-gray-500">You get a private link to the conversation. If you leave this off, there is no link and the team cannot answer you.</span>
                        </span>
                    </label>

                    @if ($wants_replies)
                        <label class="flex flex-col gap-1.5">
                            <span class="text-sm font-medium">Email me the link <span class="text-gray-400">(optional)</span></span>
                            <x-filament::input.wrapper :valid="! $errors->has('contact_email')">
                                <x-filament::input type="email" wire:model="contact_email" placeholder="you@example.com" />
                            </x-filament::input.wrapper>
                            @error('contact_email') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                            <span class="text-xs text-gray-500">Without an email the link is shown once on the next screen, so save it.</span>
                        </label>
                    @endif
                </div>
            </x-filament::section>

            <input type="text" wire:model="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

            @error('throttle') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror

            <div class="flex items-center justify-between gap-4">
                <p class="text-xs text-gray-500">@auth Signed in as {{ auth()->user()->name }}. @endauth</p>
                <x-filament::button type="submit" size="lg" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">
                    Send feedback
                </x-filament::button>
            </div>
        </form>
    @endif
</div>
