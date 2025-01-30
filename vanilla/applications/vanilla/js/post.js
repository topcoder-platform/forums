jQuery(document).ready(function($) {
    function isValidInternalRedirect(url) {
        try {
            const target = new URL(url, window.location.origin);
            return target.origin === window.location.origin; // Ensure same domain
        } catch (e) {
            return false; // Invalid URL
        }
    }

    // Reveal the textarea and hide previews.
    $(document).on('click', 'a.WriteButton', function() {
        if ($(this).hasClass('WriteButton')) {
            var frm = $(this).parents('.MessageForm').find('form');
            frm.trigger('WriteButtonClick', [frm]);

            // Reveal the "Preview" button and hide this one
            $(this).parents('.DiscussionForm').find('.PreviewButton').show();
            $(this).addClass('Hidden');
        }
        resetDiscussionForm(this);
        return false;
    });

    function resetDiscussionForm(sender) {
        var parent = $(sender).parents('.DiscussionForm, .EditDiscussionForm');
        $(parent).find('.Preview').remove();
        $(parent).find('.PreviewTitle').remove();
        $(parent).find('h1.H').show();
        $(parent).find('.bodybox-wrap .TextBoxWrapper,.P label[for=Form_Name], #Form_Name').show();
    }

    // Hijack comment form button clicks
    $('#CommentForm :submit').click(function() {
        var btn = this;
        var frm = $(btn).parents('form').get(0);

        // Handler before submitting
        $(frm).triggerHandler('BeforeCommentSubmit', [frm, btn]);

        var textbox = $(frm).find('textarea');
        var inpCommentID = $(frm).find('input:hidden[name$=CommentID]');
        var inpDraftID = $(frm).find('input:hidden[name$=DraftID]');
        var preview = $(btn).attr('name') == $('#Form_Preview').attr('name') ? true : false;
        var draft = $(btn).attr('name') == $('#Form_SaveDraft').attr('name') ? true : false;
        var postValues = $(frm).serialize();
        postValues += '&DeliveryType=VIEW&DeliveryMethod=JSON'; // DELIVERY_TYPE_VIEW
        postValues += '&' + btn.name + '=' + btn.value;
        var discussionID = $(frm).find('[name$=DiscussionID]').val();
        var action = $(frm).attr('action') + '/' + discussionID;
        gdn.disable(btn);

        $.ajax({
            type: "POST",
            url: action,
            data: postValues,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                // Remove any old popups
                $('div.Popup').remove();
                // Add new popup with error
                $.popup({}, XMLHttpRequest.responseText);
            },
            success: function(json) {
                // Remove any old popups if not saving as a draft
                if (!draft)
                    $('div.Popup').remove();

                // Assign the comment id to the form if it was defined
                if (json.CommentID != null && json.CommentID != '') {
                    $(inpCommentID).val(json.CommentID);
                    gdn.definition('LastCommentID', json.CommentID, true);
                }

                if (json.DraftID != null && json.DraftID != '')
                    $(inpDraftID).val(json.DraftID);

                // Remove any old errors from the form
                $(frm).find('div.Errors').remove();

                if (json.FormSaved == false) {
                    $(frm).prepend(json.ErrorMessages);
                    json.ErrorMessages = null;
                } else if (preview) {
                    // Pop up the new preview.
                    $.popup({}, json.Data);
                } else if (!draft && json.DiscussionUrl != null) {
                    $(frm).triggerHandler('complete');
                    // Secure redirect to the discussion
                    if (isValidInternalRedirect(json.DiscussionUrl)) {
                        document.location = json.DiscussionUrl;
                    } else {
                        console.error('Blocked potential open redirect:', json.DiscussionUrl);
                    }
                }
                gdn.inform(json);
            },
            complete: function(XMLHttpRequest, textStatus) {
                gdn.enable(btn);
            }
        });
        $(frm).triggerHandler('submit');
        return false;
    });

    // Hijack discussion form button clicks
    //$('#DiscussionForm :submit').live('click', function() {

    // Jan28, 2014 jQuery upgrade to 1.10.2, as live() removed in 1.7.
    $(document).on('click', '#DiscussionForm :submit', function() {
        var btn = this;
        var frm = $(btn).parents('form').get(0);

        // Handler before submitting
        $(frm).triggerHandler('BeforeDiscussionSubmit', [frm, btn]);
        var maxCommentLength =  $(frm).find('input:hidden[name$=MaxCommentLength]');
        var defaultValues = [
            undefined,
            null,
            '',
            '[{\"insert\":\"\\n\"}]'
        ];

        var editorContainer = $(frm).find('.EasyMDEContainer');
        var messageContainer = $(frm).find('.editor-statusbar .message');
        var textbox = $(frm).find('textarea#Form_Body');
        var currentVal = $(textbox).val();
        currentVal = gdn.normalizeText(currentVal);
        if(defaultValues.includes(currentVal) || currentVal.trim().length == 0) {
            $(editorContainer).addClass('error');
            $(messageContainer).text('Cannot post an empty message');
            $(frm).find(':submit').attr('disabled', 'disabled');
            $(frm).find('.Buttons a.Button:not(.Cancel)').addClass('Disabled');
            return false;
        }

        if(currentVal.length > maxCommentLength.val()) {
            $(editorContainer).addClass('error');
            var count = currentVal.length - maxCommentLength.val();
            $(messageContainer).text('Discussion is '+ count +' characters too long');
            $(frm).find(':submit').attr('disabled', 'disabled');
            $(frm).find('.Buttons a.Button:not(.Cancel)').addClass('Disabled');
            return false;
        }

        $(editorContainer).removeClass('error');
        $(messageContainer).text('');
        $(frm).find(':submit').removeAttr("disabled");
        $(frm).find('.Buttons a.Button').removeClass('Disabled');


        var inpDiscussionID = $(frm).find(':hidden[name$=DiscussionID]');
        var inpDraftID = $(frm).find(':hidden[name$=DraftID]');
        var preview = $(btn).attr('name') == $('#Form_Preview').attr('name') ? true : false;
        var draft = $(btn).attr('name') == $('#Form_SaveDraft').attr('name') ? true : false;
        var postValues = $(frm).serialize();
        postValues += '&DeliveryType=VIEW&DeliveryMethod=JSON'; // DELIVERY_TYPE_VIEW
        postValues += '&' + btn.name + '=' + btn.value;
        gdn.disable(btn);

        $.ajax({
            type: "POST",
            url: $(frm).attr('action'),
            data: postValues,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('div.Popup').remove();
                $.popup({}, XMLHttpRequest.responseText);
            },
            success: function(json) {
                // Remove any old popups if not saving as a draft
                if (!draft)
                    $('div.Popup').remove();

                // Assign the discussion id to the form if it was defined
                if (json.DiscussionID != null)
                    $(inpDiscussionID).val(json.DiscussionID);

                if (json.DraftID != null)
                    $(inpDraftID).val(json.DraftID);

                // Remove any old errors from the form
                $(frm).find('div.Errors').remove();

                if (json.MyDrafts != null && json.CountDrafts != null) {
                    var countMyDraftsHtml = '<span aria-hidden="true" class="Sprite SpMyDrafts"></span> My Drafts';
                    if(json.CountDrafts > 0) {
                        countMyDraftsHtml += '<span class="Aside"><span class="Count">' + json.CountDrafts + '</span></span>';
                        $('li#MyDrafts').removeClass('hidden');
                    } else {
                        $('li#MyDrafts').addClass('hidden');
                    }
                    $('li#MyDrafts a').html(countMyDraftsHtml);
                }

                if (json.FormSaved == false) {
                    $(frm).prepend(json.ErrorMessages);
                    json.ErrorMessages = null;
                } else if (preview) {
                    // Reveal the "Edit" button and hide this one
                    $(btn).hide();
                    $(frm).find('.WriteButton').removeClass('Hidden');
                    $(frm).find('.P label[for=Form_Name], #Form_Name').hide();
                    $(frm).find('.bodybox-wrap .TextBoxWrapper').hide().after(json.Data);
                    $(frm).trigger('PreviewLoaded', [frm]);
                } else if (!draft) {
                    if (json.RedirectTo) {
                        $(frm).triggerHandler('complete');
                        // Redirect to the new discussion
                        // Secure redirect to the new discussion
                        if (isValidInternalRedirect(json.RedirectTo)) {
                            document.location = json.RedirectTo;
                        } else {
                            console.error('Blocked potential open redirect:', json.RedirectTo);
                        }
                    } else {
                        var contentContainer = $("#Content");

                        if (contentContainer.length === 1) {
                            contentContainer.html(json.Data);
                        } else {
                            // Hack to emulate a content container.
                            contentContainer = $(document.createElement("div"));
                            contentContainer.html(json.Data);
                            $(frm).replaceWith(contentContainer);
                        }
                    }
                }
                gdn.inform(json);
            },
            complete: function(XMLHttpRequest, textStatus) {
                gdn.enable(btn);
            }
        });
        $(frm).triggerHandler('submit');
        return false;
    });

    // Autosave
    if ($.fn.autosave) {
        var btn = $('#Form_SaveDraft');

        $('#CommentForm textarea').autosave({
            button: btn
        });
        $('#DiscussionForm textarea').autosave({
            button: btn
        });
    }

    $(document).on('PreviewLoaded', function(ev, form, ) {
        var previewContainer = $(form).find('.Preview');
        var discussionTitle = $(form).find('#Form_Name').val();
        $(previewContainer).prepend('<div class="Title">'+discussionTitle+'</div>');
        var title = $(form).closest('.FormTitleWrapper').find('h1');
        var currentTitle = $(title).text();
        var previewTitle = $(title).clone();
        $(previewTitle).text(currentTitle + ' (Preview)');
        $(previewTitle).addClass('PreviewTitle');
        $(title).after($(previewTitle).prop('outerHTML'));
        $(title).hide();
        return false;
    });
});
