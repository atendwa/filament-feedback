@props(['feedback', 'comments', 'viewer' => 'submitter'])

@php($forStaff = $viewer === 'staff')

<ol class="flex flex-col gap-4">
    <li class="flex flex-col gap-1">
        <div class="flex items-baseline justify-between gap-3 text-xs text-gray-500">
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $feedback->submitterName() }}</span>
            <time datetime="{{ $feedback->created_at->toIso8601String() }}">{{ $feedback->created_at->format('j M Y, H:i') }}</time>
        </div>
        <div class="whitespace-pre-line rounded-2xl rounded-tl-sm bg-white p-4 text-sm leading-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">{{ $feedback->message }}</div>
    </li>

    @foreach ($comments as $comment)
        @continue($comment->is_internal && ! $forStaff)

        <li class="flex flex-col gap-1 {{ $comment->is_from_submitter ? '' : 'items-end' }}">
            <div class="flex w-full items-baseline justify-between gap-3 text-xs text-gray-500 {{ $comment->is_from_submitter ? '' : 'flex-row-reverse' }}">
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ $comment->authorName($forStaff) }}
                    @if ($comment->is_internal)
                        <span class="ml-1 rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-700">Internal note</span>
                    @endif
                </span>
                <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->format('j M Y, H:i') }}</time>
            </div>
            <div @class([
                'max-w-[90%] whitespace-pre-line p-4 text-sm leading-6 shadow-sm ring-1',
                'rounded-2xl rounded-tl-sm bg-white ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => $comment->is_from_submitter,
                'rounded-2xl rounded-tr-sm bg-primary-50 ring-primary-600/10 dark:bg-primary-950' => ! $comment->is_from_submitter && ! $comment->is_internal,
                'rounded-2xl rounded-tr-sm bg-gray-100 ring-gray-950/10 dark:bg-gray-800' => $comment->is_internal,
            ])>{{ $comment->body }}</div>
        </li>
    @endforeach
</ol>
