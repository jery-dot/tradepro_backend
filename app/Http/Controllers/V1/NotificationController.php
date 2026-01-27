<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * GET /api/get_notifications
     *
     *
     *  Query Parameters:
     *   &page=1
     *   &limit=10
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request)
    {
        $user = auth('api')->user();
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 100) : 10; // basic page/limit handling.
        $query = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at');

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        $notifications = $paginator->getCollection();

        // Mark all returned notifications as read in one query
        $idsToMark = $notifications->pluck('id')->all();

        if (! empty($idsToMark)) {
            Notification::whereIn('id', $idsToMark)
                ->where('is_read', false)
                ->update(['is_read' => true]); // bulk update is more efficient than per-row updates.
        }

        // Re-load fresh is_read values for response
        $notifications = Notification::whereIn('id', $idsToMark)
            ->orderByDesc('created_at')
            ->get();

        $items = $notifications->map(function (Notification $n) {
            return [
                'id' => $n->public_id,
                'title' => $n->title,
                'description' => $n->description,
                'time' => optional($n->created_at)->format('h:i A'),
                'profile_image_url' => $n->profile_image_url,
                'created_at' => $n->created_at?->toIso8601String(),
                'is_read' => $n->is_read,
            ];
        })->values()->all(); // mapping collection to API payload is standard for custom notification systems.

        return response()->json([
            'status' => 'success',
            'message' => 'Notifications fetched successfully.',
            'count' => count($items),
            'data' => $items,
        ]);
    }

    /**
     * GET /api/get_notification_count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationCount(Request $request)
    {
        $user = auth('api')->user();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count(); // single-count query is more efficient than loading all records.

        return response()->json([
            'status' => 'success',
            'message' => 'Unread notification count fetched successfully.',
            'data' => [
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Send Notification API.
     *
     * Endpoint:
     * POST /api/send_notification
     *
     * Headers:
     *   Authorization: Bearer <ACCESS_TOKEN>
     *   Content-Type: application/json
     *
     * Request body:
     * {
     *   "receiver_id": "contractor_001",
     *   "title": "New Review Received",
     *   "message": "You have received a new 5-star review from Raj Mohal!",
     *   "notification_type": "review",
     *   "image_url": "https://example.com/profile/reviewer_001.png"
     * }
     *
     * Success Response:
     * {
     *   "status": "success",
     *   "message": "Notification sent successfully.",
     *   "data": {
     *     "receiver_id": "contractor_001",
     *     "title": "New Review Received",
     *     "message": "You have received a new 5-star review from Raj Mohal!",
     *     "notification_type": "review",
     *     "sent_at": "2025-10-01T12:45:00Z"
     *   }
     * }
     *
     * Error Responses:
     * {
     *   "status": "error",
     *   "message": "Required fields are missing.",
     *   "error_code": "VALIDATION_ERROR"
     * }
     * {
     *   "status": "error",
     *   "message": "Invalid or missing access token.",
     *   "error_code": "UNAUTHORIZED"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request)
{
    // 1. Authorization Check
    $sender = auth('api')->user();
    if (! $sender) {
        return ApiResponse::warning('Invalid or missing access token.', 401);
    }

    // 2. Validation (Using Validator::make to control the error response format)
    $validator = Validator::make($request->all(), [
        'receiver_id'       => 'required',
        'title'             => 'required|string|max:255',
        'message'           => 'required|string',
        'notification_type' => 'required|string|max:50',
        'image_url'         => 'nullable|url|max:500',
    ]);

    if ($validator->fails()) {
        return ApiResponse::error('Validation Error', 422, $validator->errors()->toArray());
    }

    $validated = $validator->validated();

    // 3. Parse and Find Receiver
    // $receiverId = $this->parseUserId($validated['receiver_id']);
    $receiverId = $validated['receiver_id'];

    if (! $receiverId) {
        return ApiResponse::error('Invalid receiver_id format.', 422);
    }

    $receiver = User::find($receiverId);
    if (! $receiver) {
        return ApiResponse::error('Receiver user not found.', 404);
    }

    // 4. Save Notification to Database
    try {
        $notification = Notification::create([
            'user_id'           => $receiverId,
            // 'public_id'         => $publicId,
            'title'             => $validated['title'],
            'description'       => $validated['message'],
            'profile_image_url' => $validated['image_url'] ?? null,
            'is_read'           => false,
        ]);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to save notification to database.', 500, ['error' => $e->getMessage()]);
    }

    // 5. Send FCM Push Notification
    // We wrap this in a try-catch so that if FCM fails, the API still returns success
    // (since the notification is saved in the DB).
    $result = "Empty";
    if (!empty($receiver->fcm_token)) {
    $result = "Empty1";
        try {
            // Call your existing helper function
            $result = send_notification_FCM(
                $receiver->fcm_token,
                $validated['title'],
                $validated['message']
            );
        } catch (\Exception $e) {
            Log::error("FCM Send Error for User {$receiverId}: " . $e->getMessage());
        }
    }

    // 6. Return Success Response using your Helper
    $responseData = [
        'data' => [
            'receiver_id'       => $validated['receiver_id'],
            'title'             => $notification->title,
            'message'           => $notification->description,
            'notification_type' => $validated['notification_type'],
            'sent_at'           => $notification->created_at?->toIso8601String(),
        ]
    ];

    return ApiResponse::success('Notification sent successfully.', $responseData, 201);
}
    public function sendNotificationOld(Request $request)
    {
        $sender = auth('api')->user();

        if (! $sender) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing access token.',
                'error_code' => 'UNAUTHORIZED',
            ], 401);
        }

        $validated = $request->validate([
            'receiver_id' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'notification_type' => 'required|string|max:50',
            'image_url' => 'nullable|url|max:500',
        ]);

        // Parse receiver_id to actual user ID
        $receiverId = $this->parseUserId($validated['receiver_id']);

        if (! $receiverId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid receiver_id format.',
                'error_code' => 'VALIDATION_ERROR',
            ], 422);
        }

        // Check receiver exists
        $receiver = User::find($receiverId);
        if (! $receiver) {
            return response()->json([
                'status' => 'error',
                'message' => 'Receiver user not found.',
                'error_code' => 'VALIDATION_ERROR',
            ], 422);
        }

        // Generate notification ID
        $nextId = Notification::where('user_id', $receiverId)->count() + 1;
        $publicId = 'notif_'.str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);

        $notification = Notification::create([
            'user_id' => $receiverId,
            'public_id' => $publicId,
            'title' => $validated['title'],
            'description' => $validated['message'],
            'profile_image_url' => $validated['image_url'] ?? null,
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent successfully.',
            'data' => [
                'receiver_id' => $validated['receiver_id'],
                'title' => $notification->title,
                'message' => $notification->description,
                'notification_type' => $validated['notification_type'],
                'sent_at' => $notification->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Parse user_id format to actual user ID.
     * contractor_001 → 1, laborer_002 → 2, apprentice_003 → 3, etc.
     */
    private function parseUserId(string $userId): ?int
    {
        if (! preg_match('/^(contractor|laborer|apprentice|subcontractor)_(\d+)$/', $userId, $matches)) {
            return null;
        }

        return (int) $matches[2];
    }
}
