<?php if (!defined('APPLICATION')) exit();

if (c('Garden.Installed')) {
    $Database = Gdn::database();
    $SQL = $Database->sql();

    // DB settings
    saveToConfig('Database.Host', getenv('DB_HOSTNAME'), false);
    saveToConfig('Database.Name', getenv('DB_DATABASE'), false);
    saveToConfig('Database.User', getenv('DB_USERNAME'), false);
    saveToConfig('Database.Password', getenv('DB_PASSWORD'), false);

    saveToConfig('Garden.Email.SupportName', getenv('MAIL_FROM_NAME'), false);
    saveToConfig('Garden.Email.SupportAddress', getenv('MAIL_FROM_ADDRESS'), false);
    saveToConfig('Garden.Email.SmtpHost', getenv('MAIL_SMTP_HOSTNAME'), false);
    saveToConfig('Garden.Email.SmtpUser', getenv('MAIL_SMTP_USERNAME'), false);
    saveToConfig('Garden.Email.SmtpPassword', getenv('MAIL_SMTP_PASSWORD'), false);
    saveToConfig('Garden.Email.SmtpPort', getenv('MAIL_SMTP_PORT'), false);
    saveToConfig('Garden.Email.SmtpSecurity', getenv('MAIL_SMTP_SECURITY'), false);

    //Disable plugins
    saveToConfig('EnabledPlugins.stubcontent', false);

    //Enable plugins
    saveToConfig('EnabledPlugins.Topcoder', true);
    saveToConfig('EnabledPlugins.rich-editor',true);
    saveToConfig('EnabledPlugins.recaptcha',  true);
    saveToConfig('EnabledPlugins.editor', true);
    saveToConfig('EnabledPlugins.emojiextender', true);
    saveToConfig('EnabledPlugins.GooglePrettify', true);
    saveToConfig('EnabledPlugins.Quotes', true);
    saveToConfig('EnabledPlugins.swagger-ui', true);
    saveToConfig('EnabledPlugins.oauth2', true);

    // Set Theme Options
    saveToConfig('Garden.ThemeOptions.Styles.Key', 'Coral');
    saveToConfig('Garden.ThemeOptions.Styles.Value', '%s_coral');
    saveToConfig('Garden.ThemeOptions.Options.panelToLeft',true);

    // Add settings for the Topcoder plugin
    if(c('Plugins.Topcoder.BaseApiURL') === false) {
        saveToConfig('Plugins.Topcoder.BaseApiURL', getenv('TOPCODER_PLUGIN_BASE_API_URL'), false);
        saveToConfig('Plugins.Topcoder.MemberApiURI', getenv('TOPCODER_PLUGIN_MEMBER_API_URI'), false);
        saveToConfig('Plugins.Topcoder.MemberProfileURL', getenv('TOPCODER_PLUGIN_MEMBER_PROFILE_URL'), false);
    }

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

    //Add settings for the OAuth 2 SSO plugin
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
}