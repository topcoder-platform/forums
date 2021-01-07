<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    {literal}
    <style type="text/css">
    .notification {
        margin: 0 0;
        padding: 0 0;
        font-size: 10pt;
        font-weight: normal;
        font-style: normal;
        font-family: "\48\65\6C\76\65\74\69\63\61", "\41\72\69\61", sans-serif;
    }
    .notification hr {
        border-top: 1px dashed #8c8b8b;
        border-bottom: 1px dashed #fff;
    }

    .notification table td {
        border-collapse: collapse;
    }

    .notification td {
        margin-top: 0px;
        margin-right: 0px;
        margin-bottom: 0px;
        margin-left: 0px;
    }

    .notification td img {
        display: block;
    }

    .notification a {
        font-size: 10pt;
        text-decoration: underline;
    }

    .notification a img {
        text-decoration: none;
    }

    .notification h1, h2, h3, h4, h5, h6 {
        font-weight: normal;
        font-style: normal;
        font-family: "\48\65\6C\76\65\74\69\63\61", "\41\72\69\61", sans-serif;
        margin: 2px 0;
        padding: 4px 0;
    }

    .notification h1 {
        font-size: 14pt;
        font-weight: 700;
    }

    .notification h2 {
        font-size: 12pt;
        font-weight: 700;
    }

    .notification h3 {
        font-size: 10pt;
        font-weight: 600;
    }

    .notification h4 {
        font-size: 10pt;
        font-weight: 500;
    }

    .notification h5 {
        font-size: 10pt;
        font-weight: 400;
    }

    .notification h6 {
        font-size: 10pt;
        font-weight: 300;
    }

    .notification blockquote {
        padding: 1ex 16px;
        margin: 1em 0;
        background: #f3f3f3;
        background: rgba(0, 0, 0, 0.05);
        border-left: 4px solid #eee;
        border-left: 4px solid rgba(0, 0, 0, 0.1);
        min-width: 200px;
        overflow-y: initial;
    }
    .notification code {
        padding: 5px 20px;
        display: block;
        background-color: #f7f7f7;
    }

    .notification p, span {
        padding: 0 0;
        margin: 0;
    }
    .footer a {
        text-decoration: underline;
    }
    </style>
    {/literal}
</head>
<body style="margin: 0; padding: 0; background-color: {$email.backgroundColor} !important;  color: {$email.textColor};">
<center>
    <div class="notification" style="max-width: 600px;color: {$email.textColor};background-color: {$email.containerBackgroundColor}">
        <!--[if (gte mso 9)|(IE)]>
        <table width="600" align="center" cellpadding="0" cellspacing="0" border="0" style="color: {$email.textColor};background-color: {$email.containerBackgroundColor}">
            <tr>
                <td>
        <![endif]-->
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" style="margin: auto;color: {$email.textColor};background-color: {$email.containerBackgroundColor}">
                        <tbody>
                        <tr>
                            <td align="left" style="padding-bottom:15px;">
                                {if $email.image}
                                    <a href="https://www.topcoder.com/" rel=" noopener noreferrer"
                                       target="_blank">
                                        {if $email.image.link != ''}
                                            <img alt="Topcoder"
                                                 border="0"
                                                 src="{$email.image.source}"
                                                 style="width:120px">
                                        {/if}
                                    </a>
                                {/if}
                            </td>
                            <td align="right">
                            </td>
                        </tr>
                        </tbody>
                    </table>
        <!--[if (gte mso 9)|(IE)]>
                </td>
            </tr>
        </table>
        <![endif]-->
        <!--[if (gte mso 9)|(IE)]>
        <table width="600" align="center" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
        <![endif]-->
                <table class="content" width="100%" border="0" cellpadding="5" cellspacing="0" style="margin: auto;color: {$email.textColor};background-color: {$email.containerBackgroundColor}">
                <tbody>
                <tr>
                    <td>
                        {if $email.title}<h1 style='color: {$email.textColor};'>{$email.title}</h1>{/if}
                        {if $email.lead}<p style='color: {$email.textColor}'>{$email.lead}</p>{/if}
                        <p class="message" style='margin: 0;Margin-bottom: 10px;padding: 0;color: {$email.textColor};text-align: left;margin-top: 10px;
                                margin-bottom: 15px'>{$email.message}</p>
                        {if $email.button}
                            <div style="margin: 0;padding: 0; text-align: center">
                                <a class="button" href="{$email.button.url}" style="margin: 0;padding: 0;color: {$email.button.textColor};
                                        cursor: pointer;display: inline-block;" rel=" noopener noreferrer" target="_blank">{$email.button.text}</a>
                            </div>
                        {/if}

                    </td>
                </tr>
                </tbody>
            </table>
        <!--[if (gte mso 9)|(IE)]>
                </td>
            </tr>
        </table>
        <![endif]-->

        <!--[if (gte mso 9)|(IE)]>
        <table width="600" align="center" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
        <![endif]-->
            <table class="footer"  border="0" cellpadding="5" cellspacing="0"
                   width="600"  style="margin: auto;color: {$email.textColor};background-color: {$email.containerBackgroundColor}">
                <tbody>
                <tr>
                    <td align="center" colspan="2"
                        style="padding-top:7px;padding-bottom:7px;border-top: 3px solid #777;">
                    <span face="'Lucida Grande',Verdana,Arial,sans-serif"
                          style="font-size: 12px;line-height: 14px;">
                        Compete:&nbsp;&nbsp;
                        <a href="https://www.topcoder.com/challenges" rel=" noopener noreferrer" style="color: {$email.button.textColor};"
                           target="_blank">All Challenges</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="https://arena.topcoder.com/" rel=" noopener noreferrer" style="color: {$email.button.textColor};"
                           target="_blank">Competitive Programming</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="https://www.topcoder.com/gigs" rel=" noopener noreferrer" style="color: {$email.button.textColor};"
                           target="_blank" >Gig Work</a>
			        </span>
                    </td>
                </tr>
                <td align="center" colspan="2"
                    style="padding-top:7px;padding-bottom:7px;border-bottom: 1px dotted #777;">
                    <span face="'Lucida Grande',Verdana,Arial,sans-serif" style="font-size: 12px;line-height: 14px;">
                    <a href="https://www.topcoder.com/" rel=" noopener noreferrer" target="_blank" style="color: {$email.button.textColor};">Topcoder</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="https://www.topcoder.com/community/contact" rel=" noopener noreferrer"
                       target="_blank" style="color: {$email.button.textColor};">Support</a>
			    </span>
                </td>
                </tbody>
        </table>
        <!--[if (gte mso 9)|(IE)]>
        </td>
        </tr>
        </table>
        <![endif]-->
    </div>
</center>
</body>
</html>
