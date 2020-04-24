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
	   * Returns the XHTML of a user list
	   */
	  function getXHTML($data) {

		return $src = $this->_getListAsString($data);
	  }


	function _getListAsString($data) {
        global $auth;
        global $lang;

		$renderer = new Doku_Renderer_xhtml();

        if (!method_exists($auth,"retrieveUsers")) return '';

		$confNS = $this->getConf('namespace');
		$confHomeicon = $this->getConf('homeicon');

        $users = array();
	
		/* handle user groups */
        foreach ($data['group'] as $grp) {
            $getuser = $auth->retrieveUsers(0,-1,array('grps'=>'^'.preg_quote($grp,'/').'$'));
            $users = array_merge($users,$getuser);			
        }

		$tableclass = $data['class']?$data['class'][0]: 'inline';

		/* possible table classes: inline, pagelist, htCore, ul, diff */
        $renderer->doc .= '<table class="'.$tableclass.'">';

		/* handle table header*/
		if (in_array('header', $data['show'])) {
		    $renderer->doc .= '<tr>';
			foreach ($data['show'] as $show) {
				if ($show=='home' || $show=='header') continue; 
				$renderer->doc .= '<th>'.$lang[$show].'</th>';
			}
		    $renderer->doc .= '</tr>';
		}

		/* handle table body*/
		$pos = array_search('home', $data['show']) <=> array_search('user', $data['show']);
		$isShowHome = in_array('home', $data['show']);
		$isLinkUser = in_array('user', $data['link']);
		$isLinkEmail = in_array('email', $data['link']);
        foreach ($users as $user => $info) {
            $renderer->doc .= '<tr>';

			foreach ($data['show'] as $show) {
				/* skip home and header option */
				if ($show=='home' || $show=='header') continue;

            	$renderer->doc .= '<td>';

				/* handle user option */
				if ($show=='user') {
					if ($isShowHome && $pos<0) $renderer->doc .= ' <span '.$confHomeicon.'></span> ';
					if ($isLinkUser) {
						$renderer->internallink($confNS.":".$user);
					} else {
						$renderer->doc .= hsc($user);
					}
					if ($isShowHome && $pos>0) $renderer->doc .= ' <span '.$confHomeicon.'></span> ';
				}

				/* handle fullname option */
				if ($show=='fullname') $renderer->doc .= hsc($info['name']);

				/* handle email option */
				if ($show=='email') {
					if ($isLinkEmail) {
						$renderer->emaillink($info['mail']);
					} else {
						$renderer->doc .= hsc($info['mail']);
					}
				}
				$renderer->doc .= '</td>';
			}
            $renderer->doc .= '</tr>';
        }
        $renderer->doc .= '</table>';
        return $renderer->doc;
    }
}

