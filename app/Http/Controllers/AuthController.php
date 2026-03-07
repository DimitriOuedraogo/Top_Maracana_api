<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\ResendVerificationRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\VerifyResetCodeRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Top Maracana API",
 *     version="1.0.0",
 *     description="API de gestion de tournois de football Top Maracana"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Tag(name="Auth", description="Gestion de l'authentification")
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Inscription d'un nouvel utilisateur",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Test User"),
     *             @OA\Property(property="email", type="string", format="email", example="test@example9.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 nullable=true,
     *                 enum={"organizer","team_manager"},
     *                 default="organizer",
     *                 example="organizer",
     *                 description="Rôle de l'utilisateur."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inscription réussie. Vérifiez votre email.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="L'email est déjà utilisé.")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());
            return response()->json(['success' => true, ...$result], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Connexion d'un utilisateur",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example9.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants incorrects",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Identifiants incorrects.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Email non vérifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Veuillez vérifier votre email avant de vous connecter.")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Déconnexion de l'utilisateur connecté",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié.")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            $result = $this->authService->logout();
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Rafraîchir le token JWT",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié.")
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken();
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Récupérer les informations de l'utilisateur connecté",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Dimitri Ouedraogo"),
     *                 @OA\Property(property="email", type="string", format="email", example="dimitri@test.com"),
     *                 @OA\Property(property="role", type="string", enum={"organizer","team_manager"}, example="organizer"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2026-03-07T14:00:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-03-07T13:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2026-03-07T13:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié.")
     *         )
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->me();
            return response()->json(['success' => true, 'user' => $user], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/verify-email",
     *     summary="Vérifier l'email avec le code OTP",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example9.com"),
     *             @OA\Property(property="code", type="string", example="123456", description="Code OTP reçu par email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email vérifié avec succès.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Code invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code invalide ou expiré.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Utilisateur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur introuvable.")
     *         )
     *     )
     * )
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->verifyEmail($request->email, $request->code);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/resend-verification",
     *     summary="Renvoyer le code OTP de vérification",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="dimitri@test.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code renvoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code renvoyé avec succès.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Utilisateur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur introuvable.")
     *         )
     *     )
     * )
     */
    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->resendVerificationCode($request->email);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/forgot-password",
     *     summary="Demander un code OTP de réinitialisation du mot de passe",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="dimitri@test.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code de réinitialisation envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code de réinitialisation envoyé.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Utilisateur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur introuvable.")
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->forgotPassword($request->email);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/verify-reset-code",
     *     summary="Vérifier le code OTP avant réinitialisation du mot de passe",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code"},
     *             @OA\Property(property="email", type="string", format="email", example="dimitri@test.com"),
     *             @OA\Property(property="code", type="string", example="123456", description="Code OTP reçu par email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code valide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code valide.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Code invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code invalide ou expiré.")
     *         )
     *     )
     * )
     */
    public function verifyResetCode(VerifyResetCodeRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->verifyResetCode($request->email, $request->code);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/reset-password",
     *     summary="Réinitialiser le mot de passe avec le code OTP",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="dimitri@test.com"),
     *             @OA\Property(property="code", type="string", example="123456", description="Code OTP reçu par email"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Code invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code invalide ou expiré.")
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->resetPassword(
                $request->only('email', 'code', 'password')
            );
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleException(\Exception $e): JsonResponse
    {
        $code = in_array($e->getCode(), [400, 401, 403, 404, 422]) ? $e->getCode() : 500;
        return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
    }
}