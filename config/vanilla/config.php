<?php if (!defined('APPLICATION')) exit();

// Conversations
$Configuration['Conversations']['Version'] = '3.0';
$Configuration['Conversations']['Moderation']['Allow'] = true;

// Database
$Configuration['Database']['Name'] = getenv('MYSQL_DATABASE');
$Configuration['Database']['Host'] = getenv('MYSQL_HOST');
$Configuration['Database']['User'] = getenv('MYSQL_ROOT_USER');
$Configuration['Database']['Password'] = getenv('MYSQL_ROOT_PASSWORD');

// Topcoder plugin
$Configuration['Plugins']['Topcoder']['BaseApiURL'] = getenv('TOPCODER_PLUGIN_BASE_API_URL');
$Configuration['Plugins']['Topcoder']['MemberApiURI'] = getenv('TOPCODER_PLUGIN_MEMBER_API_URI');
$Configuration['Plugins']['Topcoder']['RoleApiURI'] =  getenv('TOPCODER_PLUGIN_ROLE_API_URI');
$Configuration['Plugins']['Topcoder']['MemberProfileURL'] = getenv('TOPCODER_PLUGIN_MEMBER_PROFILE_URL');
$Configuration['Plugins']['Topcoder']['UseTopcoderAuthToken'] = getenv('TOPCODER_PLUGIN_USE_AUTH_TOKEN');

// Topcoder M2M Auth0
$Configuration['Plugins']['Topcoder']['M2M']['Auth0Audience'] = getenv('AUTH0_AUDIENCE');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0ClientId'] = getenv('AUTH0_CLIENT_ID');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0ClientSecret'] = getenv('AUTH0_CLIENT_SECRET');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0Url'] = getenv('AUTH0_URL');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0ProxyServerUrl'] = getenv('AUTH0_PROXY_SERVER_URL');

// Topcoder SSO Auth0
$Configuration['Plugins']['Topcoder']['SSO']['Auth0Domain'] = 'https://topcoder-dev.auth0.com/';
$Configuration['Plugins']['Topcoder']['SSO']['Auth0Audience'] = 'JFDo7HMkf0q2CkVFHojy3zHWafziprhT';
$Configuration['Plugins']['Topcoder']['SSO']['Auth0ClientSecret'] = getenv('AUTH0_CLIENT_SECRET');

// Filestack
$Configuration['Plugins']['Filestack']['ApiKey'] = getenv('FILESTACK_API_KEY');

// EnabledApplications
$Configuration['EnabledApplications']['Conversations'] = 'conversations';
$Configuration['EnabledApplications']['Vanilla'] = 'vanilla';

// EnabledPlugins
$Configuration['EnabledPlugins']['recaptcha'] = true;
$Configuration['EnabledPlugins']['GettingStarted'] = 'GettingStarted';
$Configuration['EnabledPlugins']['stubcontent'] = false;
$Configuration['EnabledPlugins']['Topcoder'] = true;
$Configuration['EnabledPlugins']['rich-editor'] = true;
$Configuration['EnabledPlugins']['editor'] = true;
$Configuration['EnabledPlugins']['emojiextender'] = true;
$Configuration['EnabledPlugins']['GooglePrettify'] = true;
$Configuration['EnabledPlugins']['Quotes'] = true;
$Configuration['EnabledPlugins']['swagger-ui'] = true;
$Configuration['EnabledPlugins']['oauth2'] = true;
// $Configuration['EnabledPlugins']['Groups'] = true;
$Configuration['EnabledPlugins']['Filestack'] = true;

// Debug
$Configuration['Debug'] = TRUE;
$Configuration['Vanilla']['SSO']['Debug'] = TRUE;

// Feature
$Configuration['Feature']['NewFlyouts']['Enabled'] = true;

// Garden
$Configuration['Garden']['Title'] = 'Vanilla';
$Configuration['Garden']['Cookie']['Salt'] = 'rLpGSLgZD1AGdJ4n';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Registration']['ConfirmEmail'] = true;
$Configuration['Garden']['Email']['SupportName'] = 'Vanilla';
$Configuration['Garden']['Email']['Format'] = 'text';
$Configuration['Garden']['SystemUserID'] = '1';
$Configuration['Garden']['UpdateToken'] = 'c3988cd76f721f1a03d2c347ab6655609a548425';
$Configuration['Garden']['InputFormatter'] = 'Rich';
$Configuration['Garden']['Version'] = 'Undefined';
$Configuration['Garden']['CanProcessImages'] = true;
$Configuration['Garden']['Installed'] = true;
$Configuration['Garden']['Theme'] = 'topcoder-theme';
$Configuration['Garden']['MobileTheme'] = 'topcoder-theme';
$Configuration['Garden']['Profile']['EditPhotos'] = false;

// Plugins
$Configuration['Plugins']['editor']['ForceWysiwyg'] = false;
$Configuration['Plugins']['GooglePrettify']['LineNumbers'] = '';
$Configuration['Plugins']['GooglePrettify']['NoCssFile'] = '';
$Configuration['Plugins']['GooglePrettify']['UseTabby'] = '';
$Configuration['Plugins']['GooglePrettify']['Language'] = '';
$Configuration['Plugins']['GettingStarted']['Dashboard'] = '1';
$Configuration['Plugins']['GettingStarted']['Plugins'] = '1';

// Routes
$Configuration['Routes']['YXBwbGUtdG91Y2gtaWNvbi5wbmc='] = array (
    0 => 'utility/showtouchicon',
    1 => 'Internal',
);
$Configuration['Routes']['cm9ib3RzLnR4dA=='] = array (
    0 => '/robots',
    1 => 'Internal',
);
$Configuration['Routes']['dXRpbGl0eS9yb2JvdHM='] = array (
    0 => '/robots',
    1 => 'Internal',
);
$Configuration['Routes']['Y29udGFpbmVyLmh0bWw='] = array (
    0 => 'staticcontent/container',
    1 => 'Internal',
);
$Configuration['Routes']['DefaultController'] = 'discussions';

// Vanilla
$Configuration['Vanilla']['Version'] = '3.0';

// Last edited by admin (172.26.0.1) 2020-09-03 13:16:33