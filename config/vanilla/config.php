<?php if (!defined('APPLICATION')) exit();

$Configuration['Garden']['Installed'] = true;
//Embed
$Configuration['Garden']['Embed']['Allow'] = true;
$Configuration['Garden']['Embed']['ForceForum'] = true;

$Configuration['Database']['Name'] = getenv('MYSQL_DATABASE');
$Configuration['Database']['Host'] = getenv('MYSQL_HOST');
$Configuration['Database']['User'] = getenv('MYSQL_ROOT_USER');
$Configuration['Database']['Password'] = getenv('MYSQL_ROOT_PASSWORD');

// Cache
$Configuration['Cache']['Enabled'] = strtolower(getenv('CACHE_ENABLED')) === "true";
$Configuration['Cache']['Method'] = getenv('CACHE_METHOD');
$Configuration['Cache']['Memcached']['Store']=[getenv('MEMCACHED_SERVER')];

// Conversations
$Configuration['Conversations']['Version'] = '3.0';
$Configuration['Conversations']['Moderation']['Allow'] = true;

// EnabledApplications
$Configuration['EnabledApplications']['Conversations'] = 'conversations';
$Configuration['EnabledApplications']['Vanilla'] = 'vanilla';

// EnabledPlugins
$Configuration['EnabledPlugins']['recaptcha'] = true;
$Configuration['EnabledPlugins']['GettingStarted'] = 'GettingStarted';
$Configuration['EnabledPlugins']['stubcontent'] = false;
$Configuration['EnabledPlugins']['Topcoder'] = true;
$Configuration['EnabledPlugins']['TopcoderEditor'] = true;
$Configuration['EnabledPlugins']['Voting'] = true;
$Configuration['EnabledPlugins']['rich-editor'] = true;
$Configuration['EnabledPlugins']['editor'] = false;
$Configuration['EnabledPlugins']['emojiextender'] = true;
$Configuration['EnabledPlugins']['GooglePrettify'] = true;
$Configuration['EnabledPlugins']['Quotes'] = true;
$Configuration['EnabledPlugins']['swagger-ui'] = true;
$Configuration['EnabledPlugins']['oauth2'] = false;
$Configuration['EnabledPlugins']['Groups'] = true;
$Configuration['EnabledPlugins']['Filestack'] = true;
$Configuration['EnabledPlugins']['Sumologic'] = true;
$Configuration['EnabledPlugins']['ReplyTo'] = true;

// Feature
$Configuration['Feature']['NewFlyouts']['Enabled'] = true;

// Unfurl
$Configuration['Garden']['Title'] = 'Topcoder Forums';
$Configuration['Garden']['ShareImage'] = '/themes/topcoder/design/images/topcoder-image.png';

// Garden
$Configuration['Garden']['Logo']='/themes/topcoder/design/images/topcoder-logo.svg';
$Configuration['Garden']['SignIn']['Popup'] = false;
$Configuration['Garden']['EditContentTimeout'] = -1;
$Configuration['Garden']['Cookie']['Salt'] = 'rLpGSLgZD1AGdJ4n';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Registration']['ConfirmEmail'] = true;
$Configuration['Garden']['Email']['SupportName'] =  getenv('MAIL_FROM_NAME');
$Configuration['Garden']['Email']['Format'] = 'text';
$Configuration['Garden']['Email']['SupportAddress'] = getenv('MAIL_FROM_ADDRESS');
$Configuration['Garden']['Email']['UseSmtp'] =  getenv('MAIL_USE_SMTP');
$Configuration['Garden']['Email']['SmtpHost'] = getenv('MAIL_SMTP_HOSTNAME');
$Configuration['Garden']['Email']['SmtpUser'] =  getenv('MAIL_SMTP_USERNAME');
$Configuration['Garden']['Email']['SmtpPassword'] = getenv('MAIL_SMTP_PASSWORD');
$Configuration['Garden']['Email']['SmtpPort'] = getenv('MAIL_SMTP_PORT');
$Configuration['Garden']['Email']['SmtpSecurity'] = getenv('MAIL_SMTP_SECURITY');
$Configuration['Garden']['UpdateToken'] = '105e786dc643fd20143d3c137b593af168560c13';
$Configuration['Garden']['InputFormatter'] = 'Markdown';
$Configuration['Garden']['MobileInputFormatter'] = 'Markdown';
$Configuration['Garden']['ForceInputFormatter'] = false;
$Configuration['Garden']['Version'] = 'Undefined';
$Configuration['Garden']['CanProcessImages'] = true;
// Default Topcoder Theme
//$Configuration['Garden']['Theme'] = 'topcoder';
// MFE Topcoder Theme
$Configuration['Garden']['Theme'] = 'mfe-topcoder';
$Configuration['Garden']['MobileTheme'] = 'topcoder';
$Configuration['Garden']['Profile']['EditPhotos'] = false;
$Configuration['Garden']['SystemUserID'] = '1';
$Configuration['Garden']['AllowFileUploads'] = true;
$Configuration['Garden']['EditContentTimeout'] = -1;
$Configuration['Garden']['Profile']['EditPhotos'] = false;

// Plugins
$Configuration['Plugins']['editor']['ForceWysiwyg'] = false;
$Configuration['Plugins']['GooglePrettify']['LineNumbers'] = '';
$Configuration['Plugins']['GooglePrettify']['NoCssFile'] = '';
$Configuration['Plugins']['GooglePrettify']['UseTabby'] = '';
$Configuration['Plugins']['GooglePrettify']['Language'] = '';
$Configuration['Plugins']['GettingStarted']['Dashboard'] = '1';
$Configuration['Plugins']['GettingStarted']['Plugins'] = '1';

$Configuration['Plugins']['Topcoder']['NDA_UUID'] = getenv('VANILLA_ENV') == 'prod'?'c41e90e5-4d0e-4811-bd09-38ff72674490':'e5811a7b-43d1-407a-a064-69e5015b4900';
$Configuration['Plugins']['Topcoder']['BaseApiURL'] = getenv('TOPCODER_PLUGIN_BASE_API_URL');
$Configuration['Plugins']['Topcoder']['MemberApiURI'] = getenv('TOPCODER_PLUGIN_MEMBER_API_URI');
$Configuration['Plugins']['Topcoder']['RoleApiURI'] = getenv('TOPCODER_PLUGIN_ROLE_API_URI');
$Configuration['Plugins']['Topcoder']['ResourceRolesApiURI'] = '/v5/resource-roles';
$Configuration['Plugins']['Topcoder']['ResourcesApiURI'] = '/v5/resources';
$Configuration['Plugins']['Topcoder']['MemberProfileURL'] = getenv('TOPCODER_PLUGIN_MEMBER_PROFILE_URL');
$Configuration['Plugins']['Topcoder']['UseTopcoderAuthToken'] = getenv('TOPCODER_PLUGIN_USE_AUTH_TOKEN');


$Configuration['Plugins']['Topcoder']['ValidIssuers'] = str_replace(["[", "]", "\\", "\"", " "], '', getenv('VALID_ISSUERS'));
$Configuration['Plugins']['Topcoder']['M2M']['Auth0Audience'] = getenv('AUTH0_AUDIENCE');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0ClientId'] = getenv('AUTH0_CLIENT_ID');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0ClientSecret'] = getenv('AUTH0_CLIENT_SECRET');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0Url'] =  getenv('AUTH0_URL');
$Configuration['Plugins']['Topcoder']['M2M']['Auth0ProxyServerUrl'] = getenv('AUTH0_PROXY_SERVER_URL');
$Configuration['Plugins']['Topcoder']['SSO']['Auth0Domain'] = getenv('TOPCODER_PLUGIN_SSO_AUTH0DOMAIN');
$Configuration['Plugins']['Topcoder']['SSO']['AuthorizationURI'] = '/v3/authorizations/1';
$Configuration['Plugins']['Topcoder']['SSO']['CookieName'] = 'v3jwt';
$Configuration['Plugins']['Topcoder']['SSO']['TopcoderRS256']['ID'] =  getenv('TOPCODER_PLUGIN_SSO_TOPCODER_RS256_ID');
$Configuration['Plugins']['Topcoder']['SSO']['TopcoderHS256']['ID'] =  getenv('TOPCODER_PLUGIN_SSO_TOPCODER_HS256_ID');
$Configuration['Plugins']['Topcoder']['SSO']['TopcoderHS256']['Secret'] =  getenv('TOPCODER_HS256_SECRET');
$Configuration['Plugins']['Topcoder']['SSO']['TopcoderRS256']['UsernameClaim'] =  'nickname';
$Configuration['Plugins']['Topcoder']['SSO']['TopcoderHS256']['UsernameClaim'] =  'handle';
$Configuration['Plugins']['Topcoder']['SSO']['RefreshTokenURL' ] = getenv('TOPCODER_PLUGIN_SSO_REFRESHTOKENURL');

// Filestack
$Configuration['Plugins']['Filestack']['ApiKey'] = getenv('FILESTACK_API_KEY');

// SumoLogic
$Configuration['Plugins']['Sumologic']['HttpSourceURL'] = '';
$Configuration['Plugins']['Sumologic']['BatchSize'] = 10;

// e.g. '+15 min',  '+1 day'
$Configuration['Plugins']['Groups']['InviteExpiration']= '+20 min';

// RichEditor
$Configuration['RichEditor']['Quote']['Enable'] = true;

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
$Configuration['Routes']['DefaultController'] = 'categories';
$Configuration['Routes']['XmZpbGVzdGFjaygvLiopPyQ='] = array (
    0 => 'vanilla/filestack$1',
    1 => 'Internal',
);

// Vanilla
$Configuration['Vanilla']['SSO']['Debug'] = true;
$Configuration['Vanilla']['Activity']['ShowDiscussionBody'] = true;
$Configuration['Vanilla']['Activity']['ShowCommentBody'] = true;
// Show 'My Discussions' in the left nav
$Configuration['Vanilla']['Discussions']['ShowMineTab'] = false;
// Allow users to follow categories. Users will be able to see a feed of discussions of only their followed categories.
$Configuration['Vanilla']['EnableCategoryFollowing'] = false;
$Configuration['Vanilla']['Version'] = '3.0';


// Initial setup config

// Email Template settings
$Configuration['Garden']['Email']['Format']='html';
$Configuration['Garden']['EmailTemplate']['BackgroundColor']='#ffffff';
$Configuration['Garden']['EmailTemplate']['ButtonBackgroundColor']='transparent';
$Configuration['Garden']['EmailTemplate']['ButtonTextColor']='#865827';
$Configuration['Garden']['EmailTemplate']['Image']='https://www.dropbox.com/s/zddbsvh6f4h308o/e09141aacc790f0f31b80cc0bfd81cb9.png?dl=1';
// Email Logo size
$Configuration['Garden']['EmailTemplate']['ImageMaxWidth']='400';
$Configuration['Garden']['EmailTemplate']['ImageMaxHeight']='300';

// Profile Configuration
// Hide/Show the options in User Notification Preferences:
//    'Email.WallComment' = 'Notify me when people write on my wall.'
//    'Email.ActivityComment' = 'Notify me when people reply to my wall comments.'
//    'Popup.WallComment' = 'Notify me when people write on my wall.'
//    'Popup.ActivityComment' = 'Notify me when people reply to my wall comments.'
//    'Email.ConversationMessage' = 'Notify me of private messages.'
//    'Popup.ConversationMessage' = 'Notify me of private messages.'
$Configuration['Garden']['Profile']['ShowActivities']=false;

// Flood Control
$Configuration['Vanilla']['Comment']['SpamCount'] = '5';
$Configuration['Vanilla']['Comment']['SpamTime'] = '60';
$Configuration['Vanilla']['Comment']['SpamLock'] = '120';
$Configuration['Vanilla']['Discussion']['SpamCount'] = '3';
$Configuration['Vanilla']['Discussion']['SpamTime'] = '60';
$Configuration['Vanilla']['Discussion']['SpamLock'] = '120';
$Configuration['Vanilla']['Activity']['SpamCount'] = '5';
$Configuration['Vanilla']['Activity']['SpamTime'] = '60';
$Configuration['Vanilla']['Activity']['SpamLock'] = '120';
$Configuration['Vanilla']['ActivityComment']['SpamCount'] = '5';
$Configuration['Vanilla']['ActivityComment']['SpamTime'] = '60';
$Configuration['Vanilla']['ActivityComment']['SpamLock'] = '120';

// Posting Settings:
//    Should users be automatically pushed to the last comment they read in a discussion?
$Configuration['Vanilla']['Comments']['AutoOffset'] = false;
//    Maximum number of characters allowed in a comment
$Configuration['Vanilla']['Comment']['MaxLength'] = 16000;
//    Minimum comment length to discourage short comments
$Configuration['Vanilla']['Comment']['MinLength'] = 2;

// File handling.
$Configuration['Garden']['Upload']['MaxFileSize'] = '50M';
$Configuration['Garden']['Upload']['AllowedFileExtensions'] = [
    'txt', 'jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'ico', 'zip', 'gz', 'tar.gz', 'tgz', 'psd', 'ai', 'pdf', 'doc', 'xls', 'ppt', 'docx', 'xlsx', 'pptx', 'log', 'rar', '7z', 'xml', 'json'
];
// Allow "target='_blank'" for Markdown format;
$Configuration['Garden']['Html']['BlockedAttributes']='on*, download';

$Configuration['Garden']['FavIcon']='/themes/topcoder/design/images/favicon.png';

// This flag moves executing of ActivityModel queue to the scheduler
$Configuration['Feature']['deferredNotifications']['Enabled'] = true;

//If we allow users to dismiss discussions, skip ones this user dismissed
$Configuration['Vanilla']['Discussions']['Dismiss']=0;