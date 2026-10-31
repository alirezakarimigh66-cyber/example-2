<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::User => 'dashboard',
        };
    }
}
