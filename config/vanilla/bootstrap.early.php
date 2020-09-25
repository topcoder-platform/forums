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
    saveToConfig('Garden.Theme', 'topcoder-theme');
    saveToConfig('Garden.MobileTheme', 'topcoder-theme');
    saveToConfig('Feature.NewFlyouts.Enabled', true);

    // Profile settings
    saveToConfig('Garden.Profile.EditPhotos', false);

    // Add settings for the Topcoder plugin
    saveToConfig('Plugins.Topcoder.BaseApiURL', getenv('TOPCODER_PLUGIN_BASE_API_URL'),false);
    saveToConfig('Plugins.Topcoder.MemberApiURI', getenv('TOPCODER_PLUGIN_MEMBER_API_URI'),false);
    saveToConfig('Plugins.Topcoder.RoleApiURI', getenv('TOPCODER_PLUGIN_ROLE_API_URI'),false);
    saveToConfig('Plugins.Topcoder.MemberProfileURL', getenv('TOPCODER_PLUGIN_MEMBER_PROFILE_URL'),false);
    saveToConfig('Plugins.Topcoder.UseTopcoderAuthToken', getenv('TOPCODER_PLUGIN_USE_AUTH_TOKEN'),false);

    //Add settings for Topcoder M2M Auth0
    saveToConfig('Plugins.Topcoder.M2M.Auth0Audience', getenv('AUTH0_AUDIENCE') );
    saveToConfig('Plugins.Topcoder.M2M.Auth0ClientId', getenv('AUTH0_CLIENT_ID'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0ClientSecret', getenv('AUTH0_CLIENT_SECRET'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0Url', getenv('AUTH0_URL'));
    saveToConfig('Plugins.Topcoder.M2M.Auth0ProxyServerUrl', getenv('AUTH0_PROXY_SERVER_URL'));

     //Add settings for Topcoder SSO Auth0
    saveToConfig('Plugins.Topcoder.SSO.Auth0Domain', 'https://api.topcoder-dev.com');
    saveToConfig('Plugins.Topcoder.SSO.AuthorizationURI', '/v3/authorizations/1');
    saveToConfig('Plugins.Topcoder.SSO.Auth0Audience', 'JFDo7HMkf0q2CkVFHojy3zHWafziprhT');
    saveToConfig('Plugins.Topcoder.SSO.Auth0ClientSecret', getenv('AUTH_SECRET'));
    saveToConfig('Plugins.Topcoder.SSO.TopcoderH256Secret', getenv('TOPCODER_HS256_SECRET') );
    saveToConfig('Plugins.Topcoder.SSO.CookieName', 'v3jwt',false);
    saveToConfig('Plugins.Topcoder.SSO.UsernameClaim', 'handle',false);
    saveToConfig('Plugins.Topcoder.SSO.RefreshTokenURL', 'https://accounts-auth0.topcoder-dev.com/',false);

   
    // Filestack
    saveToConfig('Plugins.Filestack.ApiKey', getenv('FILESTACK_API_KEY'),false);

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


    // Fix: OAuth 2 SSO should be inactive and not by default. It should be removed later.
    if ($SQL->getWhere('UserAuthenticationProvider', ['AuthenticationKey' => 'oauth2'])->numRows() > 0) {
        $SQL->update('UserAuthenticationProvider')
            ->set('Active', 0)
            ->set('IsDefault',0)
            ->where('AuthenticationKey' , 'oauth2')->put();
    }

    // Add Topcoder User Authentication Provider
    if ($SQL->getWhere('UserAuthenticationProvider', ['AuthenticationKey' => 'topcoder'])->numRows() == 0) {
        $signUrl = getenv('TOPCODER_PLUGIN_SIGNIN_URL');
        if($signUrl === false) {
            $signUrl ='https://accounts-auth0.topcoder-dev.com/?retUrl='.urlencode('https://'.$_SERVER['SERVER_NAME'].'/');
        }
        $SQL->insert('UserAuthenticationProvider', [
            'AuthenticationKey' => 'topcoder',
            'AuthenticationSchemeAlias' => 'topcoder',
            'Name' => 'topcoder',
            'SignInUrl' => $signUrl,
            'Active' => 1,
            'IsDefault' => 1
        ]);
    }

    // Define Topcoder Member role
    $topcoderRoleName = 'Topcoder Member';
    if($SQL->getWhere('Role', ['Name' => $topcoderRoleName])->numRows() == 0) {
        $roleID = $SQL->insert('Role', [
            'Name' => $topcoderRoleName,
            'Type' => 'member',
            'Deletable' => 0,
            'CanSession' => 1,
            'PersonalInfo' => 1,
            'Description' => 'Topcoder Members can edit Notification Preferences and participate in discussions.'
        ]);

        // Define the set of permissions to singIn, view Profiles and edit Notification Preferences
        $SQL->insert('Permission', [
            'RoleID' => $roleID,
            '`Garden.SignIn.Allow`' => 1,
            '`Garden.Profiles.View`' => 1,
            '`Garden.PersonalInfo.View`' => 1,
            '`Garden.AdvancedNotifications.Allow`' => 1
        ]);

        // Define the set of permissions to view categories
        $SQL->insert('Permission', [
            'RoleID' => $roleID,
            'JunctionTable' => 'Category',
            'JunctionColumn' => 'PermissionCategoryID',
            'JunctionID' => -1,
            '`Vanilla.Discussions.View`' => 1
        ]);
    }

}