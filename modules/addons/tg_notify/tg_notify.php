<?php
if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function tg_notify_config() {
	$configarray = array(
	"name" => "Telegram notifications",
	"description" => "Notifications to the administrator and team via Telegram",
	"version" => "1.1",
	"author" => "<a href='https://github.com/hroost/whmcs-telegram' target='_blank'>Milad Maldar</a>",
	"language" => "english",
	"fields" => array(
	"key" => array ("FriendlyName" => "Bot Token", "Type" => "text", "Size" => "50", "Description" => "<a href='https://core.telegram.org/bots/api#authorizing-your-bot' target='_blank' style='color:#0000FF; text-decoration: none;'>Where I can find bot token?</a>", "Default" => "", ),
	"chatid" => array ("FriendlyName" => "Chat ID", "Type" => "text", "Size" => "50", "Description" => "<a href='https://stackoverflow.com/a/46247058' target='_blank' style='color:#0000FF; text-decoration: none;'>Where I can find chat ID?</a>", "Default" => "", ),
	));
	return $configarray;
}

function tg_notify_activate() {
	$query = "CREATE TABLE IF NOT EXISTS `tg_notify_settings` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`adminid` int(11) NOT NULL,
	`access_token` varchar(255) NOT NULL,
	`permissions` text NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;";
	$result = mysql_query($query);
}

function tg_notify_deactivate() {
	$query = "DROP TABLE `tg_notify_settings`";
	$result = mysql_query($query);
}

function tg_notify_output($vars) {
	global $customadminpath, $CONFIG;

	$access_token = select_query('tg_notify_settings', '', array('adminid' => $_SESSION['adminid']));

	if ( $_GET['return'] == '1' && $_SESSION['request_token'] ) {
		
		insert_query("tg_notify_settings", array("adminid" => $_SESSION['adminid'], "access_token" => $result['access_token']));
		$_SESSION['request_token'] = "";
		header("Location: addonmodules.php?module=tg_notify");
		
	} elseif($_GET['setup'] == '1' && !mysql_num_rows($access_token)) {

		$_SESSION['request_token'] = $vars['key'];
		header("Location: ". $CONFIG['SystemURL']."/".$customadminpath."/addonmodules.php?module=tg_notify&return=1");

	} elseif( $_GET['disable'] == '1' && mysql_num_rows($access_token) ) {
		full_query("DELETE FROM `tg_notify_settings` WHERE `adminid` = '".$_SESSION['adminid']."'");
		echo "<div class='infobox'><strong>The notification addon has been disabled successfully</strong><br>The notification addon database record was successfully deleted and the addon disabled</div>";
	} elseif( mysql_num_rows($access_token) && $_POST ){
		update_query('tg_notify_settings',array('permissions' => serialize($_POST['tg_notifyfication'])), array('adminid' => $_SESSION['adminid']));
		echo "<div class='infobox'><strong>Changes saved</strong><br>Changes saved successfully</div>";    
	}

	$access_token = select_query('tg_notify_settings', '', array('adminid' => $_SESSION['adminid']));
	$result = mysql_fetch_array($access_token, MYSQL_ASSOC);
	$permissions = unserialize($result['permissions']);   

	if ( !mysql_num_rows($access_token)) {
		echo "<p><a href='addonmodules.php?module=tg_notify&setup=1' class='btn btn-primary'>Activate addon to send notifications</a></p>";
	} else {
		echo "<p><a href='addonmodules.php?module=tg_notify&disable=1' class='btn btn-warning'>Disable notification addon</a></p>";
		echo '<form method="POST"><table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
		<tr>
		<td class="fieldlabel" width="200px">Send message when:</td>
		<td class="fieldarea">
		<table width="100%">
		<tr>
		<td valign="top">
		<input type="checkbox" name="tg_notifyfication[new_client]" value="1" id="tg_notifyfications_new_client" '.($permissions['new_client'] == "1" ? "checked" : "").'> <label for="tg_notifyfications_new_client">A new user has been registered</label><br>
		<input type="checkbox" name="tg_notifyfication[new_invoice]" value="1" id="tg_notifyfications_new_invoice" '.($permissions['new_invoice'] == "1" ? "checked" : "").'> <label for="tg_notifyfications_new_invoice">An invoice was paid</label><br>
		<input type="checkbox" name="tg_notifyfication[new_ticket]" value="1" id="tg_notifyfications_new_ticket" '.($permissions['new_ticket'] == "1" ? "checked" : "").'> <label for="tg_notifyfications_new_ticket">A new ticket was created</label><br>
		<input type="checkbox" name="tg_notifyfication[new_update]" value="1" id="tg_notifyfications_new_update" '.($permissions['new_update'] == "1" ? "checked" : "").'> <label for="tg_notifyfications_new_update">A new ticket response</label><br>
		</td>
		</tr>
		</table>
		</table>
		<p align="center"><input type="submit" value="Save changes" class="btn btn-primary"></p></form>';
	}
}
