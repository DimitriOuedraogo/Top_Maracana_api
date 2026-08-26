<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(): JsonResponse
    {
        $result = $this->notificationService->listForUser();
        return response()->json(['success' => true, ...$result]);
    }

    public function unreadCount(): JsonResponse
    {
        $result = $this->notificationService->unreadCount();
        return response()->json(['success' => true, ...$result]);
    }

    public function markRead(string $id): JsonResponse
    {
        $result = $this->notificationService->markRead($id);
        return response()->json(['success' => true, ...$result]);
    }

    public function markAllRead(): JsonResponse
    {
        $result = $this->notificationService->markAllRead();
        return response()->json(['success' => true, ...$result]);
    }
}
