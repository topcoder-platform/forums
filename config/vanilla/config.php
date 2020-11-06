<?php if (!defined('APPLICATION')) exit();

$Configuration['Garden']['Installed'] = true;

$Configuration['Garden']['SignIn']['Popup'] = false; // Should the sign-in link pop up or go to it's own page

// Conversations
$Configuration['Conversations']['Version'] = '3.0';
$Configuration['Conversations']['Moderation']['Allow'] = true;

// Database
$Configuration['Database']['Name'] = getenv('MYSQL_DATABASE');
$Configuration['Database']['Host'] = getenv('MYSQL_HOST');
$Configuration['Database']['User'] = getenv('MYSQL_ROOT_USER');
$Configuration['Database']['Password'] = getenv('MYSQL_ROOT_PASSWORD');

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
$Configuration['EnabledPlugins']['oauth2'] = false;
$Configuration['EnabledPlugins']['Groups'] = true;
$Configuration['EnabledPlugins']['Filestack'] = true;
$Configuration['EnabledPlugins']['Sumologic'] = true;

// Debug
$Configuration['Debug'] = FALSE;
$Configuration['Vanilla']['SSO']['Debug'] = TRUE;

// Email contents
$Configuration['Vanilla']['Activity']['ShowDiscussionBody'] = true;

// Feature
$Configuration['Feature']['NewFlyouts']['Enabled'] = true;
$Configuration['Vanilla']['EnableCategorygiFollowing'] = true;

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