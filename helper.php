<?php
/**
 * DokuWiki Plugin Listusergroup (Helper Component)
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
	function getXHTML($data) {
		return $src = $this->_getListAsString($data);
	}


	function _getListAsString($data) {
        global $auth;
        global $lang;

		if (is_null($xhtml_renderer)) {
			require_once DOKU_INC . 'inc/parser/xhtml.php';
			$xhtml_renderer = new Doku_Renderer_xhtml();
		}


        if (!method_exists($auth,"retrieveUsers")) return '';

		$confNS = $this->getConf('namespace');
		$confHomeicon = $this->getConf('homeicon');

        $users = array();
	
		/* handle user groups */
        foreach ($data['group'] as $grp) {
            $getuser = $auth->retrieveUsers(0,-1,array('grps'=>'^'.preg_quote($grp,'/').'$'));
            $users = $users + $getuser;			
        }

		$tableclass = $data['class']?$data['class'][0]: 'inline';

		/* possible table classes: inline, pagelist, htCore, ul, diff */
        $xhtml_renderer->doc .= '<table class="'.$tableclass.'">';

		/* handle table header*/
		if (in_array('header', $data['show'])) {
		    $xhtml_renderer->doc .= '<tr>';
			foreach ($data['show'] as $show) {
				if (substr( $show, 0, strlen('existing') ) === "existing") $show = substr($show, strlen('existing'));
				if ($show==='home' || $show==='header') continue;				
				$xhtml_renderer->doc .= '<th>'.$lang[$show].'</th>';
			}
		    $xhtml_renderer->doc .= '</tr>';
		}

		/* handle table body*/
		$posHome = array_search('home', $data['show']);
		$posUser = array_search('user', $data['show']);
		$iconPos = -1;
		if ($posHome>$posUser) $iconPos = 1;

		$isShowExistingHome = in_array('existinghome', $data['show']);
		$isShowHome = in_array('home', $data['show']);
                $isShowGroups = in_array('groups', $data['show']);
		$isLinkUser = in_array('user', $data['link']);
		$isLinkEmail = in_array('email', $data['link']);
		

        foreach ($users as $user => $info) {
			$exists = true;
			/* skip non-existing hompage entries, if defined */
			if ($isShowExistingHome) {
				$exists = null;
				$usertmp = $user; // user obj will be manipulated by 'resolve_pageid'
				resolve_pageid($confNS,$usertmp,$exists);
				if (!$exists) continue;
			}

            $xhtml_renderer->doc .= '<tr>';

			foreach ($data['show'] as $show) {
				/* skip home and header option (do not create a column) */
				if ($show==='home' || $show==='existinghome' || $show==='header') continue;

            	$xhtml_renderer->doc .= '<td>';

				/* handle user option */				
				if ($show==='user') {
					if ( ($isShowHome||$isShowExistingHome) && $iconPos<0) $xhtml_renderer->doc .= ' <span '.$confHomeicon.'></span> ';
					if ($isLinkUser) {
						$xhtml_renderer->doc .= $xhtml_renderer->internallink($confNS.':'.$user);
					} else {
						$xhtml_renderer->doc .= hsc($user);
					}
					if ( ($isShowHome||$isShowExistingHome) && $iconPos>0) $xhtml_renderer->doc .= ' <span '.$confHomeicon.'></span> ';
				}

				/* handle groups option */
				if ($show==='groups') {
					if ($isShowGroups) {
						$xhtml_renderer->doc .= hsc(implode( ", ", $info['grps']));
					}
				}	

				/* handle fullname option */
				if ($show==='fullname') {
					$xhtml_renderer->doc .= hsc($info['name']);
				}

				/* handle email option */
				if ($show==='email') {
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

