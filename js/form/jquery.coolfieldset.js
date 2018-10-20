/**
 * jQuery Plugin for creating collapsible fieldset
 * @requires jQuery 1.4 or later
 *
 * Copyright (c) 2010 Lucky <bogeyman2007@gmail.com>
 * Licensed under the GPL license:
 *   http://www.gnu.org/licenses/gpl.html
 *
 * "animation" and "speed" options are added by Mitch Kuppinger
 *
 * Fixed by Jason on Wed, 03/13/2013 - 08:35 PM
 * - Support for jQuery 1.9.1
 *
 * Fixed by SiZiOUS (@sizious) on Fri, 01/17/2014 - 10:18 AM
 * - Little fix for supporting jQuery 1.9.1, based on Jason's version
 *
 * Updated by SiZiOUS (@sizious) on Fri, 01/17/2014 - 10:55 AM
 * - Added jQuery chaining support
 * - Added an "update" event triggered on element after the operation finishes
 * - Works under IE8+, Chrome 32+, Firefox 26+, Opera 18+, Safari 5+
 */

;(function ($, window, undefined) {
  collapsedimg = "/static/app/images/collapsed.gif";
  expandedimg = "/static/app/images/expanded.gif";
  function hideFieldsetContent(obj, options) {
    if (options.animation) {
      obj.children("*:not('.hasitems > .front > .prop-name > .key')").slideUp(options.speed, function() {
        obj.trigger("update");
      });
    }
    else {
      obj.children("*:not('.hasitems > .front > .prop-name > .key')").hide();
    }
    obj.removeClass("expanded").addClass("collapsed");
    setStyle(obj, 'collapsed')

    if (!options.animation) {
      obj.trigger("update");
    }
  }

  function showFieldsetContent(obj, options) {
    if (options.animation) {
      obj.children("*:not('.hasitems > .front > .prop-name > .key')").slideDown(options.speed, function() {
        parentcolor = obj.css('backgroundColor')
        objclass = obj.hasClass('array') ? 'fieldset.array' : 'fieldset.object';
        if (parentcolor == options.color1) {
          childcolor = options.color2
        } else {
          childcolor = options.color1
        }
        if (obj.hasClass('array')) {
          obj.first().children().find('fieldset').css('backgroundColor',childcolor)
        } else {
          obj.find('fieldset').css('backgroundColor',childcolor)
        }
        
        obj.trigger("update");
      });
    }
    else {
      obj.children("*:not('.hasitems > .front > .prop-name > .key')").show();
    }

    obj.removeClass("collapsed").addClass("expanded");
    setStyle(obj, 'expanded')
    
    if (!options.animation) {
      obj.trigger("update");
    }
  }
  
  function setStyle(obj, state) {
    if (state=='expanded') {
      img = expandedimg
    } else {
      img = collapsedimg
    }
    obj.prev().css('background-image', 'url('+img+')');
    obj.prev().css('background-position', '95% center');
    obj.prev().css('background-repeat', 'no-repeat');
    obj.prev().css('background-size', '10%');    
    obj.prev().css('cursor', 'pointer');
    obj.children().first().css('padding-top', '20px')
  }

  function doToggle(fieldset, setting) {
    if (fieldset.hasClass('collapsed')) {
      showFieldsetContent(fieldset, setting);
    }
    else if (fieldset.hasClass('expanded')) {
      hideFieldsetContent(fieldset, setting);
    }
  }

  $.fn.coolfieldset = function (options) {
    var setting = { collapsed: false, animation: true, speed: 'fast', color1: 'rgb(255, 255, 255)', color2: 'rgb(248, 248, 248)' };
    $.extend(setting, options);
    
    var tab = $('div.active').attr('id')
    //set parent color
    $('label.'+tab).next().css('backgroundColor', setting.color1)
    //alert($('label.'+tab).html())
    
    return this.each(function () {
      var fieldset = $(this);
      //var legend = fieldset.children('legend');
      var legend = fieldset.parent().prev()

      if (setting.collapsed) {
        if ( !legend.hasClass(tab) )
          hideFieldsetContent(fieldset, { animation: false });
      }
      else {
        fieldset.addClass("expanded");
      }

      legend.bind("click", function () { doToggle(fieldset, setting) });

      return fieldset;
    });
  }
})(jQuery, window);
