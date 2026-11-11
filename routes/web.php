<?php

declare(strict_types=1);

use Atendwa\Feedback\Livewire\SubmitForm;
use Atendwa\Feedback\Livewire\Thread;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get(config()->string('feedback.path'), SubmitForm::class)->name('feedback.create');

    Route::get(config()->string('feedback.path') . '/conversation/{feedback:uuid}', Thread::class)
        ->middleware('signed')->name('feedback.thread');
});
