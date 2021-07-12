<?php

/**
 * @file
 * Drupal settings.pantheon.saml.php file provided by
 * utexas_pantheon_saml_auth.
 */

// Ensure correct SSL port. See
// https://pantheon.io/docs/server_name-and-server_port#set-server_port-correctly
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  if (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) && $_SERVER['HTTP_USER_AGENT_HTTPS'] === 'ON') {
    $_SERVER['SERVER_PORT'] = 443;
  }
  else {
    $_SERVER['SERVER_PORT'] = 80;
  }
}

// Provide universal absolute path to the installation.
$settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/vendor/simplesamlphp/simplesamlphp';

// Which attributes from Enterprise Authentication should we use for various user fields.
$config['simplesamlphp_auth.settings']['unique_id'] = "uid";
$config['simplesamlphp_auth.settings']['user_name'] = "uid";
$config['simplesamlphp_auth.settings']['mail_attr'] = "mail";

// We want to make sure User 1 can always log in using local authentication
// (even if SAML auth is enabled).  There are two settings required for this:
// * simplesamlphp_auth.settings -> allow.default_login
// * simplesamlphp_auth.settings -> allow_default_login_users
//
// Allow authentication with local Drupal accounts.
$config['simplesamlphp_auth.settings']['allow.default_login'] = 1;

// Change the text for the SAML link on the login page.
$config['simplesamlphp_auth.settings']['login_link_display_name'] = "Log in with UT EID";

// After logging out of Drupal, send the user to the UTLogin logout page, so
// their session is ended there as well.
// NOTE: By default they'll be redirected to www.utexas.edu once they're
// logged out of UTLogin.  See this page for more on the UTLogin logout URL:
// https://www.utexas.edu/its/help/utlogin/2377
$config['simplesamlphp_auth.settings']['logout_goto_url'] = "https://enterprise.login.utexas.edu/idp/profile/Logout";

// Security settings for how to handle Cookies over requests.
$config['simplesamlphp_auth.settings']['secure'] = 1;
$config['simplesamlphp_auth.settings']['httponly'] = 1;
