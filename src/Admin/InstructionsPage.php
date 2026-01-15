<?php
/**
 * Pagina Istruzioni ChatGPT
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\Admin;

defined('ABSPATH') || exit;

/**
 * Classe per gestire la pagina delle istruzioni ChatGPT
 */
class InstructionsPage {
    
    /**
     * Costruttore
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_instructions_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }
    
    /**
     * Aggiunge la pagina al menu
     */
    public function add_instructions_page() {
        add_submenu_page(
            'edit.php?post_type=fp_landing_page',
            __('Istruzioni ChatGPT', 'fp-landing-page'),
            __('Istruzioni ChatGPT', 'fp-landing-page'),
            'edit_posts',
            'fp-landing-page-instructions',
            [$this, 'render_page']
        );
    }
    
    /**
     * Carica stili per la pagina
     */
    public function enqueue_styles($hook) {
        if ($hook !== 'landing-page_page_fp-landing-page-instructions') {
            return;
        }
        
        wp_add_inline_style('wp-admin', $this->get_inline_styles());
    }
    
    /**
     * Stili inline per la pagina istruzioni
     */
    private function get_inline_styles() {
        return '
            .fp-instructions-wrapper {
                max-width: 1200px;
                margin: 20px auto;
                padding: 0 20px;
            }
            .fp-instructions-content {
                background: #fff;
                padding: 30px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                border-radius: 4px;
            }
            .fp-instructions-content h1 {
                font-size: 28px;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #2271b1;
            }
            .fp-instructions-content h2 {
                font-size: 22px;
                margin-top: 30px;
                margin-bottom: 15px;
                color: #1d2327;
                padding-bottom: 10px;
                border-bottom: 1px solid #ddd;
            }
            .fp-instructions-content h3 {
                font-size: 18px;
                margin-top: 25px;
                margin-bottom: 12px;
                color: #1d2327;
            }
            .fp-instructions-content pre {
                background: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                overflow-x: auto;
                font-family: "Courier New", Courier, monospace;
                font-size: 13px;
                line-height: 1.6;
                margin: 15px 0;
            }
            .fp-instructions-content code {
                background: #f5f5f5;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: "Courier New", Courier, monospace;
                font-size: 13px;
            }
            .fp-instructions-content pre code {
                background: transparent;
                padding: 0;
            }
            .fp-instructions-content ul,
            .fp-instructions-content ol {
                margin: 15px 0;
                padding-left: 30px;
            }
            .fp-instructions-content li {
                margin: 8px 0;
                line-height: 1.6;
            }
            .fp-instructions-content p {
                margin: 15px 0;
                line-height: 1.6;
            }
            .fp-instructions-content strong {
                font-weight: 600;
                color: #1d2327;
            }
            .fp-instructions-content hr {
                border: none;
                border-top: 2px solid #ddd;
                margin: 30px 0;
            }
            .fp-instructions-content table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .fp-instructions-content table th,
            .fp-instructions-content table td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            .fp-instructions-content table th {
                background: #f5f5f5;
                font-weight: 600;
            }
            .fp-instructions-copy-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                background: #2271b1;
                color: #fff;
                border: none;
                padding: 5px 10px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 12px;
            }
            .fp-instructions-copy-btn:hover {
                background: #135e96;
            }
            .fp-instructions-content .code-block-wrapper {
                position: relative;
                margin: 15px 0;
            }
        ';
    }
    
    /**
     * Renderizza la pagina
     */
    public function render_page() {
        $instructions_file = FP_LANDING_PAGE_DIR . 'ISTRUZIONI-CHATGPT.md';
        
        if (!file_exists($instructions_file)) {
            echo '<div class="wrap"><h1>' . esc_html__('Istruzioni ChatGPT', 'fp-landing-page') . '</h1>';
            echo '<p>' . esc_html__('File istruzioni non trovato.', 'fp-landing-page') . '</p></div>';
            return;
        }
        
        $content = file_get_contents($instructions_file);
        $html = $this->markdown_to_html($content);
        
        ?>
        <div class="wrap fp-instructions-wrapper">
            <div class="fp-instructions-content">
                <?php echo $html; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Converte markdown semplice in HTML
     */
    private function markdown_to_html($markdown) {
        // Salva i code blocks prima di processare
        $code_blocks = [];
        $block_index = 0;
        
        // Estrai code blocks
        $markdown = preg_replace_callback('/```(\w+)?\n(.*?)```/s', function($matches) use (&$code_blocks, &$block_index) {
            $lang = !empty($matches[1]) ? $matches[1] : '';
            $code = $matches[2];
            $placeholder = "___CODE_BLOCK_{$block_index}___";
            $code_blocks[$block_index] = [
                'lang' => $lang,
                'code' => htmlspecialchars($code, ENT_QUOTES, 'UTF-8')
            ];
            $block_index++;
            return $placeholder;
        }, $markdown);
        
        // Escape HTML di base
        $html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
        
        // Ripristina code blocks
        foreach ($code_blocks as $index => $block) {
            $placeholder = "___CODE_BLOCK_{$index}___";
            $code_html = '<div class="code-block-wrapper"><pre><code class="language-' . esc_attr($block['lang']) . '">' . $block['code'] . '</code></pre></div>';
            $html = str_replace($placeholder, $code_html, $html);
        }
        
        // Code inline (dopo aver ripristinato i code blocks)
        $html = preg_replace_callback('/`([^`]+)`/', function($matches) {
            // Non processare se è già dentro un code block
            return '<code>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</code>';
        }, $html);
        
        // Headers
        $html = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^##\s+(.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^#\s+(.+)$/m', '<h1>$1</h1>', $html);
        
        // Bold
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        
        // HR
        $html = preg_replace('/^---$/m', '<hr>', $html);
        
        // Liste non ordinate
        $lines = explode("\n", $html);
        $result = [];
        $in_list = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Code blocks già processati - non toccare
            if (strpos($line, '___CODE_BLOCK_') !== false || strpos($line, 'code-block-wrapper') !== false) {
                $result[] = $line;
                continue;
            }
            
            // Headers - non toccare
            if (preg_match('/^<h[1-6]>/', $trimmed)) {
                if ($in_list) {
                    $result[] = '</ul>';
                    $in_list = false;
                }
                $result[] = $line;
                continue;
            }
            
            // Liste non ordinate
            if (preg_match('/^[\*\-]\s+(.+)$/', $trimmed, $matches)) {
                if (!$in_list) {
                    $result[] = '<ul>';
                    $in_list = true;
                }
                $result[] = '<li>' . $matches[1] . '</li>';
            } 
            // Liste ordinate
            elseif (preg_match('/^(\d+)\.\s+(.+)$/', $trimmed, $matches)) {
                if (!$in_list) {
                    $result[] = '<ul>';
                    $in_list = true;
                }
                $result[] = '<li>' . $matches[2] . '</li>';
            }
            // Linea vuota
            elseif (empty($trimmed)) {
                if ($in_list) {
                    $result[] = '</ul>';
                    $in_list = false;
                }
                $result[] = '';
            }
            // Altro - paragrafo o altro contenuto
            else {
                if ($in_list) {
                    $result[] = '</ul>';
                    $in_list = false;
                }
                // Se non è già un tag HTML, wrappa in paragrafo
                if (!preg_match('/^<\/?[a-z]/', $trimmed)) {
                    $result[] = '<p>' . $trimmed . '</p>';
                } else {
                    $result[] = $line;
                }
            }
        }
        
        // Chiudi eventuale lista aperta
        if ($in_list) {
            $result[] = '</ul>';
        }
        
        $html = implode("\n", $result);
        
        return wp_kses_post($html);
    }
}
