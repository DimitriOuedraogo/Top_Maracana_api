<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Mail\VerifyEmailMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    /**
     * Générer un code OTP à 6 chiffres
     */
    private function generateOTP(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(array $data): array
    {
        // Créer l'utilisateur
        $user = $this->authRepository->create($data);

        // Générer OTP et l'envoyer par email
        $code = $this->generateOTP();
        $this->authRepository->setEmailVerificationCode($user, $code);
        Mail::to($user->email)->send(new VerifyEmailMail($user, $code));

        return [
            'message' => 'Inscription réussie. Un code de vérification a été envoyé à ' . $user->email,
            'user'    => $this->formatUser($user),
        ];
    }

    /**
     * Connexion
     */
    public function login(array $credentials): array
    {
        $user = $this->authRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Email ou mot de passe incorrect.', 401);
        }

        if (!$user->email_verified_at) {
            // Renvoyer un nouveau code automatiquement
            $code = $this->generateOTP();
            $this->authRepository->setEmailVerificationCode($user, $code);
            Mail::to($user->email)->send(new VerifyEmailMail($user, $code));

            throw new \Exception('Email non vérifié. Un nouveau code a été envoyé à ' . $user->email, 403);
        }

        $token = JWTAuth::fromUser($user);

        return [
            'message'      => 'Connexion réussie.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => config('jwt.ttl') * 60,
            'user'         => $this->formatUser($user),
        ];
    }

    /**
     * Déconnexion
     */
    public function logout(): array
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return ['message' => 'Déconnexion réussie.'];
    }

    /**
     * Rafraîchir le token JWT
     */
    public function refreshToken(): array
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        return [
            'access_token' => $newToken,
            'token_type'   => 'Bearer',
            'expires_in'   => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Vérifier l'email avec le code OTP
     */
    public function verifyEmail(string $email, string $code): array
    {
        $user = $this->authRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('Aucun compte associé à cet email.', 404);
        }

        if ($user->email_verified_at) {
            return ['message' => 'Email déjà vérifié. Vous pouvez vous connecter.'];
        }

        if ($user->email_verification_code !== $code) {
            throw new \Exception('Code incorrect.', 400);
        }

        if (now()->isAfter($user->email_verification_expires_at)) {
            throw new \Exception('Code expiré. Veuillez en demander un nouveau.', 400);
        }

        $this->authRepository->markEmailAsVerified($user);

        return ['message' => 'Email vérifié avec succès. Vous pouvez maintenant vous connecter.'];
    }

    /**
     * Renvoyer le code OTP de vérification
     */
    public function resendVerificationCode(string $email): array
    {
        $user = $this->authRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('Aucun compte associé à cet email.', 404);
        }

        if ($user->email_verified_at) {
            throw new \Exception('Cet email est déjà vérifié.', 400);
        }

        $code = $this->generateOTP();
        $this->authRepository->setEmailVerificationCode($user, $code);
        Mail::to($user->email)->send(new VerifyEmailMail($user, $code));

        return ['message' => 'Un nouveau code a été envoyé à ' . $email];
    }

    /**
     * Demander la réinitialisation du mot de passe
     */
    public function forgotPassword(string $email): array
    {
        $user = $this->authRepository->findByEmail($email);

        // Toujours retourner le même message pour des raisons de sécurité
        if (!$user) {
            return ['message' => 'Si cet email existe, un code a été envoyé.'];
        }

        $code = $this->generateOTP();
        $this->authRepository->setPasswordResetCode($user, $code);
        Mail::to($user->email)->send(new ResetPasswordMail($user, $code));

        return ['message' => 'Un code de réinitialisation a été envoyé à ' . $email];
    }

    /**
     * Vérifier le code OTP de reset password (étape intermédiaire)
     */
    public function verifyResetCode(string $email, string $code): array
    {
        $user = $this->authRepository->findByEmail($email);

        if (!$user || $user->password_reset_code !== $code) {
            throw new \Exception('Code incorrect.', 400);
        }

        if (now()->isAfter($user->password_reset_expires_at)) {
            throw new \Exception('Code expiré. Veuillez refaire une demande.', 400);
        }

        return ['message' => 'Code valide. Vous pouvez maintenant choisir un nouveau mot de passe.'];
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(array $data): array
    {
        $user = $this->authRepository->findByEmail($data['email']);

        if (!$user || $user->password_reset_code !== $data['code']) {
            throw new \Exception('Code incorrect.', 400);
        }

        if (now()->isAfter($user->password_reset_expires_at)) {
            throw new \Exception('Code expiré. Veuillez refaire une demande.', 400);
        }

        $this->authRepository->resetPassword($user, $data['password']);

        return ['message' => 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.'];
    }

    /**
     * Retourner l'utilisateur authentifié
     */
    public function me(): array
    {
        $user = JWTAuth::parseToken()->authenticate();

        return $this->formatUser($user);
    }

    /**
     * Formater les données utilisateur pour la réponse API
     */
    private function formatUser(User $user): array
    {
        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'role'              => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'created_at'        => $user->created_at,
        ];
    }
}