<?php
/**
 * Activity Controller
 * 
 * Handles activity-related API endpoints.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\ActivityService;
use App\Services\ProofService;
use App\Services\ProgressionService;
use App\Services\QuizService;

class ActivityController
{
    private ActivityService $activityService;
    private ProofService $proofService;
    private ProgressionService $progressionService;

    public function __construct()
    {
        $this->activityService = new ActivityService();
        $this->proofService = new ProofService();
        $this->progressionService = new ProgressionService();
    }

    /**
     * List available activities for current user
     */
    public function index(): void
    {
        $user = App::user();
        $activities = $this->activityService->getAvailableForUser($user['id']);

        $this->json(['activities' => array_values($activities)]);
    }

    /**
     * Get single activity details
     */
    public function show(array $params): void
    {
        $activity = $this->activityService->findById((int) $params['id']);

        if (!$activity) {
            $this->jsonError('Activity not found', 404);
            return;
        }

        $activity['prerequisites'] = $this->activityService->getPrerequisites($activity['id']);
        $activity['proof_types'] = json_decode($activity['proof_types'], true);

        $this->json(['activity' => $activity]);
    }

    /**
     * Start an activity
     */
    public function start(array $params): void
    {
        $user = App::user();
        $activityId = (int) $params['id'];

        $result = $this->activityService->startActivity($user['id'], $activityId);

        if ($result['success']) {
            $this->json($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }

    /**
     * Submit proof for an activity
     */
    public function submitProof(array $params): void
    {
        $userActivityId = (int) ($_POST['user_activity_id'] ?? 0);
        $proofType = $_POST['type'] ?? '';

        if (!$userActivityId) {
            $this->jsonError('User activity ID required', 400);
            return;
        }

        switch ($proofType) {
            case 'url':
                $url = $_POST['url'] ?? '';
                $result = $this->proofService->submitUrlProof($userActivityId, $url);
                break;

            case 'upload':
                if (empty($_FILES['file'])) {
                    $this->jsonError('File required', 400);
                    return;
                }
                $result = $this->proofService->submitUploadProof($userActivityId, $_FILES['file']);
                break;

            default:
                $this->jsonError('Invalid proof type', 400);
                return;
        }

        if ($result['success']) {
            $this->json($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }

    /**
     * Get user's activity progress
     */
    public function myProgress(): void
    {
        $user = App::user();
        $progress = $this->progressionService->getUserProgress($user['id']);
        $achievements = $this->progressionService->getUserAchievements($user['id']);

        $this->json([
            'progress' => $progress,
            'achievements' => $achievements,
        ]);
    }

    /**
     * Get leaderboard
     */
    public function leaderboard(): void
    {
        $limit = (int) ($_GET['limit'] ?? 10);
        $leaderboard = $this->progressionService->getLeaderboard(min($limit, 50));

        $this->json(['leaderboard' => $leaderboard]);
    }

    /**
     * Get pending proofs for review (directors only)
     */
    public function pendingProofs(): void
    {
        if (!can('proofs.review')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $proofs = $this->proofService->getPendingProofs();
        $this->json(['proofs' => $proofs]);
    }

    /**
     * Review a proof (directors only)
     */
    public function reviewProof(array $params): void
    {
        if (!can('proofs.review')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $proofId = (int) $params['id'];
        $action = $_POST['action'] ?? '';
        $comment = $_POST['comment'] ?? null;

        $result = $this->proofService->reviewProof($proofId, $action, $comment);

        if ($result['success']) {
            $this->json(['message' => 'Proof reviewed successfully']);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }

    /**
     * Send JSON response
     */
    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Send JSON error
     */
    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
