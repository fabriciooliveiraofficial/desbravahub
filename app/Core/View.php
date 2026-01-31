<?php
namespace App\Core;

class View
{
    /**
     * Render a view with a layout
     * 
     * @param string $viewPath path to the view file (relative to views/)
     * @param array $data data to be extracted to the view
     * @param string $layout path to the layout file (relative to views/layouts/)
     */
    public static function render(string $viewPath, array $data = [], string $layout = 'admin'): void
    {
        // Extract data to be available in the view
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = BASE_PATH . '/views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("View not found: {$viewPath}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Check for HTMX request
        $headers = getallheaders();
        $isHtmx = isset($headers['HX-Request']) || isset($_SERVER['HTTP_HX_REQUEST']);

        if ($isHtmx) {
            // If it's an HTMX request, render only the content (partial)
            // unless HX-Boosted, which usually expects full page updates if checking targets, 
            // but for simple navigation (hx-boost), the body swap handles it.
            // Actually, standard hx-boost expects the full page or at least the body content mostly.
            // Ideally for 'seamless navigation' via hx-boost on body, we return the full page 
            // OR we can optimize to return just the body content if we know the target.
            // For now, let's keep simple: HTMX requests often want partials for modals/dynamic parts,
            // but for hx-boost navigation, they expect the full doc usually to swap Title/Content.
            // 
            // However, the prompt asked for "SPA template" and "seamless navigation". 
            // Usually with HTMX, we can detect if it's a boosted request and return just the main content 
            // if the target is #main-content.

            // if (isset($headers['HX-Target']) && $headers['HX-Target'] == 'main-content') {
            //    // Render only the content part if targeting main content
            //    echo $content;
            //    return;
            // }

            // If generic HTMX or Boost, we might still want the layout or partial.
            // Let's assume for standard navigation we might still render full layout for now to be safe with hx-boost
            // UNLESS we specifically target a container.
            // But to make it "SPA-like" with hx-boost=true on body, it swaps body.

            // Let's stick to returning full page for now unless specifically optimizing.
            // BUT, the goal is often to save bandwidth/rendering.
            // Let's implement a 'layout-less' render if requested explicitly via a header or var,
            // otherwise render layout.
        }

        // Render layout
        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            // Fallback if layout doesn't exist
            echo $content;
            return;
        }

        require $layoutFile;
    }
}
