<?php
    /**
    * -------------------------------.*~ E G N  ~*.------------------------------
    * EGN Software: egMember
    * Copyright (c) 2012-2024 Elwin HG - http://egnsoftware.com (elwin@cvegn.com)
    * ---------------------------------------------------------------------------
    * This software is  furnished  under a  license and may  be used and   copied
    * only  in accordance with the terms of such  license and with  the inclusion
    * of  the above copyright notice.  This software or any other  copies thereof
    * may not be  provided or otherwise made available  to any other person.   No
    * title to and  ownership of the software is hereby transferred.
    * 
    * You  may  not  reverse   engineer,  decompile,  defeat  license  encryption
    * mechanisms, or  disassemble  this  software  product  or software   product
    * license. EGN  may terminate  this license if you  don't comply with any  of
    * the terms and   conditions set forth   in our  End  User  License Agreement
    * (EULA). In  such event, licensee  agrees to return licensor or  destroy all
    * copies of software upon termination  of the license. 
    * Please see the EULA file for the full End User License Agreement.
    * ---------------------------------------------------------------------------
    */
    require_once ('config.inc.php');
    require_once (DOCSPATH . 'includes/public.inc.php');
    require_once (DOCSPATH . 'includes/eg_member.php');
    require_once (DOCSPATH . 'includes/eg_ticket.php');
    $egLanguage->include_frontpage_translation(__FILE__);

    if(!$config['ticket_status']) {
        egHtml::show_error(_ticket_offline);  
    }

    $egTicket = new egTicket();

    function show_ticket($ticket){
        global $egTicket, $config;
        if(!$config['view_closed_ticket'] && $ticket['status'] == 'closed') {
            egHtml::show_error(_ticket_closed);
        }

        egHtml::display_front_page('page_ticket_view.html', $egTicket->member_view_ticket($ticket)); 
    }

    switch ($_GET['do']) {
        case 'login':
            if ($_POST) {
                if($ticket = $egTicket->validate_ticket_login($_POST)) {
                    show_ticket($ticket);
                }
            }  
            do_redirect('index.php?pa=contact-us');
            break;
        default:
            if ($_POST) {
                if($id = eg_intval($_GET['t'])) {
                    $egTicket->check_ticket_session($_GET['t']);
                    $egTicket = new egTicket($_POST);
                    $egTicket->user_reply($id);
                }
            }

            $email = safe_string($_GET['e']);
            $ticket_id = eg_intval($_GET['t']);
            if(!$email || !$ticket_id) {
                do_redirect('index.php?pa=contact-us');
            }

            if(!$ticket = $egTicket->getTicketByTicketId($ticket_id)) {
                do_redirect('index.php?pa=contact-us');
            }

            if($ticket['username']) {
                $egLogin = new egLogin();
                if(!$egLogin->check_login_session(true)) {
                    $show_array = array('redirect' => "members.php?pa=ticket&do=view&id={$ticket['ticket_id']}");
                    $egLogin->show_page(_login_first, $show_array);
                }
                else {
                    do_member_redirect("members.php?pa=ticket&do=view&id={$ticket['ticket_id']}");
                }
            }

            $egTicket->check_ticket_login($ticket);
            show_ticket($ticket);  
    }
?>