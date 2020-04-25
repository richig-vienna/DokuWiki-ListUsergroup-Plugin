<?php
/**
 * DokuWiki Plugin listusergroup (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Richard Gfrerer <richard.gfrerer@gmx.net>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class helper_plugin_listusergroup extends DokuWiki_Plugin {

  function getMethods() {
    $result = array();
    $result[] = array(
      'name'   => 'getXHTML',
      'desc'   => 'returns the XHTML to display a user list',
      'params' => array(
		'group (optional)'	=> 'array',
        'show (optional)'	=> 'array',
        'link (optional)'	=> 'array',
        'class (optional)'	=> 'array'),
      'return' => array('xhtml' => 'string')
    );
    return $result;
  }


    /**
     * Constructor loads default config settings once
     */
    function helper_plugin_listusergroup() {
        $this->defaults['namespace']  = $this->getConf('namespace');
        $this->defaults['homeicon']   = $this->getConf('homeicon');
	}

	/**
	 * Returns the XHTML of a user list
	 */
	function getXHTML(Doku_Renderer $renderer, $data) {
		return $src = $this->_getListAsString($renderer, $data);
	}


	function _getListAsString(Doku_Renderer $xhtml_renderer, $data) {
        global $auth;
        global $lang;

		dbglog('function _getListAsString: start');

		//require_once DOKU_INC . 'inc/parser/xhtml.php';
		//$xhtml_renderer = new Doku_Renderer_xhtml();

		/*
		if (is_null($xhtml_renderer)) {
			require_once DOKU_INC . 'inc/parser/xhtml.php';
			$xhtml_renderer = new Doku_Renderer_xhtml();
		}
		*/


        if (!method_exists($auth,"retrieveUsers")) return '';

		$confNS = $this->getConf('namespace');
		$confHomeicon = $this->getConf('homeicon');

        $users = array();
	
		/* handle user groups */
		dbglog('function _getListAsString: handle user groups');
        foreach ($data['group'] as $grp) {
            $getuser = $auth->retrieveUsers(0,-1,array('grps'=>'^'.preg_quote($grp,'/').'$'));
            $users = array_merge($users,$getuser);			
        }

		$tableclass = $data['class']?$data['class'][0]: 'inline';

		/* possible table classes: inline, pagelist, htCore, ul, diff */
        $xhtml_renderer->doc .= '<table class="'.$tableclass.'">';

		/* handle table header*/
		dbglog('function _getListAsString: handle table header');
		if (in_array('header', $data['show'])) {
		    $xhtml_renderer->doc .= '<tr>';
			foreach ($data['show'] as $show) {
				if ($show=='home' || $show=='header') continue; 
				$xhtml_renderer->doc .= '<th>'.$lang[$show].'</th>';
			}
		    $xhtml_renderer->doc .= '</tr>';
		}

		/* handle table body*/
		dbglog('function _getListAsString: handle table body');
		$pos = array_search('home', $data['show']) <=> array_search('user', $data['show']);
		$isShowHome = in_array('home', $data['show']);
		$isLinkUser = in_array('user', $data['link']);
		$isLinkEmail = in_array('email', $data['link']);
        foreach ($users as $user => $info) {
            $xhtml_renderer->doc .= '<tr>';

			foreach ($data['show'] as $show) {
				/* skip home and header option */
				if ($show=='home' || $show=='header') continue;

            	$xhtml_renderer->doc .= '<td>';

				/* handle user option */				
				if ($show=='user') {
					dbglog('function _getListAsString: handle user option');
					if ($isShowHome && $pos<0) $xhtml_renderer->doc .= ' <span '.$confHomeicon.'></span> ';
					if ($isLinkUser) {
						$xhtml_renderer->doc .= $xhtml_renderer->internallink($confNS.":".$user);
					} else {
						$xhtml_renderer->doc .= hsc($user);
					}
					if ($isShowHome && $pos>0) $xhtml_renderer->doc .= ' <span '.$confHomeicon.'></span> ';
				}

				/* handle fullname option */
				if ($show=='fullname') {
					dbglog('function _getListAsString: handle fullname option');
					$xhtml_renderer->doc .= hsc($info['name']);
				}

				/* handle email option */
				if ($show=='email') {
					dbglog('function _getListAsString: handle email option');
					if ($isLinkEmail) {
						$xhtml_renderer->doc .= $xhtml_renderer->emaillink($info['mail']);
					} else {
						$xhtml_renderer->doc .= hsc($info['mail']);
					}
				}
				$xhtml_renderer->doc .= '</td>';
			}
            $xhtml_renderer->doc .= '</tr>';
        }
        $xhtml_renderer->doc .= '</table>';
        return $xhtml_renderer->doc;
    }
}

