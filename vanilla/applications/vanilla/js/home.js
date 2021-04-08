jQuery(document).ready(function ($) {
  // $(".CategoryAccordion").accordion({ header: ".CategoryAccordionHeader",
  //   animate:false,
  //   collapsible: true,
  //  active: 0,  heightStyle: "content" ,
  //  icons: { "header": "icon icon-chevron-down", "activeHeader": "icon icon-chevron-up" }});

  var headers = $('.CategoryAccordion .accordion-header')

  // add the accordion functionality
  headers.click(function () {
    var panel = $(this).next()
    var isOpen = panel.is(':visible')
    if (isOpen) {
      $(panel).parent().find('.ui-accordion-header-icon').removeClass('icon-chevron-up').addClass('icon-chevron-down')
      $(panel).parent().find('.CategoryAccordionHeader').removeClass('ui-state-active')
    } else {
      $(panel).parent().find('.ui-accordion-header-icon').addClass('icon-chevron-up').removeClass('icon-chevron-down')
      $(panel).parent().find('.CategoryAccordionHeader').addClass('ui-state-active')
    }

    //panel[isOpen? 'slideUp': 'slideDown']()
    panel[isOpen ? 'hide' : 'show']().trigger(isOpen ? 'hide' : 'show')

    return false
  })

  // hook up the expand/collapse
  var hash = window.location.hash.substr(1)
  if (hash) {
    // Collapse other categories
    $('.CategoryAccordionHeader').removeClass('ui-state-active')
    $('.CategoryAccordionHeader').find('.ui-accordion-header-icon').removeClass('icon-chevron-up').addClass('icon-chevron-down')
    $('.ui-accordion-content').hide()

    $('#' + hash).find('.CategoryAccordionHeader').addClass('ui-state-active')
    $('#' + hash).find('.ui-accordion-header-icon').removeClass('icon-chevron-down').addClass('icon-chevron-up');
    $('#' + hash).find('.ui-accordion-content').show()
  } else {
    // Expand all categories
    $('.CategoryAccordionHeader').addClass('ui-state-active')
    $('.CategoryAccordionHeader').find('.ui-accordion-header-icon').removeClass('icon-chevron-down').addClass('icon-chevron-up');
    $('.ui-accordion-content').show()
  }
})
