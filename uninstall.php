<?php
// this is the uninstall handler
// include unregister_setting, delete_option, and other uninstall behavior here

require_once('wp-plugin.php');

function uninstall_options($name) {
    unregister_setting("${name}_group", $name);
    WPPlugin::remove_options($name);
}

// nlpcaptcha
uninstall_options('nlpcaptcha_options');

// mailhide
uninstall_options('mailhide_options');

?>