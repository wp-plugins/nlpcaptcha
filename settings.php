<?php

    if (defined('ALLOW_INCLUDE') === false)
        die('no direct access');

?>

<div class="wrap">
   <a name="nlpcaptcha"></a>
   <h2><?php _e('NLPCaptcha Options', 'nlpcaptcha'); ?></h2>
   <p><?php _e('NLPCaptcha is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.', 'nlpcaptcha'); ?></p>
   
   <form method="post" action="options.php">
      <?php settings_fields('nlpcaptcha_options_group'); ?>

      <h3><?php _e('Authentication', 'nlpcaptcha'); ?></h3>
      <p><?php _e('These keys are required before you are able to do anything else.', 'nlpcaptcha'); ?> <?php _e('You can get the keys', 'nlpcaptcha'); ?> <a href="<?php echo nlpcaptcha_get_signup_url($this->blog_domain(), 'wordpress');?>" title="<?php _e('Get your NLPCaptcha API Keys', 'nlpcaptcha'); ?>"><?php _e('here', 'nlpcaptcha'); ?></a>.</p>
      <p><?php _e('Be sure not to mix them up! The public and private keys are not interchangeable!'); ?></p>
      
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Publisher Key', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[publisherkey]" size="40" value="<?php echo $this->options['publisherkey']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Validate Key', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[validatekey]" size="40" value="<?php echo $this->options['validatekey']; ?>" />
            </td>
         </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Private Key', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[privatekey]" size="40" value="<?php echo $this->options['privatekey']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Comment Options', 'nlpcaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Activation', 'nlpcaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="nlpcaptcha_options[show_in_comments]" name="nlpcaptcha_options[show_in_comments]" value="1" <?php checked('1', $this->options['show_in_comments']); ?> />
               <label for="nlpcaptcha_options[show_in_comments]"><?php _e('Enable for comments form', 'nlpcaptcha'); ?></label>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Target', 'nlpcaptcha'); ?></th>
            <td>
               <input type="checkbox" id="nlpcaptcha_options[bypass_for_registered_users]" name="nlpcaptcha_options[bypass_for_registered_users]" value="1" <?php checked('1', $this->options['bypass_for_registered_users']); ?> />
               <label for="nlpcaptcha_options[bypass_for_registered_users]"><?php _e('Hide for Registered Users who can', 'nlpcaptcha'); ?></label>
               <?php $this->capabilities_dropdown(); ?>
            </td>
         </tr>

        <tr valign="top">
            <th scope="row"><?php _e('Tab Index', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[comments_tab_index]" size="10" value="<?php echo $this->options['comments_tab_index']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Registration Options', 'nlpcaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Activation', 'nlpcaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="nlpcaptcha_options[show_in_registration]" name="nlpcaptcha_options[show_in_registration]" value="1" <?php checked('1', $this->options['show_in_registration']); ?> />
               <label for="nlpcaptcha_options[show_in_registration]"><?php _e('Enable for registration form', 'nlpcaptcha'); ?></label>
            </td>
         </tr>
         
               
         <tr valign="top">
            <th scope="row"><?php _e('Tab Index', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[registration_tab_index]" size="10" value="<?php echo $this->options['registration_tab_index']; ?>" />
            </td>
         </tr>
      </table>
      
            
      <h3><?php _e('Error Messages', 'nlpcaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('NLPCaptcha Ignored', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[no_response_error]" size="70" value="<?php echo $this->options['no_response_error']; ?>" />
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Incorrect Guess', 'nlpcaptcha'); ?></th>
            <td>
               <input type="text" name="nlpcaptcha_options[incorrect_response_error]" size="70" value="<?php echo $this->options['incorrect_response_error']; ?>" />
            </td>
         </tr>
      </table>

      <p class="submit"><input type="submit" class="button-primary" title="<?php _e('Save NLPCaptcha Options') ?>" value="<?php _e('Save NLPCaptcha Changes') ?> &raquo;" /></p>
   </form>
   
   <?php do_settings_sections('nlpcaptcha_options_page'); ?>
</div>