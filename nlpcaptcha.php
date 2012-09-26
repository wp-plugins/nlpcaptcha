<?php

require_once('wp-plugin.php');

if (!class_exists('NLPCaptcha')) {
    class NLPCaptcha extends WPPlugin {
        // member variables
        private $saved_error;
        
        // php 4 constructor
        function NLPCaptcha($options_name) {
            $args = func_get_args();
            call_user_func_array(array(&$this, "__construct"), $args);
        }
        
        // php 5 constructor
        function __construct($options_name) {
            parent::__construct($options_name);
            
            $this->register_default_options();
            
            // require the nlpcaptcha library
            $this->require_library();
            
            // register the hooks
            $this->register_actions();
            $this->register_filters();
        }
        
        function register_actions() {
            // load the plugin's textdomain for localization
            add_action('init', array(&$this, 'load_textdomain'));

            // styling
            add_action('wp_head', array(&$this, 'register_stylesheets')); // make unnecessary: instead, inform of classes for styling
            add_action('admin_head', array(&$this, 'register_stylesheets')); // make unnecessary: shouldn't require styling in the options page
            
            if ($this->options['show_in_registration'])
                add_action('login_head', array(&$this, 'registration_style')); // make unnecessary: instead use jQuery and add to the footer?

            // options
            register_activation_hook(WPPlugin::path_to_plugin_directory() . '/wp-nlpcaptcha.php', array(&$this, 'register_default_options')); // this way it only happens once, when the plugin is activated
            add_action('admin_init', array(&$this, 'register_settings_group'));

            // only register the hooks if the user wants nlpcaptcha on the registration page
            if ($this->options['show_in_registration']) {
                // nlpcaptcha form display
                if ($this->is_multi_blog())
                    add_action('signup_extra_fields', array(&$this, 'show_nlpcaptcha_in_registration'));
                else
                    add_action('register_form', array(&$this, 'show_nlpcaptcha_in_registration'));
            }

            // only register the hooks if the user wants nlpcaptcha on the comments page
            if ($this->options['show_in_comments']) {
                add_action('comment_form', array(&$this, 'show_nlpcaptcha_in_comments'));

                // nlpcaptcha comment processing (look into doing all of this with AJAX, optionally)
                add_action('wp_head', array(&$this, 'saved_comment'), 0);
                add_action('preprocess_comment', array(&$this, 'check_comment'), 0);
                add_action('comment_post_redirect', array(&$this, 'relative_redirect'), 0, 2);
            }

            // administration (menus, pages, notifications, etc.)
            add_filter("plugin_action_links", array(&$this, 'show_settings_link'), 10, 2);

            add_action('admin_menu', array(&$this, 'add_settings_page'));
            
            // admin notices
            add_action('admin_notices', array(&$this, 'missing_keys_notice'));
        }
        
        function register_filters() {
            // only register the hooks if the user wants nlpcaptcha on the registration page
            if ($this->options['show_in_registration']) {
                // nlpcaptcha validation
                if ($this->is_multi_blog())
                    add_filter('wpmu_validate_user_signup', array(&$this, 'validate_nlpcaptcha_response_wpmu'));
                else
                    add_filter('registration_errors', array(&$this, 'validate_nlpcaptcha_response'));
            }
        }
        
        function load_textdomain() {
            load_plugin_textdomain('nlpcaptcha', false, 'languages');
        }
        
        // set the default options
        function register_default_options() {
            if ($this->options)
               return;
           
            $option_defaults = array();
           
            $old_options = WPPlugin::retrieve_options("nlpcaptcha");
           
            if ($old_options) {
               $option_defaults['publisherkey'] = $old_options['publisherkey']; // the public key for NLPCaptcha
                $option_defaults['validatekey'] = $old_options['validatekey']; // the private key for NLPCaptcha

               $option_defaults['privatekey'] = $old_options['privatekey']; // the private key for NLPCaptcha

               // placement
               $option_defaults['show_in_comments'] = $old_options['re_comments']; // whether or not to show NLPCaptcha on the comment post
               $option_defaults['show_in_registration'] = $old_options['re_registration']; // whether or not to show NLPCaptcha on the registration page

               // bypass levels
               $option_defaults['bypass_for_registered_users'] = ($old_options['re_bypass'] == "on") ? 1 : 0; // whether to skip NLPCaptchas for registered users
               $option_defaults['minimum_bypass_level'] = $old_options['re_bypasslevel']; // who doesn't have to do the NLPCaptcha (should be a valid WordPress capability slug)

               if ($option_defaults['minimum_bypass_level'] == "level_10") {
                  $option_defaults['minimum_bypass_level'] = "activate_plugins";
               }

               // styling
               $option_defaults['comments_tab_index'] = $old_options['re_tabindex']; // the default tabindex for NLPCaptcha
               $option_defaults['registration_tab_index'] = 30; // the default tabindex for NLPCaptcha

               // error handling
               $option_defaults['no_response_error'] = $old_options['error_blank']; // message for no CAPTCHA response
               $option_defaults['incorrect_response_error'] = $old_options['error_incorrect']; // message for incorrect CAPTCHA response
            }
           
            else {
               // keys
               $option_defaults['publisherkey'] = ''; // the public key for NLPCaptcha
                $option_defaults['validatekey']='';
               $option_defaults['privatekey'] = ''; // the private key for NLPCaptcha

               // placement
               $option_defaults['show_in_comments'] = 1; // whether or not to show NLPCaptcha on the comment post
               $option_defaults['show_in_registration'] = 1; // whether or not to show NLPCaptcha on the registration page

               // bypass levels
               $option_defaults['bypass_for_registered_users'] = 1; // whether to skip NLPCaptchas for registered users
               $option_defaults['minimum_bypass_level'] = 'read'; // who doesn't have to do the NLPCaptcha (should be a valid WordPress capability slug)

               // styling
               $option_defaults['comments_tab_index'] = 5; // the default tabindex for NLPCaptcha
               $option_defaults['registration_tab_index'] = 30; // the default tabindex for NLPCaptcha

               // error handling
               $option_defaults['no_response_error'] = '<strong>ERROR</strong>: Please fill in the NLPCaptcha form.'; // message for no CAPTCHA response
               $option_defaults['incorrect_response_error'] = '<strong>ERROR</strong>: That NLPCaptcha response was incorrect.'; // message for incorrect CAPTCHA response
            }
            
            // add the option based on what environment we're in
            WPPlugin::add_options($this->options_name, $option_defaults);
        }
        
        // require the nlpcaptcha library
        function require_library() {
            require_once($this->path_to_plugin_directory() . '/nlpcaptchalib.php');
        }
        
        // register the settings
        function register_settings_group() {
            register_setting("nlpcaptcha_options_group", 'nlpcaptcha_options', array(&$this, 'validate_options'));
        }
        
        // todo: make unnecessary
        function register_stylesheets() {
            $path = WPPlugin::url_to_plugin_directory() . '/nlpcaptcha.css';
                
            echo '<link rel="stylesheet" type="text/css" href="' . $path . '" />';
        }
        
        // stylesheet information
        // todo: this 'hack' isn't nice, try to figure out a workaround
        function registration_style() {
            $width = 0; // the width of the nlpcaptcha form

            // every theme is 358 pixels wide except for the clean theme, so we have to programmatically handle that
            if ($this->options['registration_theme'] == 'clean')
                $width = 485;
            else
                $width = 360;

            echo <<<REGISTRATION
                <script type="text/javascript">
                window.onload = function() {
                    document.getElementById('login').style.width = '{$width}px';
                    document.getElementById('reg_passmail').style.marginTop = '10px';
                    document.getElementById('nlpcaptcha_widget_div').style.marginBottom = '10px';
                };
                </script>
REGISTRATION;
        }
        
        function nlpcaptcha_enabled() {
            return ($this->options['show_in_comments'] || $this->options['show_in_registration']);
        }
        
        function keys_missing() {
            return (empty($this->options['publisherkey']) || empty($this->options['validatekey'])|| empty($this->options['privatekey']));
        }
        
        function create_error_notice($message, $anchor = '') {
            $options_url = admin_url('options-general.php?page=nlpcaptcha/nlpcaptcha.php') . $anchor;
            $error_message = sprintf(__($message . ' <a href="%s" title="NLPCaptcha Options">Fix this</a>', 'nlpcaptcha'), $options_url);
            
            echo '<div class="error"><p><strong>' . $error_message . '</strong></p></div>';
        }
        
        function missing_keys_notice() {
            if ($this->nlpcaptcha_enabled() && $this->keys_missing()) {
                $this->create_error_notice('You enabled NLPCaptcha, but some of the NLPCaptcha API Keys seem to be missing.');
            }
        }
        
        function validate_dropdown($array, $key, $value) {
            // make sure that the capability that was supplied is a valid capability from the drop-down list
            if (in_array($value, $array))
                return $value;
            else // if not, load the old value
                return $this->options[$key];
        }
        
        function validate_options($input) {
            // todo: make sure that 'incorrect_response_error' is not empty, prevent from being empty in the validation phase
            
            // trim the spaces out of the key, as they are usually present when copied and pasted
            // todo: keys seem to usually be 40 characters in length, verify and if confirmed, add to validation process
            $validated['publisherkey'] = trim($input['publisherkey']);
            $validated['validatekey'] = trim($input['validatekey']);
            $validated['privatekey'] = trim($input['privatekey']);
            
            $validated['show_in_comments'] = ($input['show_in_comments'] == 1 ? 1 : 0);
            $validated['bypass_for_registered_users'] = ($input['bypass_for_registered_users'] == 1 ? 1: 0);
            
            $capabilities = array ('read', 'edit_posts', 'publish_posts', 'moderate_comments', 'activate_plugins');
                    
                 
            $validated['minimum_bypass_level'] = $this->validate_dropdown($capabilities, 'minimum_bypass_level', $input['minimum_bypass_level']);
            
            $validated['comments_tab_index'] = $input['comments_tab_index'] ? $input["comments_tab_index"] : 5; // use the intval filter
            
            $validated['show_in_registration'] = ($input['show_in_registration'] == 1 ? 1 : 0);
             $validated['registration_tab_index'] = $input['registration_tab_index'] ? $input["registration_tab_index"] : 30; // use the intval filter
            
          
            
            $validated['no_response_error'] = $input['no_response_error'];
            $validated['incorrect_response_error'] = $input['incorrect_response_error'];
            
            return $validated;
        }
        
        // display nlpcaptcha
        function show_nlpcaptcha_in_registration($errors) {
            
            $comment_string = <<<COMMENT_FORM
            <script type='text/javascript'>   
            document.getElementById('nlpcaptcha_table').style.direction = 'ltr';
            </script>
COMMENT_FORM;

            // todo: is this check necessary? look at the latest nlpcaptchalib.php
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
                $use_ssl = true;
            else
                $use_ssl = false;

            $escaped_error = htmlentities($_GET['rerror'], ENT_QUOTES);

            // if it's for wordpress mu, show the errors
            if ($this->is_multi_blog()) {
                $error = $errors->get_error_message('captcha');
                echo '<label for="verification">Verification:</label>';
                echo ($error ? '<p class="error">'.$error.'</p>' : '');
                echo  $this->get_nlpcaptcha_html($escaped_error, $use_ssl);
            }
            
            // for regular wordpress
            else {
                echo $this->get_nlpcaptcha_html($escaped_error, $use_ssl);
            }
        }
        
        function validate_nlpcaptcha_response($errors) {
            // empty so throw the empty response error
            if (empty($_POST['nlpAnswer']) || $_POST['nlpAnswer'] == '') {
                $errors->add('blank_captcha', $this->options['no_response_error']);
                return $errors;
            }

            $response = NLP_Validate($_POST['nlpIdentifier'], $_POST['nlpAnswer'],$this->options['validatekey']);

            // response is bad, add incorrect response error
            if (!$response)
                   $errors->add('captcha_wrong', $this->options['incorrect_response_error']);

           return $errors;
        }
        
        function validate_nlpcaptcha_response_wpmu($result) {
            // must make a check here, otherwise the wp-admin/user-new.php script will keep trying to call
            // this function despite not having called do_action('signup_extra_fields'), so the nlpcaptcha
            // field was never shown. this way it won't validate if it's called in the admin interface
            
            if (!$this->is_authority()) {
                // blogname in 2.6, blog_id prior to that
                // todo: why is this done?
                if (isset($_POST['blog_id']) || isset($_POST['blogname']))
                    return $result;
                    
                // no text entered
                if (empty($_POST['nlpAnswer']) || $_POST['nlpAnswer'] == '') {
                    $result['errors']->add('blank_captcha', $this->options['no_response_error']);
                    return $result;
                }
                
                 $response = NLP_Validate($_POST['nlpIdentifier'], $_POST['nlpAnswer'],$this->options['validatekey']);
  
                // response is bad, add incorrect response error
                // todo: why echo the error here? wpmu specific?
                if (!$response)
                    {
                        $result['errors']->add('captcha_wrong', $this->options['incorrect_response_error']);
                        echo '<div class="error">' . $this->options['incorrect_response_error'] . '</div>';
                    }
                    
                return $result;
            }
        }
        
        // utility methods
        function hash_comment($id) {
            define ("NLPCAPTCHA_WP_HASH_SALT", "4564640638d85f5d7f3694f68e944136d62");
            
            if (function_exists('wp_hash'))
                return wp_hash(NLPCAPTCHA_WP_HASH_SALT . $id);
            else
                return md5(NLPCAPTCHA_WP_HASH_SALT . $this->options['privatekey'] . $id);
        }
        
        function get_nlpcaptcha_html($nlpcaptcha_error, $use_ssl=false) {
            return NLP_Captcha($this->options['publisherkey']);
        }
        
        function show_nlpcaptcha_in_comments() {
            global $user_ID;

            // set the minimum capability needed to skip the captcha if there is one
            if (isset($this->options['bypass_for_registered_users']) && $this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];

            // skip the NLPCaptcha display if the minimum capability is met
            if ((isset($needed_capability) && $needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return;

            else {
                // Did the user fail to match the CAPTCHA? If so, let them know
                if ((isset($_GET['rerror']) && $_GET['rerror'] == 'incorrect-captcha-sol'))
                    echo '<p class="nlpcaptcha-error">' . $this->options['incorrect_response_error'] . "</p>";

                //modify the comment form for the NLPCaptcha widget
               

                add_action('wp_footer', array(&$this, 'save_comment_script')); // preserve the comment that was entered
				
                // todo: replace this with jquery: http://digwp.com/2009/06/including-jquery-in-wordpress-the-right-way/
                // todo: use math to increment+1 the submit button based on what the tab_index option is
                
                    $comment_string = <<<COMMENT_FORM
                        <div id="nlpcaptcha-submit-btn-area">&nbsp;</div>
COMMENT_FORM;
              

                $use_ssl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on");

                $escaped_error = htmlentities($_GET['rerror'], ENT_QUOTES);

                echo $this->get_nlpcaptcha_html(isset($escaped_error) ? $escaped_error : null, $use_ssl) . $comment_string;
           }
        }
        
        // this is what does the submit-button re-ordering
        function save_comment_script() {
            $javascript = <<<JS
                <script type="text/javascript">
                var sub = document.getElementById('submit');
                document.getElementById('nlpcaptcha-submit-btn-area').appendChild (sub);
                document.getElementById('submit').tabIndex = 6;
                if ( typeof _nlpcaptcha_wordpress_savedcomment != 'undefined') {
                        document.getElementById('comment').value = _nlpcaptcha_wordpress_savedcomment;
                }
                document.getElementById('nlpcaptcha_table').style.direction = 'ltr';
                </script>
JS;
            echo $javascript;
        }
        
        // todo: this doesn't seem necessary
        function show_captcha_for_comment() {
            global $user_ID;
            return true;
        }
        
        function check_comment($comment_data) {
            global $user_ID;
            
            if ($this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];
            
            if (($needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return $comment_data;
            
            if ($this->show_captcha_for_comment()) {
                // do not check trackbacks/pingbacks
                if ($comment_data['comment_type'] == '') {
                    $nlpIdentifier = $_POST['nlpIdentifier'];
                    $nlpAnswer = $_POST['nlpAnswer'];
                    
                    $nlpcaptcha_response = NLP_Validate($nlpIdentifier, $nlpAnswer,$this->options['validatekey']);
                    
                    if ($nlpcaptcha_response)
                        return $comment_data;
                        
                    else {
                        $this->saved_error = "incorrect-captcha-sol";
                        
                        // http://codex.wordpress.org/Plugin_API/Filter_Reference#Database_Writes_2
                        add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
                        return $comment_data;
                    }
                }
            }
            
            return $comment_data;
        }
        
        function relative_redirect($location, $comment) {
            if ($this->saved_error != '') {
                // replace #comment- at the end of $location with #commentform
                
                $location = substr($location, 0, strpos($location, '#')) .
                    ((strpos($location, "?") === false) ? "?" : "&") .
                    'rcommentid=' . $comment->comment_ID .
                    '&rerror=' . $this->saved_error .
                    '&rchash=' . $this->hash_comment($comment->comment_ID) .
                    '#commentform';
            }
            
            return $location;
        }
        
        function saved_comment() {
            if (!is_single() && !is_page())
                return;
            
            $comment_id = $_REQUEST['rcommentid'];
            $comment_hash = $_REQUEST['rchash'];
            
            if (empty($comment_id) || empty($comment_hash))
               return;
            
            if ($comment_hash == $this->hash_comment($comment_id)) {
               $comment = get_comment($comment_id);

               // todo: removed double quote from list of 'dangerous characters'
               $com = preg_replace('/([\\/\(\)\+\;\'])/e','\'%\'.dechex(ord(\'$1\'))', $comment->comment_content);
                
               $com = preg_replace('/\\r\\n/m', '\\\n', $com);
                
               echo "
                <script type='text/javascript'>
                var _nlpcaptcha_wordpress_savedcomment =  '" . $com  ."';
                _nlpcaptcha_wordpress_savedcomment = unescape(_nlpcaptcha_wordpress_savedcomment);
                </script>
                ";

                wp_delete_comment($comment->comment_ID);
            }
        }
        
        // todo: is this still needed?
        // this is used for the api keys url in the administration interface
        function blog_domain() {
            $uri = parse_url(get_option('siteurl'));
            return $uri['host'];
        }
        
        // add a settings link to the plugin in the plugin list
        function show_settings_link($links, $file) {
            if ($file == plugin_basename($this->path_to_plugin_directory() . '/wp-nlpcaptcha.php')) {
               $settings_title = __('Settings for this Plugin', 'nlpcaptcha');
               $settings = __('Settings', 'nlpcaptcha');
               $settings_link = '<a href="options-general.php?page=nlpcaptcha/nlpcaptcha.php" title="' . $settings_title . '">' . $settings . '</a>';
               array_unshift($links, $settings_link);
            }
            
            return $links;
        }
        
        // add the settings page
        function add_settings_page() {
            // add the options page
            if ($this->environment == Environment::WordPressMU && $this->is_authority())
                add_submenu_page('wpmu-admin.php', 'NLPCaptcha', 'NLPCaptcha', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));

           
            add_options_page('NLPCaptcha', 'NLPCaptcha', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
        }
        
        // store the xhtml in a separate file and use include on it
        function show_settings_page() {
            include("settings.php");
        }
        
        function build_dropdown($name, $keyvalue, $checked_value) {
            echo '<select name="' . $name . '" id="' . $name . '">' . "\n";
            
            foreach ($keyvalue as $key => $value) {
                $checked = ($value == $checked_value) ? ' selected="selected" ' : '';
                
                echo '\t <option value="' . $value . '"' . $checked . ">$key</option> \n";
                $checked = NULL;
            }
            
            echo "</select> \n";
        }
        
        function capabilities_dropdown() {
            // define choices: Display text => permission slug
            $capabilities = array (
                __('all registered users', 'nlpcaptcha') => 'read',
                __('edit posts', 'nlpcaptcha') => 'edit_posts',
                __('publish posts', 'nlpcaptcha') => 'publish_posts',
                __('moderate comments', 'nlpcaptcha') => 'moderate_comments',
                __('activate plugins', 'nlpcaptcha') => 'activate_plugins'
            );
            
            $this->build_dropdown('nlpcaptcha_options[minimum_bypass_level]', $capabilities, $this->options['minimum_bypass_level']);
        }
    
    } // end class declaration
} // end of class exists clause

?>
