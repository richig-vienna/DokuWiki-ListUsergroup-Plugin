<?php
/**
 * DokuWiki Plugin listusergroup (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Richard Gfrerer <richard.gfrerer@gmx.net>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_listusergroup extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() { return 'substition'; }

    /**
     * @return string Paragraph type
     */
    public function getPType() { return 'normal'; }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() { return 161; }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {

         $this->Lexer->addSpecialPattern('{{listusergroup>[^}]+}}',$mode,'plugin_listusergroup');
    }

    /**
     * Handle matches of the listusergroup syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
		list($syntax, $match) = explode('>', substr( strtolower($match), 0, -2), 2); // strip markup
		dbglog('function handle: got match:'.$match);


		$options = [];
		$optionParts = explode(';', $match);
		foreach ($optionParts as $optionPart) {
			list($key,$optiontxt) = explode('=', $optionPart,2);
			$options[trim($key)] = explode(',', $optiontxt);
			$options[$key] = array_map('trim', $options[$key]);
		}

		return $options;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data){
		if ($mode == 'xhtml') {
			if ($my =& plugin_load('helper', 'listusergroup'))
				// $renderer->doc .= 
				$my->getXHTML($renderer, $data);
				dbglog('function render: $renderer->doc:'.$renderer->doc);
			return true;
		}
		return false;
    }
}

