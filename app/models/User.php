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

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_users WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        return $this->execute(
            'UPDATE hris_users SET password_hash = :hash WHERE id = :id',
            ['hash' => $passwordHash, 'id' => $id]
        );
    }
}
