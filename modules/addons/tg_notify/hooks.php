<?php
function sendTelegramMessage($pm) {
	global $vars;
	$application_chatid = mysql_fetch_array( select_query('tbladdonmodules', 'value', array('module' => 'tg_notify', 'setting' => 'chatid') ), MYSQL_ASSOC );
	$application_botkey = mysql_fetch_array( select_query('tbladdonmodules', 'value', array('module' => 'tg_notify', 'setting' => 'key') ), MYSQL_ASSOC );
	$chat_id 		= $application_chatid['value'];
	$botToken 		= $application_botkey['value'];

	$data = array(
		'chat_id' 	=> $chat_id,
		'text' 		=> $pm
	);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot$botToken/sendMessage");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_exec($curl);
	curl_close($curl);
}

function tg_notify_ClientAdd($vars) {
	global $customadminpath, $CONFIG;
	sendTelegramMessage("A new user has been registered \n---------------------------------------------------------------------------------------------- \n". $CONFIG['SystemURL'].'/'.$customadminpath.'/clientssummary.php?userid='.$vars['userid']);
}

function tg_notify_InvoicePaid($vars) {
	global $customadminpath, $CONFIG;
	sendTelegramMessage("An invoice was paid \n---------------------------------------------------------------------------------------------- \n Invoice ID: $vars[invoiceid] \n Amount: $vars[total] \n". $CONFIG['SystemURL'].'/'.$customadminpath.'/invoices.php?action=edit&id='.$vars['invoiceid']);
}

function tg_notify_TicketOpen($vars) {
	global $customadminpath, $CONFIG;
	sendTelegramMessage("A new ticket was created \n---------------------------------------------------------------------------------------------- \n Ticket ID: $vars[ticketid] \n Department: $vars[deptname] \n Subject: $vars[subject] \n". $CONFIG['SystemURL'].'/'.$customadminpath.'/supporttickets.php?action=viewticket&id='.$vars['ticketid']);
}

function tg_notify_TicketUserReply($vars) {
	global $customadminpath, $CONFIG;
	sendTelegramMessage("A new ticket response \n---------------------------------------------------------------------------------------------- \n Ticket ID: $vars[ticketid] \n Department: $vars[deptname] \n Subject: $vars[subject] \n". $CONFIG['SystemURL'].'/'.$customadminpath.'/supporttickets.php?action=viewticket&id='.$vars['ticketid']);

}

add_hook("ClientAdd",1,"tg_notify_ClientAdd");
add_hook("InvoicePaid",1,"tg_notify_InvoicePaid");
add_hook("TicketOpen",1,"tg_notify_TicketOpen");
add_hook("TicketUserReply",1,"tg_notify_TicketUserReply");
