<?php
// FILE: /app/core/View.php

class View {
    /**
     * Render view with layout
     */
    public static function render($view, $data = [], $layout = 'layouts/main') {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: $view");
        }

        // Get view content
        $content = ob_get_clean();

        // Include layout
        if ($layout) {
            $layoutFile = APP_PATH . '/views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Escape HTML
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate URL
     */
    public static function url($path = '') {
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Asset URL
     */
    public static function asset($path) {
        return self::url('assets/' . ltrim($path, '/'));
    }
}
