<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    /**
     * Créer un nouvel utilisateur
     */
    public function create(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'] ?? 'team_manager',
        ]);
    }

    /**
     * Trouver un utilisateur par email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Trouver un utilisateur par ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Sauvegarder le code OTP de vérification d'email (expire dans 10 min)
     */
    public function setEmailVerificationCode(User $user, string $code): void
    {
        $user->update([
            'email_verification_code'        => $code,
            'email_verification_expires_at'  => now()->addMinutes(10),
        ]);
    }

    /**
     * Marquer l'email comme vérifié et effacer l'OTP
     */
    public function markEmailAsVerified(User $user): void
    {
        $user->update([
            'email_verified_at'              => now(),
            'email_verification_code'        => null,
            'email_verification_expires_at'  => null,
        ]);
    }

    /**
     * Sauvegarder le code OTP de réinitialisation du mot de passe (expire dans 10 min)
     */
    public function setPasswordResetCode(User $user, string $code): void
    {
        $user->update([
            'password_reset_code'        => $code,
            'password_reset_expires_at'  => now()->addMinutes(10),
        ]);
    }

    /**
     * Réinitialiser le mot de passe et effacer l'OTP
     */
    public function resetPassword(User $user, string $newPassword): void
    {
        $user->update([
            'password'                   => Hash::make($newPassword),
            'password_reset_code'        => null,
            'password_reset_expires_at'  => null,
        ]);
    }
}