<?php
// FILE: /app/models/Risk.php

class Risk extends Model {
    protected $table = 'risks';
    protected $tenantIsolation = true;

    /**
     * Get risks by category
     */
    public function getByCategory($category) {
        return $this->findAll(['category' => $category]);
    }

    /**
     * Get open risks
     */
    public function getOpenRisks() {
        return $this->findAll(['status' => 'open'], 'inherent_risk DESC');
    }

    /**
     * Get high-risk items
     */
    public function getHighRisks($threshold = 15) {
        $sql = "SELECT * FROM risks
                WHERE tenant_id = ? AND inherent_risk >= ? AND status = 'open'
                ORDER BY inherent_risk DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $threshold]);
    }

    /**
     * Get risk heatmap data
     */
    public function getHeatmapData() {
        $sql = "SELECT likelihood, impact, COUNT(*) as count
                FROM risks
                WHERE tenant_id = ? AND status = 'open'
                GROUP BY likelihood, impact";

        return $this->db->fetchAll($sql, [Auth::tenantId()]);
    }

    /**
     * Get risk statistics
     */
    public function getStatistics() {
        $sql = "SELECT
                    COUNT(*) as total_risks,
                    AVG(inherent_risk) as avg_inherent_risk,
                    AVG(residual_risk) as avg_residual_risk,
                    SUM(CASE WHEN inherent_risk >= 15 THEN 1 ELSE 0 END) as high_risks,
                    SUM(CASE WHEN inherent_risk >= 8 AND inherent_risk < 15 THEN 1 ELSE 0 END) as medium_risks,
                    SUM(CASE WHEN inherent_risk < 8 THEN 1 ELSE 0 END) as low_risks
                FROM risks
                WHERE tenant_id = ? AND status = 'open'";

        return $this->db->fetchOne($sql, [Auth::tenantId()]);
    }

    /**
     * Calculate and update risk scores after create/update
     * Since generated columns were removed for MySQL 5.7 compatibility,
     * we calculate these values in the application layer
     */
    public function calculateRiskScores($id) {
        $risk = $this->findById($id);

        if (!$risk) {
            return false;
        }

        // Calculate inherent risk (likelihood × impact)
        $inherentRisk = ($risk['likelihood'] ?? 0) * ($risk['impact'] ?? 0);

        // Calculate residual risk (inherent risk - control effectiveness)
        $residualRisk = $inherentRisk - ($risk['control_effectiveness'] ?? 0);

        // Ensure residual risk is never negative
        $residualRisk = max(0, $residualRisk);

        // Update the risk scores
        return $this->update($id, [
            'inherent_risk' => $inherentRisk,
            'residual_risk' => $residualRisk
        ]);
    }
}
