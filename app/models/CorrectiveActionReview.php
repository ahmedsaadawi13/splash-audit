<?php
// FILE: /app/models/CorrectiveActionReview.php

class CorrectiveActionReview extends Model {
    protected $table = 'corrective_action_reviews';
    protected $tenantIsolation = true;

    /**
     * Get reviews by corrective action
     */
    public function getByAction($correctiveActionId) {
        $sql = "SELECT car.*,
                       u.first_name,
                       u.last_name,
                       u.email
                FROM corrective_action_reviews car
                LEFT JOIN users u ON car.reviewer_user_id = u.id
                WHERE car.tenant_id = ? AND car.corrective_action_id = ?
                ORDER BY car.review_date DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $correctiveActionId]);
    }

    /**
     * Get reviews by reviewer
     */
    public function getByReviewer($reviewerId) {
        $sql = "SELECT car.*,
                       ca.action_description,
                       f.title as finding_title
                FROM corrective_action_reviews car
                JOIN corrective_actions ca ON car.corrective_action_id = ca.id
                LEFT JOIN findings f ON ca.finding_id = f.id
                WHERE car.tenant_id = ? AND car.reviewer_user_id = ?
                ORDER BY car.review_date DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $reviewerId]);
    }

    /**
     * Get reviews by status
     */
    public function getByStatus($status) {
        $sql = "SELECT car.*,
                       ca.action_description,
                       u.first_name,
                       u.last_name
                FROM corrective_action_reviews car
                JOIN corrective_actions ca ON car.corrective_action_id = ca.id
                LEFT JOIN users u ON car.reviewer_user_id = u.id
                WHERE car.tenant_id = ? AND car.status = ?
                ORDER BY car.review_date DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $status]);
    }

    /**
     * Get latest review for action
     */
    public function getLatestReview($correctiveActionId) {
        return $this->db->fetchOne(
            'SELECT * FROM corrective_action_reviews
             WHERE tenant_id = ? AND corrective_action_id = ?
             ORDER BY review_date DESC
             LIMIT 1',
            [Auth::tenantId(), $correctiveActionId]
        );
    }

    /**
     * Get review statistics
     */
    public function getStatistics() {
        $sql = "SELECT
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                    SUM(CASE WHEN status = 'needs_revision' THEN 1 ELSE 0 END) as needs_revision_count
                FROM corrective_action_reviews
                WHERE tenant_id = ?";

        return $this->db->fetchOne($sql, [Auth::tenantId()]);
    }

    /**
     * Check if action has been reviewed
     */
    public function hasBeenReviewed($correctiveActionId) {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM corrective_action_reviews
             WHERE tenant_id = ? AND corrective_action_id = ?',
            [Auth::tenantId(), $correctiveActionId]
        );

        return $result && $result['count'] > 0;
    }

    /**
     * Get pending reviews count for reviewer
     */
    public function getPendingReviewsCount($reviewerId) {
        $sql = "SELECT COUNT(*) as count
                FROM corrective_actions ca
                WHERE ca.tenant_id = ?
                AND ca.status = 'pending_review'
                AND ca.responsible_user_id = ?";

        $result = $this->db->fetchOne($sql, [Auth::tenantId(), $reviewerId]);

        return $result ? (int)$result['count'] : 0;
    }
}
