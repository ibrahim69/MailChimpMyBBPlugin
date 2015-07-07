<?php 
function mailchimp() {
	    
	global $mybb, $db, $user_info, $userfields;
	if ($mybb->settings['mailchimp_onoff'] == "1") {
		$MailChimp = '';
		$api_key = '';
		$list_id = '';
		$double_optin = '';
		
		$user_id = '';
		$user_name = '';
		$user_email = '';
		$user_ip = '';
		$optinTIME = '';
		$merge_vars = '';
		$retval = '';
		$parameters = '';
		
		$user = get_user($uid);
		
		$api_key = $mybb->settings['mailchimp_api'];
		$list_id =  $mybb->settings['mailchimp_listid'];
		$fieldnum = $mybb->settings['mailchimp_field'];
		$api_loc = $mybb->settings['mailchimp_apiadd'];
		$confmsg = $mybb->settings['mailchimp_conf'];
		$prefix = $mybb->settings['mailchimp_prefix'];
		
		$getuserinfo = $db->simple_select(
			"users",
			"uid, username, email, regip ",
			"uid=" . (int)$user_info['uid'] . ""
		);
		$userInfo = $db->fetch_array($getuserinfo);
		$user_id = $userInfo['uid'];
		$user_name = $userInfo['username'];
		$user_email = $userInfo['email'];
		$user_ip = $userInfo['regip'];
		$optinTIME = date('Y-m-d H:i:s');

		if ($mybb->settings['mailchimp_opting']){
			$useroptq = $db->query("SELECT fid" . $fieldnum . " FROM " . $prefix ."userfields WHERE ufid=" . $user_id . "");
			$user_opt = $db->fetch_array($useroptq, 'fid'. $fieldnum);
			$user_opt = $user_opt['fid'. $fieldnum];
		} else {
			$user_opt = $confmsg;
		}
		
		$newmem = array(
					"email_address" => $user_email, 
					"status" => "subscribed", 
					"merge_fields" => array(
						"UNAME" => $user_name, 
					)
				  );
		$jsonnewmem = json_encode($newmem);
		$apifresh = 'https://'. $api_loc .'.api.mailchimp.com/3.0/lists/'. $list_id . '/members/';
		if ($user_opt == $confmsg){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$apifresh);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
														'Authorization: Basic '.$api_key));
			curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonnewmem);                                                                                                                  
			$result = curl_exec($ch);	
			/* print('Opt' . $user_opt);
			print('success');
			var_dump('Result'. $result);
			print('loc' . $api_loc);
			print('conf msg'. $confmsg); */
		} else {
			/* var_dump($user_opt); */
		}
	}
}

$plugins->add_hook("member_do_register_end", "mailchimp");

function mailchimp_integration_info(){
    return array(
        "name"          => "Mailchimp",
        "description"   =>  '
	<p> Mailchimp Plugin for MyBB</p>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="">
		<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif\" width="1" height="1">
	</form>
',
        "website"       => "htttps://mailchimp.com",
        "author"        => "Desgyz",
        "authorsite"    => "https://github.com/desgyz",
        "version"       => "1.0.4",
        "guid"      => "",
        "compatibility" => "*"
    );
}

function mailchimp_integration_activate(){
    global $db,$mybb;
    $settingsGroup = array(
        "gid" => "NULL",
        "name" => "mailchimp",
        "title" => "Mailchimp Integration",	
        "description" => "Enable or disable of MailChimp email delivery service integration.",
        "disporder" => "50",
        "isdefault" => "no",
    );
    $db->insert_query("settinggroups", $settingsGroup);
    $gid = $db->insert_id();

    $settingOnOff = array(
        "sid" => "NULL",
        "name" => "mailchimp_onoff",
        "title" => "Plugin Activation",
        "description" => "Enable/Disable the plugin",
        "optionscode" => "yesno",
        "value" => "0",
        "disporder" => "1",
        "gid" => intval($gid),
    );
    $db->insert_query("settings", $settingOnOff);

	
        $settingOpting = array(
		"sid" => "NULL",
		"name" => "mailchimp_opting",
		"title" => "Opt-In Setting",
		"description" => "Enable/Disable Opt-In settings on the registration",
		"optionscode" => "yesno",
		"value" => "1",
		"disporder" => "4",
		"gid" => intval($gid),
	);
	$db->insert_query("settings", $settingOpting);

	$settingAPIadd = array(
		"sid" => "NULL",
		"name" => "mailchimp_apiadd",
		"title" => "Location Server API Key",
		"description" => "The last 3 to 4 character on end of api key (after the hyphen). Eg: us10, us2",
		"optionscode" => "text",
		"value" => "us9",
		"disporder" => "7",
		"gid" => intval($gid),
	);
	$db->insert_query("settings", $settingAPIadd);
	
	$settingCustomField = array(
		"sid" => "NULL",
		"name" => "mailchimp_field",
		"title" => "Custom Field ID",
		"description" => "Custom Field id for the checkbox or radio button opt-in setting.",
		"optionscode" => "text", 
		"value" => "4",
		"disporder" => "5",
		"gid" => intval($gid),
	);
	$db->insert_query("settings", $settingCustomField);	
	
	$settingConfirmMessage = array(
		"sid" => "NULL",
		"name" => "mailchimp_conf",
		"title" => "Custom Field Text",
		"description" => "The text for the checkbox/radio button option.",
		"optionscode" => "text", 
		"value" => "Yes?",
		"disporder" => "8",
		"gid" => intval($gid),
	);
	$db->insert_query("settings", $settingConfirmMessage);
	
	$settingPrefix = array(
		"sid" => "NULL",
		"name" => "mailchimp_prefix",
		"title" => "Database Prefix",
		"description" => "Database Prefix on your installation.",
		"optionscode" => "text",
		"value" => "mybb_",
		"disporder" => "9",
		"gid" => intval($gid),
	);
	$db->insert_query("settings", $settingPrefix);
	
	/*
	$settingDoubleOptin = array(
        "sid" => "NULL",
        "name" => "mailchimp_doubleoptin",
        "title" => "Double opt-in confirmation",
        "description" => "Flag to control whether a double opt-in confirmation message is sent, defaults to true. Abusing this may cause your Mailchimp account to be suspended.",
        "optionscode" => "yesno",
        "value" => "1",
        "disporder" => "4",
        "gid" => intval($gid),
    );
    $db->insert_query("settings", $settingDoubleOptin);*/
	
    $settingApi = array(
        "sid" => "NULL",
        "name" => "mailchimp_api",
        "title" => "Mailchimp API Key",
        "description" => 'Please enter the API Key retrieved from here: <href src=\"http://admin.mailchimp.com/account/api/\">http://admin.mailchimp.com/account/api/</a>',
        "optionscode" => "text",
        "value" => "123456789-us2",
        "disporder" => "2",
        "gid" => intval($gid),
    );
    $db->insert_query("settings", $settingApi);
    
	$settingListID = array(
        "sid" => "NULL",
        "name" => "mailchimp_listid",
        "title" => "Mailchimp List ID",
        "description" => 'Please enter the List Unique Id obtained by going to: <href src=\"http://admin.mailchimp.com/lists/\">http://admin.mailchimp.com/lists/</a><br /><small>Click the \"setting\" -> \"List name & defaults\" for the list - the Unique Id is at this page.</small>',
        "optionscode" => "text",
        "value" => "abcdefgh",
        "disporder" => "3",
        "gid" => intval($gid),
    );
    $db->insert_query("settings", $settingListID);
	
    rebuild_settings();
}

function mailchimp_integration_deactivate()
{
    global $db;
    $db->delete_query("settinggroups", "name='mailchimp'");
    $db->delete_query(
        "settings",
        "name IN(
            'mailchimp_onoff', 'mailchimp_api', 'mailchimp_listid', 'mailchimp_opting', 'mailchimp_field', 'mailchimp_apiadd', 'mailchimp_conf', 'mailchimp_prefix'
        )"
    );
    rebuild_settings();
}
?>
