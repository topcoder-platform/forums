<?php if (!defined('APPLICATION')) exit();

if (c('Garden.Installed')) {
    $Database = Gdn::database();
    $SQL = $Database->sql();

    // DB settings
    saveToConfig('Database.Host', getenv('DB_HOSTNAME'), false);
    saveToConfig('Database.Name', getenv('DB_DATABASE'), false);
    saveToConfig('Database.User', getenv('DB_USERNAME'), false);
    saveToConfig('Database.Password', getenv('DB_PASSWORD'), false);

    // Cache Settings
    saveToConfig('Cache.Enabled', getenv('CACHE_ENABLED'), true);
    saveToConfig('Cache.Method', getenv('CACHE_METHOD'), 'dirtycache');
    saveToConfig('memcached.Store', getenv('MEMCACHED_SERVER'), 'localhost:11211');

    saveToConfig('Garden.Email.SupportName', getenv('MAIL_FROM_NAME'), false);
    saveToConfig('Garden.Email.SupportAddress', getenv('MAIL_FROM_ADDRESS'), false);
    saveToConfig('Garden.Email.UseSmtp', getenv('MAIL_USE_SMTP'), false);
    saveToConfig('Garden.Email.SmtpHost', getenv('MAIL_SMTP_HOSTNAME'), false);
    saveToConfig('Garden.Email.SmtpUser', getenv('MAIL_SMTP_USERNAME'), false);
    saveToConfig('Garden.Email.SmtpPassword', getenv('MAIL_SMTP_PASSWORD'), false);
    saveToConfig('Garden.Email.SmtpPort', getenv('MAIL_SMTP_PORT'), false);
    saveToConfig('Garden.Email.SmtpSecurity', getenv('MAIL_SMTP_SECURITY'), false);

    //Disable plugins
    saveToConfig('EnabledPlugins.stubcontent', false);

    //Enable plugins
    saveToConfig('EnabledPlugins.Topcoder', true);
    //saveToConfig('EnabledPlugins.Groups', true);
    saveToConfig('EnabledPlugins.Filestack', true);
    saveToConfig('EnabledPlugins.rich-editor',true);
    saveToConfig('EnabledPlugins.recaptcha',  true);
    saveToConfig('EnabledPlugins.editor', true);
    saveToConfig('EnabledPlugins.emojiextender', true);
    saveToConfig('EnabledPlugins.GooglePrettify', true);
    saveToConfig('EnabledPlugins.Quotes', true);
    saveToConfig('EnabledPlugins.swagger-ui', true);
    saveToConfig('EnabledPlugins.oauth2', true);

    // Appearance
    saveToConfig('Garden.Theme', 'topcoder-theme');
    saveToConfig('Garden.MobileTheme', 'topcoder-theme');
    saveToConfig('Feature.NewFlyouts.Enabled', true);

    // Profile settings
    saveToConfig('Garden.Profile.EditPhotos', false);

    // Add settings for the Topcoder plugin
    saveToConfig('Plugins.Topcoder.BaseApiURL', getenv('TOPCODER_PLUGIN_BASE_API_URL'), false);
    saveToConfig('Plugins.Topcoder.MemberApiURI', getenv('TOPCODER_PLUGIN_MEMBER_API_URI'), false);
    saveToConfig('Plugins.Topcoder.RoleApiURI', getenv('TOPCODER_PLUGIN_ROLE_API_URI'), false);
    saveToConfig('Plugins.Topcoder.MemberProfileURL', getenv('TOPCODER_PLUGIN_MEMBER_PROFILE_URL'), false);
    saveToConfig('Plugins.Topcoder.UseTopcoderAuthToken', getenv('TOPCODER_PLUGIN_USE_AUTH_TOKEN'), false);

    //Add settings for Topcoder M2M Auth0
    saveToConfig('Plugins.Topcoder.M2M.Auth0Audience', getenv('AUTH0_AUDIENCE'), false);
    saveToConfig('Plugins.Topcoder.M2M.Auth0ClientId', getenv('AUTH0_CLIENT_ID'), false);
    saveToConfig('Plugins.Topcoder.M2M.Auth0ClientSecret', getenv('AUTH0_CLIENT_SECRET'), false);
    saveToConfig('Plugins.Topcoder.M2M.Auth0Url', getenv('AUTH0_URL'), false);
    saveToConfig('Plugins.Topcoder.M2M.Auth0ProxyServerUrl', getenv('AUTH0_PROXY_SERVER_URL'), false);

     //Add settings for Topcoder SSO Auth0
    saveToConfig('Plugins.Topcoder.SSO.Auth0Domain', 'https://topcoder-dev.auth0.com/', false);
    saveToConfig('Plugins.Topcoder.SSO.Auth0Audience', 'JFDo7HMkf0q2CkVFHojy3zHWafziprhT', false);
    saveToConfig('Plugins.Topcoder.SSO.Auth0ClientSecret', getenv('AUTH_SECRET'), false);

    // Filestack
    saveToConfig('Plugins.Filestack.ApiKey', getenv('FILESTACK_API_KEY'), false);

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

    // Add settings for the OAuth 2 SSO plugin
    if ($SQL->getWhere('UserAuthenticationProvider', ['AuthenticationKey' => 'oauth2'])->numRows() == 0) {
        $attributes = array(
            'AssociationKey'=> getenv('TOPCODER_AUTH0_ASSOCIATION_KEY'),
            'AuthorizeUrl'=> getenv('TOPCODER_AUTH0_AUTHORIZE_URL'),
            'TokenUrl'=> getenv('TOPCODER_AUTH0_TOKEN_URL'),
            'AcceptedScope'=> getenv('TOPCODER_AUTH0_ACCEPTED_SCOPE'),
            'ProfileKeyEmail'=> getenv('TOPCODER_AUTH0_PROFILE_KEY_EMAIL'),
            'ProfileKeyPhoto'=> getenv('TOPCODER_AUTH0_PROFILE_KEY_PHOTO'),
            'ProfileKeyName'=> getenv('TOPCODER_AUTH0_PROFILE_KEY_NAME'),
            'ProfileKeyFullName'=> getenv('TOPCODER_AUTH0_PROFILE_KEY_FULL_NAME'),
            'ProfileKeyUniqueID'=> getenv('TOPCODER_AUTH0_PROFILE_KEY_UNIQUE_ID'),
            'Prompt'=> getenv('TOPCODER_AUTH0_PROMPT'),
            'BearerToken'=> getenv('TOPCODER_AUTH0_BEARER_TOKEN'),
            'BaseUrl'=> getenv('TOPCODER_AUTH0_BASE_URL')
        );
        $SQL->insert('UserAuthenticationProvider', [
            'AuthenticationKey' => 'oauth2',
            'AuthenticationSchemeAlias' => 'oauth2',
            'Name' => 'oauth2',
            'AssociationSecret' => getenv('TOPCODER_AUTH0_SECRET'),
            'RegisterUrl' => getenv('TOPCODER_AUTH0_REGISTER_URL'),
            'SignInUrl' => getenv('TOPCODER_AUTH0_SIGNIN_URL'),
            'SignOutUrl' => getenv('TOPCODER_AUTH0_SIGNOUT_URL'),
            'ProfileUrl' => getenv('TOPCODER_AUTH0_PROFILE_URL'),
            'Attributes' => json_encode($attributes,JSON_UNESCAPED_SLASHES),
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