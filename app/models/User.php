<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function findByIdentity(string $identity): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM hris_users WHERE username = :username OR email = :email LIMIT 1',
            [
                'username' => $identity,
                'email' => $identity,
            ]
        );
    }
}
