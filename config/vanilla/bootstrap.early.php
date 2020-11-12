<?php if (!defined('APPLICATION')) exit();

if (c('Garden.Installed')) {
    $Database = Gdn::database();
    $SQL = $Database->sql();

    // Cache Settings
    saveToConfig('Cache.Enabled', getenv('CACHE_ENABLED'), true);
    saveToConfig('Cache.Method', getenv('CACHE_METHOD'), 'dirtycache');
    saveToConfig('memcached.Store', getenv('MEMCACHED_SERVER'), 'localhost:11211');

    saveToConfig('Garden.Email.SupportName', getenv('MAIL_FROM_NAME') );
    saveToConfig('Garden.Email.SupportAddress', getenv('MAIL_FROM_ADDRESS'));
    saveToConfig('Garden.Email.UseSmtp', getenv('MAIL_USE_SMTP'));
    saveToConfig('Garden.Email.SmtpHost', getenv('MAIL_SMTP_HOSTNAME'));
    saveToConfig('Garden.Email.SmtpUser', getenv('MAIL_SMTP_USERNAME'));
    saveToConfig('Garden.Email.SmtpPassword', getenv('MAIL_SMTP_PASSWORD'));
    saveToConfig('Garden.Email.SmtpPort', getenv('MAIL_SMTP_PORT'));
    saveToConfig('Garden.Email.SmtpSecurity', getenv('MAIL_SMTP_SECURITY'));

    // Appearance
    saveToConfig('Garden.Theme', 'topcoder-theme', false);
    saveToConfig('Garden.MobileTheme', 'topcoder-theme', false);
    saveToConfig('Feature.NewFlyouts.Enabled', true);

    // Feature
    saveToConfig('Garden.EditContentTimeout', -1, false);

    // Profile settings
    saveToConfig('Garden.Profile.EditPhotos', false);

    // Add settings for the Topcoder plugin
    saveToConfig('Plugins.Topcoder.BaseApiURL', getenv('TOPCODER_PLUGIN_BASE_API_URL'),false);
    saveToConfig('Plugins.Topcoder.MemberApiURI', getenv('TOPCODER_PLUGIN_MEMBER_API_URI'),false);
    saveToConfig('Plugins.Topcoder.RoleApiURI', getenv('TOPCODER_PLUGIN_ROLE_API_URI'),false);
    saveToConfig('Plugins.Topcoder.ResourceRolesApiURI', '/v5/resource-roles', false);
    saveToConfig('Plugins.Topcoder.ResourcesApiURI', '/v5/resources', false);
    saveToConfig('Plugins.Topcoder.MemberProfileURL', getenv('TOPCODER_PLUGIN_MEMBER_PROFILE_URL'), false); // prod: 
    saveToConfig('Plugins.Topcoder.UseTopcoderAuthToken', getenv('TOPCODER_PLUGIN_USE_AUTH_TOKEN'), false);

    saveToConfig('Plugins.Topcoder.ValidIssuers', str_replace(["[", "]", "\\", "\"", " "], '', getenv('VALID_ISSUERS')));

    //Add settings for Topcoder M2M Auth0
    saveToConfig('Plugins.Topcoder.M2M.Auth0Audience', getenv('AUTH0_AUDIENCE'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0ClientId', getenv('AUTH0_CLIENT_ID'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0ClientSecret', getenv('AUTH0_CLIENT_SECRET'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0Url', getenv('AUTH0_URL'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0ProxyServerUrl', getenv('AUTH0_PROXY_SERVER_URL'));

     //Add settings for Topcoder SSO Auth0
    saveToConfig('Plugins.Topcoder.SSO.Auth0Domain', getenv('TOPCODER_PLUGIN_SSO_AUTH0DOMAIN'));
    saveToConfig('Plugins.Topcoder.SSO.AuthorizationURI', '/v3/authorizations/1');
    saveToConfig('Plugins.Topcoder.SSO.CookieName', 'v3jwt',false);
    saveToConfig('Plugins.Topcoder.SSO.TopcoderRS256.ID', getenv('TOPCODER_PLUGIN_SSO_TOPCODER_RS256_ID'), false);
    saveToConfig('Plugins.Topcoder.SSO.TopcoderHS256.ID', getenv('TOPCODER_PLUGIN_SSO_TOPCODER_HS256_ID'), false);
    
    saveToConfig('Plugins.Topcoder.SSO.TopcoderHS256.Secret', getenv('TOPCODER_HS256_SECRET') );
    saveToConfig('Plugins.Topcoder.SSO.TopcoderRS256.UsernameClaim', 'nickname',false);
    saveToConfig('Plugins.Topcoder.SSO.TopcoderHS256.UsernameClaim', 'handle',false);
    $topcoderSSOAuth0Url = getenv('TOPCODER_PLUGIN_SSO_REFRESHTOKENURL');
    saveToConfig('Plugins.Topcoder.SSO.RefreshTokenURL', $topcoderSSOAuth0Url,false);
    $signInUrl = getenv('TOPCODER_PLUGIN_SIGNIN_URL');
    $signOutUrl = getenv('TOPCODER_PLUGIN_SIGNOUT_URL');
    if($signInUrl === false) {
        $signInUrl =$topcoderSSOAuth0Url.'?retUrl='.urlencode('https://'.$_SERVER['SERVER_NAME'].'/');
    }
    if($signOutUrl === false) {
        $signOutUrl =$topcoderSSOAuth0Url.'?logout=true&retUrl='.urlencode('https://'.$_SERVER['SERVER_NAME'].'/');
    }
    saveToConfig('Plugins.Topcoder.AuthenticationProvider.SignInUrl', $signInUrl,false);
    saveToConfig('Plugins.Topcoder.AuthenticationProvider.SignOutUrl', $signOutUrl,false);
    saveToConfig('Plugins.Topcoder.AuthenticationProvider.RegisterUrl', getenv('TOPCODER_PLUGIN_AUTHENTICATIONPROVIDER_REGISTERURL'),false);

    // Filestack
    saveToConfig('Plugins.Filestack.ApiKey', getenv('FILESTACK_API_KEY'),false);

    // SumoLogic
    saveToConfig('Plugins.Sumologic.HttpSourceURL', '',false);
    saveToConfig('Plugins.Sumologic.BatchSize', '10',false);

    // Add settings for the Editor plugin
    if(c('Plugins.editor.ForceWysiwyg') === false) {
        saveToConfig('Plugins.editor.ForceWysiwyg', false);
    }

    // Add settings for the Syntax Prettifier plugin
    if(c('Plugins.GooglePrettify.LineNumbers') === false) {
        saveToConfig('Plugins.GooglePrettify.LineNumbers', '');
        saveToConfig('Plugins.GooglePrettify.NoCssFile', '');
        saveToConfig('Plugins.GooglePrettify.UseTabby', '');
        saveToConfig('Plugins.GooglePrettify.Language', '');
    }

    // Add settings for the Recaptcha plugin
    if(c('Recaptcha.PrivateKey') === false) {
        saveToConfig('Recaptcha.PrivateKey', getenv('RECAPTCHA_PLUGIN_PRIVATE_KEY'), false);
        saveToConfig('Recaptcha.PublicKey', getenv('RECAPTCHA_PLUGIN_PUBLIC_KEY'), false);
    }

    // Fix: Add the 'topcoder' role type in Role Table. It should be removed after upgrading existing DB.
    // The Topcoder plugin's setup method will upgrade DB during Vanilla installation
    $SQL->query('alter table GDN_Role modify Type enum(\'topcoder\', \'guest\', \'unconfirmed\', \'applicant\', \'member\', \'moderator\', \'administrator\')');

}