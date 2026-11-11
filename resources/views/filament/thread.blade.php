@php($feedback = $getRecord())

<x-feedback::partials.thread :feedback="$feedback" :comments="$feedback->comments()->with(['user', 'feedback'])->get()" :viewer="$viewer" />
