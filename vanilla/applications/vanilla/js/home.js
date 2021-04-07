jQuery(document).ready(function($) {
  $(".CategoryAccordion").accordion({ header: ".CategoryAccordionHeader",
    animate:false,
    collapsible: true,
    active: 0,  heightStyle: "content" ,
    icons: { "header": "icon icon-chevron-down", "activeHeader": "icon icon-chevron-up" }});

 });
