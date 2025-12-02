<div class="page-header">
    <h2>Add Risk</h2>
    <a href="/risk" class="btn">← Back</a>
</div>
<div class="content-section">
    <form action="/risk/store" method="POST" class="form">
        <?php echo Session::csrfField(); ?>
        <div class="form-group">
            <label for="title">Risk Title *</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="operational">Operational</option>
                    <option value="financial">Financial</option>
                    <option value="compliance">Compliance</option>
                    <option value="strategic">Strategic</option>
                </select>
            </div>
            <div class="form-group">
                <label for="likelihood">Likelihood (1-5) *</label>
                <input type="number" id="likelihood" name="likelihood" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label for="impact">Impact (1-5) *</label>
                <input type="number" id="impact" name="impact" min="1" max="5" required>
            </div>
        </div>
        <div class="form-group">
            <label for="controls_description">Controls Description</label>
            <textarea id="controls_description" name="controls_description" rows="3"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Risk</button>
            <a href="/risk" class="btn">Cancel</a>
        </div>
    </form>
</div>
