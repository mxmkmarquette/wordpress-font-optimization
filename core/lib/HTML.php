<?php
namespace O10n;

/**
 * HTML Minifier
 *
 * Enhanced version of HTML.php from Minify
 *
 * Some optimizations:
 * - line break every 500 characters @link https://developers.google.com/closure/compiler/faq?csw=1#linefeeds
 *
 * @link https://github.com/mrclay/minify/
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class HTMLMinify
{
    protected $_jsCleanComments = true;
    protected $_isXhtml = null;
    protected $_replacementHash = null;
    protected $_placeholders = array();

    final public function __construct()
    {

        // set replacement hash
        $this->_replacementHash = 'MINIFYHTML' . md5($_SERVER['REQUEST_TIME']);
    }

    /**
     * Minify HTML
     *
     * @param  string $HTML HTML string to minify.
     * @return string Minified HTML.
     */
    final public function minify($HTML)
    {
        if ($this->_isXhtml === null) {
            $this->_isXhtml = (false !== strpos($HTML, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML'));
        }

        $this->_placeholders = array();
        
        // replace SCRIPTs with placeholders
        $HTML = preg_replace_callback(
            '/(\\s*)<script(\\b[^>]*?>)([\\s\\S]*?)<\\/script>(\\s*)/i',
            array($this, '_removeScriptCB'),
            $HTML
        );
        
        // replace STYLEs with placeholders
        $HTML = preg_replace_callback(
            '/\\s*<style(\\b[^>]*>)([\\s\\S]*?)<\\/style>\\s*/i',
            array($this, '_removeStyleCB'),
            $HTML
        );

        // replace PREs with placeholders
        $HTML = preg_replace_callback('/\\s*<pre(\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/i', array($this, '_removePreCB'), $HTML);
        
        // replace TEXTAREAs with placeholders
        $HTML = preg_replace_callback(
            '/\\s*<textarea(\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/i',
            array($this, '_removeTextareaCB'),
            $HTML
        );
        
        // trim each line.
        // @todo take into account attribute values that span multiple lines.
        $HTML = preg_replace('/^\\s+|\\s+$/m', '', $HTML);
        
        // remove ws around block/undisplayed elements
        $HTML = preg_replace('/\\s+(<\\/?(?:area|base(?:font)?|blockquote|body'
            .'|caption|center|col(?:group)?|dd|dir|div|dl|dt|fieldset|form'
            .'|frame(?:set)?|h[1-6]|head|hr|html|legend|li|link|map|menu|meta'
            .'|ol|opt(?:group|ion)|p|param|t(?:able|body|head|d|h||r|foot|itle)'
            .'|ul)\\b[^>]*>)/i', '$1', $HTML);
        
        // remove ws outside of all elements
        $HTML = preg_replace(
            '/>(\\s(?:\\s*))?([^<]+)(\\s(?:\s*))?</',
            '>$1$2$3<',
            $HTML
        );
        
        // use newlines before 1st attribute in open tags (to limit line lengths)
        // @improved: every 500 characters
        $HTML = preg_replace('/(.{1,500}<[a-z\\-]+)\\s+([^>]+>)/is', "$1\n$2", $HTML);
        
        // fill placeholders
        $HTML = str_replace(
            array_keys($this->_placeholders),
            array_values($this->_placeholders),
            $HTML
        );
        // issue 229: multi-pass to catch scripts that didn't get replaced in textareas
        $HTML = str_replace(
            array_keys($this->_placeholders),
            array_values($this->_placeholders),
            $HTML
        );
        
        return $HTML;
    }
    
    final private function _reservePlace($content)
    {
        $placeholder = '%' . $this->_replacementHash . count($this->_placeholders) . '%';
        $this->_placeholders[$placeholder] = $content;

        return $placeholder;
    }
    final private function _removePreCB($m)
    {
        return $this->_reservePlace("<pre{$m[1]}");
    }
    
    final private function _removeTextareaCB($m)
    {
        return $this->_reservePlace("<textarea{$m[1]}");
    }

    final private function _removeStyleCB($m)
    {
        $openStyle = "<style{$m[1]}";
        $css = $m[2];
        // remove HTML comments
        $css = preg_replace('/(?:^\\s*<!--|-->\\s*$)/', '', $css);
        
        // remove CDATA section markers
        $css = $this->_removeCdata($css);
        
        // minify
        $css = trim($css);
        
        return $this->_reservePlace(
        
            $this->_needsCdata($css)
            ? "{$openStyle}/*<![CDATA[*/{$css}/*]]>*/</style>"
            : "{$openStyle}{$css}</style>"
        );
    }

    final private function _removeScriptCB($m)
    {
        $openScript = "<script{$m[2]}";
        $js = $m[3];
        
        // whitespace surrounding? preserve at least one space
        $ws1 = ($m[1] === '') ? '' : ' ';
        $ws2 = ($m[4] === '') ? '' : ' ';
        // remove HTML comments (and ending "//" if present)
        if ($this->_jsCleanComments) {
            $js = preg_replace('/(?:^\\s*<!--\\s*|\\s*(?:\\/\\/)?\\s*-->\\s*$)/', '', $js);
        }
        // remove CDATA section markers
        $js = $this->_removeCdata($js);
        
        // minify
        $js = trim($js);
        
        return $this->_reservePlace(
        
            $this->_needsCdata($js)
            ? "{$ws1}{$openScript}/*<![CDATA[*/{$js}/*]]>*/</script>{$ws2}"
            : "{$ws1}{$openScript}{$js}</script>{$ws2}"
        );
    }

    final private function _removeCdata($str)
    {
        return (false !== strpos($str, '<![CDATA['))
            ? str_replace(array('<![CDATA[', ']]>'), '', $str)
            : $str;
    }
    
    final private function _needsCdata($str)
    {
        return ($this->_isXhtml && preg_match('/(?:[<&]|\\-\\-|\\]\\]>)/', $str));
    }

    
    // cloning is forbidden.
    final private function __clone()
    {
    }

    // unserializing instances of this class is forbidden.
    final private function __wakeup()
    {
    }
}
