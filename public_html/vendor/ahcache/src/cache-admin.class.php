<?php

/**
 * --------------------------------------------------------------------------------<br>
 *          __    ______           __       
 *   ____ _/ /_  / ____/___ ______/ /_  ___ 
 *  / __ `/ __ \/ /   / __ `/ ___/ __ \/ _ \
 * / /_/ / / / / /___/ /_/ / /__/ / / /  __/
 * \__,_/_/ /_/\____/\__,_/\___/_/ /_/\___/ 
 *                                        
 * --------------------------------------------------------------------------------<br>
 * AXELS CACHE CLASS :: ADMIN<br>
 * --------------------------------------------------------------------------------<br>
 * <br>
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * <br>
 * --------------------------------------------------------------------------------<br>
 * <br>
 * --- HISTORY:<br>
 * 2021-09-28  2.6  first version for admin UI<br>
 * 2021-10-07  2.7  optical improvements using font awesome<br>
 * 2023-06-02  2.10 shorten code: defaults using ??; short array syntax<br>
 * 2024-07-19  2.12 WIP: add type declarations for PHP 8
 * --------------------------------------------------------------------------------<br>
 * @version 2.12
 * @author Axel Hahn
 * @link https://www.axel-hahn.de/docs/ahcache/index.htm
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package Axels Cache
 */
require_once('cache.class.php');

class AhCacheAdmin extends AhCache
{

    protected array $_aIcon = [
        'invalid' => '<i class="fas fa-eraser"></i>',
        'item' => '<i class="fas fa-box-open"></i>',
        'module' => '<i class="fas fa-puzzle-piece"></i>',
        'outdated' => '<i class="fas fa-hourglass-end"></i>',
        'size' => '<i class="fas fa-puzzle-piece"></i>',
    ];

    // ----------------------------------------------------------------------
    // private methods
    // ----------------------------------------------------------------------

    /**
     * for security reasons the admin must be enabled by an existing file
     * next to the class file. This method returns true if the enable file
     * exists
     * @return boolean
     */
    protected function _isEnabled(): bool
    {
        $sFile2Check = __DIR__ . '/' . str_replace('.php', '-enabled.php', basename(__FILE__));
        return file_exists($sFile2Check);
    }

    /**
     * helper function for action buttons: draw a form with a POST action
     * @param string  $sUrl     target url
     * @param string  $sAction  value for action item
     * @param string  $sHtml    html code inside a button
     * @return string
     */
    protected function _addForm(string $sUrl, string $sAction, string $sHtml = ''): string
    {
        return '
			<form action="' . $sUrl . '" method="POST" style="float: left;">
			<input type="hidden" name="action" value="' . $sAction . '">
			' . ($sHtml ? $sHtml : '<button>' . $sAction . '</button>') . '
		</form>';
    }

    // ----------------------------------------------------------------------
    // public methods
    // ----------------------------------------------------------------------

    /**
     * get html code - render ul list of existing modules
     *
     * @param array $aOptions  array with the keys
     *                         - baseurl - url prefix
     *                         - module  - active module
     * @return string
     */
    public function renderModuleList(array $aOptions = []): string
    {
        if (!$this->_isEnabled()) {
            return 'Admin is disabled.';
        }
        $sReturn = '';
        $aMods = $this->getModules();
        if (count($aMods)) {
            $sReturn .= ''
                . 'Modules: <strong class="counter">' . count($aMods) . '</strong><br><br>'
                . '<nav><ul>'
                // .'<li><a href="'.$aOptions['baseurl'].'&module=">all</a></li>'
            ;
            foreach ($aMods as $sModulename) {
                $sReturn .= '<li'
                    . (isset($aOptions['module']) && $aOptions['module'] == $sModulename ? ' class="active"' : ''
                    )
                    . '><a href="' . $aOptions['baseurl'] . '&module=' . $sModulename . '">' . $this->_aIcon['module'] . ' ' . $sModulename . '</a></li>';
            }
            $sReturn .= '</ul></nav>';
        } else {
            $sReturn = 'No Module was found. The cache is not in use - or wasn\'t inizialized with the right cache dir.<br>';
        }
        return $sReturn;
    }

    /**
     * get html code - render ul list of existing cache items of a module
     *
     * @param array $aOptions  array with the keys
     *                         - baseurl - url prefix
     *                         - module  - active module
     * @return string
     */
    public function renderModuleItems(array $aOptions = []): string
    {
        if (!$this->_isEnabled()) {
            return '';
        }
        $sReturn = '';
        $bHasOutdated = false;
        $iSize = 0;
        $aItems = $this->getCachedItems();
        // echo '<pre>'.print_r($aItems, 1).'</pre>';
        if (count($aItems)) {
            $sReturn .= ''
                . '<table class="datatable"><thead>
                        <tr>
                            <th>cache id</th>
                            <th>size</th>
                            <th>TTL [s]</th>
                            <th>time left [s]</th>
                            <th>visual</th>
                        </tr>
                    </thead>
                    <tbody>'
                // .'<li><a href="'.$aOptions['baseurl'].'&module=">all</a></li>'
            ;
            foreach ($aItems as $sFile => $aItem) {

                $iSize += filesize($sFile);
                // $bSelected=isset($aOptions['item']) && $aOptions['item']==$aItem['cacheid'];
                $bSelected = isset($aOptions['file']) && $aOptions['file'] == $sFile;

                $sLabel = strlen($aItem['cacheid']) < 100 ? $aItem['cacheid'] : substr($aItem['cacheid'], 0, 100) . '...';

                $iLeft = max($aItem['_lifetime'], 0);
                $sBar = $aItem['iTtl'] > 0 ? '<div class="bar"><div class="left" style="width:' . ($iLeft / $aItem['iTtl'] * 100) . '%;"></div></div>' : '';
                $sClasses = '';
                $sClasses .= $bSelected ? 'active' : '';
                if ($aItem['_lifetime'] < $aItem['iTtl'] * 0.33) {
                    if ($aItem['_lifetime'] < 0) {
                        $sClasses .= ' outdated';
                        $bHasOutdated = true;
                    } else {
                        $sClasses .= ' less30';
                    }
                } else {
                    $sClasses .= ' ok';
                }

                // Array ( [iTtl] => 86400 [tsExpire] => 1559303108 [module] => ahdiashow [cacheid] => dirD:/htdocs/axel-hahn.de/c58/diashows/images/2015-2018/MachicoArray ( [skip] => Array ( [0] => /_orig/ ) [remove] => D:/htdocs/axel-hahn.de/c58/diashows/images [intelligent] => 1 ) [_lifetime] => -15574681 [_age] => 15661081 ) 
                $sReturn .= '<tr'
                    . ($sClasses ? ' class="' . $sClasses . '"' : '')
                    . '><td><a href="' . $aOptions['baseurl'] . '&module=' . $aOptions['module'] . '&file=' . $sFile . '" title="' . $aItem['cacheid'] . '">'
                    . '<i class="fas fa-box-open"></i> ' . $sLabel
                    . '</a></td>
                            <td align="right">' . filesize($sFile) . '</td>
                            <td align="right">' . ($aItem['iTtl'] ? $aItem['iTtl'] : '-') . '</td>
                            <td align="right">' . ($iLeft ? $iLeft : '-') . '</td>
                            <td><span style="display:none;">' . $iLeft . '</span>' . $sBar . '</td>
                        </tr>
                        ';
            }
            $sReturn .= '</tbody></table>';
        } else {
            $sReturn = 'No Item was found.<br>';
        }
        $sSize = ($iSize > 1024 ? ($iSize > 1024 * 1024 ? number_format($iSize / 1024 / 1024, 2) . ' MB' : number_format($iSize / 1024, 2) . ' kB'
        ) : $iSize . ' byte'
        );

        $sUrl = $aOptions['baseurl'] . '&module=' . $aOptions['module'];

        return ''
            . (count($aItems) ?
                '<table class="right">'
                . '<tr><td>' . $this->_aIcon['item'] . ' Items</td><td align="right" class="counter"><strong>' . count($aItems) . '</strong></td></tr>'
                . '<tr><td>' . $this->_aIcon['size'] . ' Size</td><td align="right"><strong>' . $sSize . '</strong></td></tr>'
                . '</table>'
                : ''
            )
            . '<h2>' . $this->_aIcon['module'] . ' ' . $this->sModule . '</h2>'
            . ($bHasOutdated ? $this->_addForm($sUrl, 'delete', '<button class="delete">' . $this->_aIcon['outdated'] . ' delete outdated</button>&nbsp;') : ''
            )
            . (count($aItems) ? $this->_addForm($sUrl, 'makeInvalid', '<button class="delete">' . $this->_aIcon['invalid'] . ' make all invalid</button>&nbsp;') : '')
            . $this->_addForm($sUrl, 'deleteModule', '<button class="delete">' . $this->_aIcon['module'] . ' delete module</button>&nbsp;')
            . '<div style="clear: both;"></div><br><br><br>'
            . $sReturn;
    }
}
