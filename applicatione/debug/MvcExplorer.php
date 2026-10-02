<?php
if (defined('ENVIRONMENT') && ENVIRONMENT == 'development') {
    // Cek apakah BUKAN AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {

        class MvcExplorer
        {
            private $CI;

            private $models = [];
            private $views = [];
            private $templates = [];
            private $functions = [];

            public function __construct()
            {
                $this->CI =& get_instance();
            }

            public function render()
            {
                try {

                    $controller = $this->CI->router->class;
                    $method     = $this->CI->router->method;

                    $ref  = new ReflectionClass($this->CI);
                    $file = $ref->getFileName();

                    if (!$file || !file_exists($file)) {
                        return;
                    }

                    $source = file_get_contents($file);

                    $this->scanMethod($source, $method);

                    echo '
                    <div id="mvcExplorer"
                    style="
                        position:fixed;
                        bottom:65px;
                        left:10px;
                        width:275px;
                        max-height:500px;
                        overflow:auto;
                        background:rgba(0,0,0,.5);
                        color:#fff;
                        z-index:99999999;
                        padding:12px;
                        font-family:Consolas,monospace;
                        font-size:12px;
                        border-radius:8px;
                        border:1px solid rgba(255,255,255,.15);
                        backdrop-filter:blur(3px);
                    ">';

                    echo '
                    <div style="
                        color:#fff;
                        font-size:16px;
                        font-weight:bold;
                        margin-bottom:10px;
                    ">
                        🐛 MVC EXPLORER
                    </div>';

                    echo $this->section(
                        'CONTROLLER',
                        '#66ff66',
                        [$controller]
                    );

                    echo $this->section(
                        'METHOD',
                        '#ffff66',
                        [$method]
                    );

                    echo $this->section(
                        'FILE',
                        '#cccccc',
                        [basename($file)]
                    );

                    echo $this->section(
                        'MODELS',
                        '#00ffff',
                        array_unique($this->models)
                    );

                    echo $this->section(
                        'VIEWS',
                        '#ff66ff',
                        array_unique($this->views)
                    );

                    echo $this->section(
                        'TEMPLATES',
                        '#ff9966',
                        array_unique($this->templates)
                    );

                    echo $this->section(
                        'FUNCTIONS',
                        '#99ccff',
                        array_unique($this->functions)
                    );

                    echo '
                    <div style="margin-top:10px;text-align:right;">
                        <button id="mvcExplorerHide"
                            style="
                                background:#222;
                                color:#fff;
                                border:1px solid #444;
                                padding:4px 8px;
                                cursor:pointer;
                                border-radius:4px;
                                position: absolute;
                                top: 10px;
                                right: 15px;
                            ">
                            Hide
                        </button>
                    </div>';

                    echo '</div>';

                    echo '
                    <div id="mvcExplorerMini"
                    style="
                        display:none;
                        position:fixed;
                        bottom:65px;
                        left:10px;
                        z-index:99999999;
                    ">
                        <button id="mvcExplorerShow"
                            style="
                                background:rgba(0,0,0,.7);
                                color:#66ff66;
                                border:1px solid #444;
                                padding:6px 10px;
                                cursor:pointer;
                                border-radius:20px;
                                font-family:Consolas,monospace;
                                font-size:12px;
                            ">
                            🐛 MVC
                        </button>
                    </div>';

                    echo '<script src="'.base_url('assets/js/mvc-explorer.js').'"></script>';

                } catch (Exception $e) {

                    echo '
                    <div style="
                        position:fixed;
                        bottom:10px;
                        right:10px;
                        background:#300;
                        color:#fff;
                        padding:10px;
                        z-index:99999999;
                        font-family:monospace;
                    ">
                        '.$e->getMessage().'
                    </div>';
                }
            }

            private function section($title, $color, $items)
            {
                $html = '';

                $html .= '
                <div style="margin-bottom:10px;">

                    <div style="
                        color:'.$color.';
                        font-weight:bold;
                        margin-bottom:4px;
                    ">
                        '.$title.'
                    </div>';

                if (empty($items)) {

                    $html .= '
                    <div style="color:#666;">
                        - none
                    </div>';

                } else {

                    foreach ($items as $item) {

                        $html .= '
                        <div>
                            • '.htmlspecialchars($item).'
                        </div>';
                    }
                }

                $html .= '</div>';

                return $html;
            }

            private function scanMethod($source, $methodName)
            {
                $pattern =
                    '/function\s+'
                    . preg_quote($methodName, '/')
                    . '\s*\([^)]*\)\s*\{([\s\S]*?)^\}/mi';

                if (!preg_match($pattern, $source, $match)) {
                    return;
                }

                $body = trim($match[1]);

                $this->findModels($body);
                $this->findViews($body);
                $this->findTemplatesAndViews($body);
                $this->findFunctions($body);
            }

            private function findModels($body)
            {
                preg_match_all(
                    '/load->model\s*\(\s*[\'"]([^\'"]+)[\'"]/i',
                    $body,
                    $matches
                );

                foreach (($matches[1] ?? []) as $model) {
                    $this->models[] = $model;
                }
            }

            private function findViews($body)
            {
                preg_match_all(
                    '/load->view\s*\(\s*[\'"]([^\'"]+)[\'"]/i',
                    $body,
                    $matches
                );

                foreach (($matches[1] ?? []) as $view) {
                    $this->views[] = $view;
                }
            }

            private function findTemplatesAndViews($body)
            {
                preg_match_all(
                    '/\$this->([a-zA-Z0-9_]*template[a-zA-Z0-9_]*)\s*\(\s*[\'"]([^\'"]+)[\'"]/i',
                    $body,
                    $matches,
                    PREG_SET_ORDER
                );

                foreach ($matches as $row) {

                    $this->templates[] = $row[1];
                    $this->views[]     = $row[2];
                }
            }

            private function findFunctions($body)
            {
                preg_match_all(
                    '/\$this->([a-zA-Z0-9_]+)\s*\(/',
                    $body,
                    $matches
                );

                $ignore = [
                    'load',
                    'input',
                    'db',
                    'session',
                    'config',
                    'uri',
                    'pagination',
                    'form_validation'
                ];

                foreach (($matches[1] ?? []) as $func)
                {
                    if (in_array($func, $ignore)) {
                        continue;
                    }

                    if (strpos(strtolower($func), 'template') !== false) {
                        continue;
                    }

                    $this->functions[] = $func;
                }
            }
        }
    }
}