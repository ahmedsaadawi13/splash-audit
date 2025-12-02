<?php
/**
 * PDF Export Helper
 * Generates PDF reports using FPDF or similar library
 * Note: Requires FPDF library - install via: composer require setasign/fpdf
 */

class PdfExport {
    private $pdf;
    private $title;

    public function __construct() {
        // Initialize PDF (would use FPDF in production)
        // For now, this is a template for PDF generation
    }

    /**
     * Export findings report to PDF
     */
    public function exportFindingsReport($findings, $filters = []) {
        $html = $this->generateFindingsReportHtml($findings, $filters);
        return $this->convertHtmlToPdf($html, 'Findings_Report.pdf');
    }

    /**
     * Export audit summary to PDF
     */
    public function exportAuditSummary($auditPlan, $statistics) {
        $html = $this->generateAuditSummaryHtml($auditPlan, $statistics);
        return $this->convertHtmlToPdf($html, 'Audit_Summary.pdf');
    }

    /**
     * Export risk register to PDF
     */
    public function exportRiskRegister($risks, $statistics) {
        $html = $this->generateRiskRegisterHtml($risks, $statistics);
        return $this->convertHtmlToPdf($html, 'Risk_Register.pdf');
    }

    /**
     * Generate findings report HTML
     */
    private function generateFindingsReportHtml($findings, $filters) {
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #2c3e50; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background: #3498db; color: white; padding: 10px; text-align: left; }
                td { padding: 10px; border-bottom: 1px solid #ddd; }
                .high { color: #e74c3c; font-weight: bold; }
                .medium { color: #f39c12; font-weight: bold; }
                .low { color: #3498db; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Findings Report</h1>
            <p>Generated: " . date('Y-m-d H:i:s') . "</p>
            <p>Total Findings: " . count($findings) . "</p>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($findings as $finding) {
            $severityClass = strtolower($finding['severity']);
            $html .= "
                    <tr>
                        <td>{$finding['id']}</td>
                        <td>{$finding['title']}</td>
                        <td class=\"$severityClass\">" . strtoupper($finding['severity']) . "</td>
                        <td>{$finding['status']}</td>
                        <td>" . date('Y-m-d', strtotime($finding['created_at'])) . "</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        return $html;
    }

    /**
     * Generate audit summary HTML
     */
    private function generateAuditSummaryHtml($auditPlan, $statistics) {
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1, h2 { color: #2c3e50; }
                .stat-box { background: #ecf0f1; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .stat-label { font-weight: bold; }
                .stat-value { font-size: 24px; color: #3498db; }
            </style>
        </head>
        <body>
            <h1>Audit Summary Report</h1>
            <h2>{$auditPlan['title']}</h2>
            <p><strong>Period:</strong> {$auditPlan['planned_start']} to {$auditPlan['planned_end']}</p>
            <p><strong>Status:</strong> {$auditPlan['status']}</p>

            <div class=\"stat-box\">
                <div class=\"stat-label\">Total Findings</div>
                <div class=\"stat-value\">{$statistics['total_findings']}</div>
            </div>

            <div class=\"stat-box\">
                <div class=\"stat-label\">High Severity</div>
                <div class=\"stat-value\">{$statistics['high_count']}</div>
            </div>

            <div class=\"stat-box\">
                <div class=\"stat-label\">Medium Severity</div>
                <div class=\"stat-value\">{$statistics['medium_count']}</div>
            </div>

            <div class=\"stat-box\">
                <div class=\"stat-label\">Low Severity</div>
                <div class=\"stat-value\">{$statistics['low_count']}</div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Generate risk register HTML
     */
    private function generateRiskRegisterHtml($risks, $statistics) {
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #2c3e50; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background: #e74c3c; color: white; padding: 10px; }
                td { padding: 10px; border-bottom: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <h1>Risk Register</h1>
            <p>Generated: " . date('Y-m-d H:i:s') . "</p>

            <table>
                <thead>
                    <tr>
                        <th>Risk</th>
                        <th>Category</th>
                        <th>Likelihood</th>
                        <th>Impact</th>
                        <th>Inherent Risk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($risks as $risk) {
            $html .= "
                    <tr>
                        <td>{$risk['title']}</td>
                        <td>{$risk['category']}</td>
                        <td>{$risk['likelihood']}</td>
                        <td>{$risk['impact']}</td>
                        <td>{$risk['inherent_risk']}</td>
                        <td>{$risk['status']}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        return $html;
    }

    /**
     * Convert HTML to PDF
     * In production, this would use a library like FPDF, TCPDF, or Dompdf
     */
    private function convertHtmlToPdf($html, $filename) {
        // Option 1: Use wkhtmltopdf (command-line tool)
        // Option 2: Use Dompdf library (composer require dompdf/dompdf)
        // Option 3: Use TCPDF library (composer require tecnickcom/tcpdf)

        // For now, return HTML (in production, convert to actual PDF)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // This is a placeholder - actual implementation would use a PDF library
        return [
            'success' => true,
            'filename' => $filename,
            'html' => $html,
            'note' => 'Install PDF library for actual PDF generation: composer require dompdf/dompdf'
        ];
    }

    /**
     * Quick PDF download (CSV alternative for now)
     */
    public static function exportToCsv($data, $headers, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, $headers);

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
