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
        "description"   => "Mailchimp Plugon for MyBB",
        "website"       => "htttps://mailchimp.com",
        "author"        => "Desgyz",
        "authorsite"    => "https://github.com/desgyz",
        "version"       => "1.0.3",
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
        "description" => "Enable or disable of MailChimp email delivery service integration. 
			<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_top\">
				<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
				<input type=\"hidden\" name=\"encrypted\" value=\"-----BEGIN PKCS7-----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-----END PKCS7-----\">
				<input type=\"image\" src=\"https://www.paypalobjects.com/en_AU/i/btn/btn_donate_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal — The safer, easier way to pay online.\">
				<img alt=\"\" border=\"0\" src=\"https://www.paypalobjects.com/en_AU/i/scr/pixel.gif\" width=\"1\" height=\"1\">
			</form>
		",
        "disporder" => "50",
        "isdefault" => "no",
    );
    $db->insert_query("settinggroups", $settingsGroup);
    $gid = $db->insert_id();

    $settingOnOff = array(
        "sid" => "NULL",
        "name" => "mailchimp_onoff",
        "title" => "Plugin Activation",
        "description" => "Set to YES to enable Mailchimp email service integration.",
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
        "description" => "Please enter the API Key retrieved from here: http://admin.mailchimp.com/account/api/ ",
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
        "description" => "Please enter the List Unique Id obtained by going to: http://admin.mailchimp.com/lists/ <br /><small>Click the \"setting\" -> \"List name & defaults\" for the list - the Unique Id is at this page.</small>",
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
