<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
* 
* @package core
* @filename wallet.php
* @copyright 2016 Joachim Dieterich
* @author Joachim Dieterich
* @date 2016.12.28 05:25
* @license: 
*
* The MIT License (MIT)
* Copyright (c) 2012 Joachim Dieterich http://www.curriculumonline.de
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
* to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
* and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
* DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
* THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
global $PAGE, $USER, $TEMPLATE;
$TEMPLATE->assign('breadcrumb',  array('Sammelmappe' => 'index.php?action=wallet'));
$TEMPLATE->assign('page_title', 'Sammelmappe');  
$search = false;
if (isset($_POST) ){
    if (isset($_POST['search'])){
        $search = filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);
        $TEMPLATE->assign('wallet_reset', true); 
    }
}
if (isset($_GET['view'])){
    switch ($_GET['view']) {
        case 'shared':  $wallet   = new Wallet();
                        $TEMPLATE->assign('wallet', $wallet->get('shared', $USER->id, 'userFiles')); 
            break;

        default:
            break;
    }
} else {
    $wallet   = new Wallet();
    $TEMPLATE->assign('wallet', $wallet->get('search', $search));   
}