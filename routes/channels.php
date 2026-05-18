<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('student.{id}', function ($student, $id) {
    return (int) $student->id === (int) $id;
}, ['guards' => ['student']]);
