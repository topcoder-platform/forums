<?php if (!defined('APPLICATION')) exit();

if (c('Garden.Installed')) {
    $Database = Gdn::database();
    $SQL = $Database->sql();

    // Logging
    // saveToConfig('DebugAssets', true);
    // saveToConfig('Debug', true);

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
        saveToConfig('Plugins.Topcoder.BaseApiURL', 'https://api.topcoder-dev.com');
        saveToConfig('Plugins.Topcoder.MemberApiURI', '/v3/members');
        saveToConfig('Plugins.Topcoder.MemberProfileURL', 'https://www.topcoder.com/members');
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

    //Add settings for the OAuth 2 SSO plugin
    if ($SQL->getWhere('UserAuthenticationProvider', ['AuthenticationKey' => 'oauth2'])->numRows() == 0) {
        $SQL->insert('UserAuthenticationProvider', [
            'AuthenticationKey' => 'oauth2',
            'AuthenticationSchemeAlias' => 'oauth2',
            'Name' => 'oauth2',
            'AssociationSecret' => 'yvaegnvYhFhWUwL3s0nObhZz76ZVYE4qVms3z75ngm3ubHu1ZmwyKStML7N_i9nE',
            'RegisterUrl' => '',
            'SignInUrl' => 'https://topcoder-dev.auth0.com',
            'SignOutUrl' => '',
            'ProfileUrl' => 'https://topcoder-dev.auth0.com/userinfo',
            'Attributes' => '{"AssociationKey":"Q9iRXM0QzGRidhcUK8MSTXxBRrmvrjA4","AuthorizeUrl":"https://topcoder-dev.auth0.com/authorize","TokenUrl":"https://topcoder-dev.auth0.com/oauth/token","AcceptedScope":"openid email profile","ProfileKeyEmail":"email","ProfileKeyPhoto":"picture","ProfileKeyName":"nickname","ProfileKeyFullName":"name","ProfileKeyUniqueID":"sub","Prompt":"login","BearerToken":false,"BaseUrl":"https://topcoder-dev.auth0.com"}',
            'Active' => 1,
            'IsDefault' => 1
        ]);
    }
}