<div class="page-header">
    <h2>Risk Heatmap</h2>
    <a href="/risk" class="btn">← Back to Risk Register</a>
</div>
<div class="content-section">
    <div style="margin: 30px auto; max-width: 600px;">
        <table class="data-table" style="text-align: center;">
            <thead>
                <tr><th>Likelihood / Impact</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th></tr>
            </thead>
            <tbody>
                <?php for ($l = 5; $l >= 1; $l--): ?>
                    <tr>
                        <td><strong><?php echo $l; ?></strong></td>
                        <?php for ($i = 1; $i <= 5; $i++):
                            $score = $l * $i;
                            $color = $score >= 15 ? '#e74c3c' : ($score >= 8 ? '#f39c12' : '#3498db');
                            $count = 0;
                            if (!empty($heatmapData)) {
                                foreach ($heatmapData as $item) {
                                    if ($item['likelihood'] == $l && $item['impact'] == $i) {
                                        $count = $item['count'];
                                        break;
                                    }
                                }
                            }
                        ?>
                            <td style="background-color: <?php echo $color; ?>; color: white; font-weight: bold; padding: 30px;">
                                <?php echo $count > 0 ? $count : ''; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <div style="margin-top: 20px; text-align: center;">
            <span style="display: inline-block; margin: 0 10px;"><span style="display: inline-block; width: 20px; height: 20px; background: #e74c3c; vertical-align: middle;"></span> High (15-25)</span>
            <span style="display: inline-block; margin: 0 10px;"><span style="display: inline-block; width: 20px; height: 20px; background: #f39c12; vertical-align: middle;"></span> Medium (8-14)</span>
            <span style="display: inline-block; margin: 0 10px;"><span style="display: inline-block; width: 20px; height: 20px; background: #3498db; vertical-align: middle;"></span> Low (1-7)</span>
        </div>
    </div>
</div>
