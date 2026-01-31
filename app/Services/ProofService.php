<?php
/**
 * Proof Service
 * 
 * Handles activity proof submissions and validation.
 */

namespace App\Services;

use App\Core\App;

class ProofService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4', 'webm'];
    private const MAX_FILE_SIZE = 10485760; // 10MB

    /**
     * Submit a URL proof
     */
    public function submitUrlProof(int $userActivityId, string $url): array
    {
        $tenantId = App::tenantId();

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'Invalid URL'];
        }

        // Validate user activity belongs to current user
        $userActivity = $this->validateUserActivity($userActivityId);
        if (!$userActivity) {
            return ['success' => false, 'error' => 'Invalid activity'];
        }

        // Create proof record
        $proofId = db_insert('activity_proofs', [
            'user_activity_id' => $userActivityId,
            'tenant_id' => $tenantId,
            'type' => 'url',
            'content' => $url,
            'status' => 'pending',
        ]);

        // Update user activity status
        db_update('user_activities', ['status' => 'pending_review'], 'id = ?', [$userActivityId]);

        return ['success' => true, 'proof_id' => $proofId];
    }

    /**
     * Submit a file upload proof
     */
    public function submitUploadProof(int $userActivityId, array $file): array
    {
        $tenantId = App::tenantId();

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload error'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File too large (max 10MB)'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }

        // Validate user activity
        $userActivity = $this->validateUserActivity($userActivityId);
        if (!$userActivity) {
            return ['success' => false, 'error' => 'Invalid activity'];
        }

        // Generate unique filename
        $filename = sprintf(
            '%d_%d_%s.%s',
            $tenantId,
            $userActivityId,
            bin2hex(random_bytes(8)),
            $extension
        );

        // Move uploaded file
        $uploadDir = BASE_PATH . '/storage/proofs/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }

        // Store relative path
        $relativePath = 'storage/proofs/' . date('Y/m') . '/' . $filename;

        // Create proof record
        $proofId = db_insert('activity_proofs', [
            'user_activity_id' => $userActivityId,
            'tenant_id' => $tenantId,
            'type' => 'upload',
            'content' => $relativePath,
            'status' => 'pending',
        ]);

        db_update('user_activities', ['status' => 'pending_review'], 'id = ?', [$userActivityId]);

        return ['success' => true, 'proof_id' => $proofId, 'path' => $relativePath];
    }

    /**
     * Submit quiz proof (auto-validated)
     */
    public function submitQuizProof(int $userActivityId, int $quizAttemptId, bool $passed): array
    {
        $tenantId = App::tenantId();

        $userActivity = $this->validateUserActivity($userActivityId);
        if (!$userActivity) {
            return ['success' => false, 'error' => 'Invalid activity'];
        }

        $status = $passed ? 'approved' : 'rejected';

        $proofId = db_insert('activity_proofs', [
            'user_activity_id' => $userActivityId,
            'tenant_id' => $tenantId,
            'type' => 'quiz',
            'quiz_attempt_id' => $quizAttemptId,
            'status' => $status,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        if ($passed) {
            $this->completeActivity($userActivityId);
        }

        return ['success' => true, 'proof_id' => $proofId, 'auto_validated' => true, 'passed' => $passed];
    }

    /**
     * Review a proof (for directors)
     */
    public function reviewProof(int $proofId, string $action, ?string $comment = null): array
    {
        $tenantId = App::tenantId();
        $reviewer = App::user();

        if (!in_array($action, ['approved', 'rejected', 'requested_changes'])) {
            return ['success' => false, 'error' => 'Invalid action'];
        }

        $proof = db_fetch_one(
            "SELECT * FROM activity_proofs WHERE id = ? AND tenant_id = ?",
            [$proofId, $tenantId]
        );

        if (!$proof) {
            return ['success' => false, 'error' => 'Proof not found'];
        }

        // Update proof status
        db_update('activity_proofs', [
            'status' => $action === 'requested_changes' ? 'pending' : $action,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $reviewer['id'],
        ], 'id = ?', [$proofId]);

        // Create review record
        db_insert('proof_reviews', [
            'proof_id' => $proofId,
            'reviewer_id' => $reviewer['id'],
            'action' => $action,
            'comment' => $comment,
        ]);

        // If approved, check if all proofs are approved
        if ($action === 'approved') {
            $this->checkAllProofsApproved($proof['user_activity_id']);
        }

        return ['success' => true];
    }

    /**
     * Check if all proofs for an activity are approved
     */
    private function checkAllProofsApproved(int $userActivityId): void
    {
        $pending = db_fetch_column(
            "SELECT COUNT(*) FROM activity_proofs WHERE user_activity_id = ? AND status != 'approved'",
            [$userActivityId]
        );

        if ($pending == 0) {
            $this->completeActivity($userActivityId);
        }
    }

    /**
     * Complete an activity and award XP
     */
    private function completeActivity(int $userActivityId): void
    {
        $userActivity = db_fetch_one("SELECT * FROM user_activities WHERE id = ?", [$userActivityId]);
        if (!$userActivity || $userActivity['status'] === 'completed') {
            return;
        }

        $activity = db_fetch_one("SELECT xp_reward FROM activities WHERE id = ?", [$userActivity['activity_id']]);
        $xpReward = $activity['xp_reward'] ?? 0;

        // Update user activity
        db_update('user_activities', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'xp_earned' => $xpReward,
        ], 'id = ?', [$userActivityId]);

        // Award XP to user
        if ($xpReward > 0) {
            $progressionService = new ProgressionService();
            $progressionService->addXp($userActivity['user_id'], $xpReward, 'activity_completion', $userActivity['activity_id']);
        }
    }

    /**
     * Validate user activity belongs to current user
     */
    private function validateUserActivity(int $userActivityId): ?array
    {
        $user = App::user();
        $tenantId = App::tenantId();

        return db_fetch_one(
            "SELECT * FROM user_activities WHERE id = ? AND user_id = ? AND tenant_id = ?",
            [$userActivityId, $user['id'], $tenantId]
        );
    }

    /**
     * Get proofs for a user activity
     */
    public function getProofsForActivity(int $userActivityId): array
    {
        return db_fetch_all(
            "SELECT p.*, u.name as reviewer_name
             FROM activity_proofs p
             LEFT JOIN users u ON p.reviewed_by = u.id
             WHERE p.user_activity_id = ?
             ORDER BY p.submitted_at DESC",
            [$userActivityId]
        );
    }

    /**
     * Get pending proofs for review (directors)
     */
    public function getPendingProofs(): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT p.*, ua.user_id, u.name as user_name, a.title as activity_title
             FROM activity_proofs p
             JOIN user_activities ua ON p.user_activity_id = ua.id
             JOIN users u ON ua.user_id = u.id
             JOIN activities a ON ua.activity_id = a.id
             WHERE p.tenant_id = ? AND p.status = 'pending'
             ORDER BY p.submitted_at ASC",
            [$tenantId]
        );
    }
}
