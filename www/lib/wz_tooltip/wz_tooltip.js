/* This notice must be untouched at all times.
Copyright (c) 2002-2008 Walter Zorn. All rights reserved.

wz_tooltip.js	 v. 5.20

The latest version is available at
http://www.walterzorn.com
or http://www.devira.com
or http://www.walterzorn.de

Created 1.12.2002 by Walter Zorn (Web: http://www.walterzorn.com )
Last modified: 1.8.2008

Easy-to-use cross-browser tooltips.
Just include the script at the beginning of the <body> section, and invoke
Tip('Tooltip text') to show and UnTip() to hide the tooltip, from the desired
HTML eventhandlers. Example:
<a onmouseover="Tip('Some text')" onmouseout="UnTip()" href="index.htm">My home page</a>
No container DIV required.
By default, width and height of tooltips are automatically adapted to content.
Is even capable of dynamically converting arbitrary HTML elements to tooltips
by calling TagToTip('ID_of_HTML_element_to_be_converted') instead of Tip(),
which means you can put important, search-engine-relevant stuff into tooltips.
Appearance & behaviour of tooltips can be individually configured
via commands passed to Tip() or TagToTip().

Tab Width: 4
LICENSE: LGPL

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License (LGPL) as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

For more details on the GNU Lesser General Public License,
see http://www.gnu.org/copyleft/lesser.html
*/

const config = new Object();

//= ==================  GLOBAL TOOPTIP CONFIGURATION  =========================//
const tt_Debug = true; // false or true - recommended: false once you release your page to the public
const tt_Enabled = true; // Allows to (temporarily) suppress tooltips, e.g. by providing the user with a button that sets this global variable to false
const TagsToTip = true; // false or true - if true, HTML elements to be converted to tooltips via TagToTip() are automatically hidden;
// if false, you should hide those HTML elements yourself

// For each of the following config variables there exists a command, which is
// just the variablename in uppercase, to be passed to Tip() or TagToTip() to
// configure tooltips individually. Individual commands override global
// configuration. Order of commands is arbitrary.
// Example: onmouseover="Tip('Tooltip text', LEFT, true, BGCOLOR, '#FF9900', FADEIN, 400)"

config.Above = false; // false or true - tooltip above mousepointer
config.BgColor = '#E2E7FF'; // Background colour (HTML colour value, in quotes)
config.BgImg = ''; // Path to background image, none if empty string ''
config.BorderColor = '#003099';
config.BorderStyle = 'solid'; // Any permitted CSS value, but I recommend 'solid', 'dotted' or 'dashed'
config.BorderWidth = 1;
config.CenterMouse = false; // false or true - center the tip horizontally below (or above) the mousepointer
config.ClickClose = true; // false or true - close tooltip if the user clicks somewhere
config.ClickSticky = false; // false or true - make tooltip sticky if user left-clicks on the hovered element while the tooltip is active
config.CloseBtn = false; // false or true - closebutton in titlebar
config.CloseBtnColors = ['#990000', '#FFFFFF', '#DD3333', '#FFFFFF']; // [Background, text, hovered background, hovered text] - use empty strings '' to inherit title colours
config.CloseBtnText = '&nbsp;X&nbsp;'; // Close button text (may also be an image tag)
config.CopyContent = true; // When converting a HTML element to a tooltip, copy only the element's content, rather than converting the element by its own
config.Delay = 1; // Time span in ms until tooltip shows up
config.Duration = 0; // Time span in ms after which the tooltip disappears; 0 for infinite duration, < 0 for delay in ms _after_ the onmouseout until the tooltip disappears
config.FadeIn = 0; // Fade-in duration in ms, e.g. 400; 0 for no animation
config.FadeOut = 0;
config.FadeInterval = 30; // Duration of each alpha step in ms (recommended: 30) - shorter is smoother but causes more CPU-load
config.Fix = null; // Fixated position, two modes. Mode 1: x- an y-coordinates in brackets, e.g. [210, 480]. Mode 2: Show tooltip at a position related to an HTML element: [ID of HTML element, x-offset, y-offset from HTML element], e.g. ['SomeID', 10, 30]. Value null (default) for no fixated positioning.
config.FollowMouse = true; // false or true - tooltip follows the mouse
config.FontColor = '#000044';
config.FontFace = 'Verdana,Geneva,sans-serif';
config.FontSize = '8pt'; // E.g. '9pt' or '12px' - unit is mandatory
config.FontWeight = 'normal'; // 'normal' or 'bold';
config.Height = 0; // Tooltip height; 0 for automatic adaption to tooltip content, < 0 (e.g. -100) for a maximum for automatic adaption
config.JumpHorz = false; // false or true - jump horizontally to other side of mouse if tooltip would extend past clientarea boundary
config.JumpVert = true; // false or true - jump vertically		"
config.Left = false; // false or true - tooltip on the left of the mouse
config.OffsetX = 14; // Horizontal offset of left-top corner from mousepointer
config.OffsetY = 8; // Vertical offset
config.Opacity = 100; // Integer between 0 and 100 - opacity of tooltip in percent
config.Padding = 3; // Spacing between border and content
config.Shadow = false; // false or true
config.ShadowColor = '#C0C0C0';
config.ShadowWidth = 5;
config.Sticky = false; // false or true - fixate tip, ie. don't follow the mouse and don't hide on mouseout
config.TextAlign = 'left'; // 'left', 'right' or 'justify'
config.Title = ''; // Default title text applied to all tips (no default title: empty string '')
config.TitleAlign = 'left'; // 'left' or 'right' - text alignment inside the title bar
config.TitleBgColor = ''; // If empty string '', BorderColor will be used
config.TitleFontColor = '#FFFFFF'; // Color of title text - if '', BgColor (of tooltip body) will be used
config.TitleFontFace = ''; // If '' use FontFace (boldified)
config.TitleFontSize = ''; // If '' use FontSize
config.TitlePadding = 2;
config.Width = 0; // Tooltip width; 0 for automatic adaption to tooltip content; < -1 (e.g. -240) for a maximum width for that automatic adaption;
// -1: tooltip width confined to the width required for the titlebar
//= ======  END OF TOOLTIP CONFIG, DO NOT CHANGE ANYTHING BELOW  ==============//

//= ====================  PUBLIC  =============================================//
function Tip() {
  tt_Tip(arguments, null);
}
function TagToTip() {
  const t2t = tt_GetElt(arguments[0]);
  if (t2t) tt_Tip(arguments, t2t);
}
const UnTip = () => {
  tt_OpReHref();
  if (tt_aV[DURATION] < 0 && tt_iState & 0x2)
    tt_tDurt.Timer('tt_HideInit()', -tt_aV[DURATION], true);
  else if (!(tt_aV[STICKY] && tt_iState & 0x2)) tt_HideInit();
};

//= =================  PUBLIC PLUGIN API	 =====================================//
// Extension eventhandlers currently supported:
// OnLoadConfig, OnCreateContentString, OnSubDivsCreated, OnShow, OnMoveBefore,
// OnMoveAfter, OnHideInit, OnHide, OnKill

const tt_aElt = new Array(10); // Container DIV, outer title & body DIVs, inner title & body TDs, closebutton SPAN, shadow DIVs, and IFRAME to cover windowed elements in IE
var tt_aV = new Array(); // Caches and enumerates config data for currently active tooltip
let tt_sContent; // Inner tooltip text or HTML
let tt_t2t;
let tt_t2tDad; // Tag converted to tip, and its DOM parent element
let tt_scrlX = 0;
let tt_scrlY = 0;
let tt_musX;
let tt_musY;
let tt_over;
let tt_x;
let tt_y;
let tt_w;
let tt_h; // Position, width and height of currently displayed tooltip

function tt_Extension() {
  tt_ExtCmdEnum();
  tt_aExt[tt_aExt.length] = this;
  return this;
}
const tt_SetTipPos = (x, y) => {
  const css = tt_aElt[0].style;

  tt_x = x;
  tt_y = y;
  css.left = `${x}px`;
  css.top = `${y}px`;
  if (tt_ie56) {
    const ifrm = tt_aElt[tt_aElt.length - 1];
    if (ifrm) {
      ifrm.style.left = css.left;
      ifrm.style.top = css.top;
    }
  }
};
const tt_HideInit = () => {
  if (tt_iState) {
    tt_ExtCallFncs(0, 'HideInit');
    tt_iState &= ~0x4;
    if (tt_flagOpa && tt_aV[FADEOUT]) {
      tt_tFade.EndTimer();
      if (tt_opa) {
        const n = Math.round(
          tt_aV[FADEOUT] / (tt_aV[FADEINTERVAL] * (tt_aV[OPACITY] / tt_opa)),
        );
        tt_Fade(tt_opa, tt_opa, 0, n);
        return;
      }
    }
    tt_tHide.Timer('tt_Hide();', 1, false);
  }
};
const tt_Hide = () => {
  if (tt_db && tt_iState) {
    tt_OpReHref();
    if (tt_iState & 0x2) {
      tt_aElt[0].style.visibility = 'hidden';
      tt_ExtCallFncs(0, 'Hide');
    }
    tt_tShow.EndTimer();
    tt_tHide.EndTimer();
    tt_tDurt.EndTimer();
    tt_tFade.EndTimer();
    if (!tt_op && !tt_ie) {
      tt_tWaitMov.EndTimer();
      tt_bWait = false;
    }
    if (tt_aV[CLICKCLOSE] || tt_aV[CLICKSTICKY])
      tt_RemEvtFnc(document, 'mouseup', tt_OnLClick);
    tt_ExtCallFncs(0, 'Kill');
    // In case of a TagToTip tip, hide converted DOM node and
    // re-insert it into DOM
    if (tt_t2t && !tt_aV[COPYCONTENT]) tt_UnEl2Tip();
    tt_iState = 0;
    tt_over = null;
    tt_ResetMainDiv();
    if (tt_aElt[tt_aElt.length - 1])
      tt_aElt[tt_aElt.length - 1].style.display = 'none';
  }
};
const tt_GetElt = (id) =>
  document.getElementById
    ? document.getElementById(id)
    : document.all
    ? document.all[id]
    : null;
const tt_GetDivW = (el) =>
  el ? el.offsetWidth || el.style.pixelWidth || 0 : 0;
const tt_GetDivH = (el) =>
  el ? el.offsetHeight || el.style.pixelHeight || 0 : 0;
const tt_GetScrollX = () =>
  window.pageXOffset || (tt_db ? tt_db.scrollLeft || 0 : 0);
const tt_GetScrollY = () =>
  window.pageYOffset || (tt_db ? tt_db.scrollTop || 0 : 0);
const tt_GetClientW = () => {
  const de = document.documentElement;
  return de && de.clientWidth
    ? de.clientWidth
    : document.body.clientWidth || window.innerWidth || 0;
};
const tt_GetClientH = () => {
  const de = document.documentElement;
  return de && de.clientHeight
    ? de.clientHeight
    : document.body.clientHeight || window.innerHeight || 0;
};
const tt_GetEvtX = (e) =>
  e ? (typeof e.pageX !== tt_u ? e.pageX : e.clientX + tt_scrlX) : 0;
const tt_GetEvtY = (e) =>
  e ? (typeof e.pageY !== tt_u ? e.pageY : e.clientY + tt_scrlY) : 0;
const tt_AddEvtFnc = (el, sEvt, PFnc) => {
  if (el) {
    if (el.addEventListener) el.addEventListener(sEvt, PFnc, false);
    else el.attachEvent(`on${sEvt}`, PFnc);
  }
};
const tt_RemEvtFnc = (el, sEvt, PFnc) => {
  if (el) {
    if (el.removeEventListener) el.removeEventListener(sEvt, PFnc, false);
    else el.detachEvent(`on${sEvt}`, PFnc);
  }
};
const tt_GetDad = (el) => el.parentNode || el.parentElement || el.offsetParent;
const tt_MovDomNode = (el, dadFrom, dadTo) => {
  if (dadFrom) dadFrom.removeChild(el);
  if (dadTo) dadTo.appendChild(el);
};

//= =====================  PRIVATE  ===========================================//
var tt_aExt = new Array(); // Array of extension objects

let tt_db;
let tt_op;
let tt_ie;
let tt_ie56;
let tt_bBoxOld; // Browser flags
let tt_body;
let tt_ovr_; // HTML element the mouse is currently over
let tt_flagOpa; // Opacity support: 1=IE, 2=Khtml, 3=KHTML, 4=Moz, 5=W3C
let tt_maxPosX;
let tt_maxPosY;
var tt_iState = 0; // Tooltip active |= 1, shown |= 2, move with mouse |= 4
let tt_opa; // Currently applied opacity
let tt_bJmpVert;
let tt_bJmpHorz;
let // Tip temporarily on other side of mouse
  tt_elDeHref; // The tag from which we've removed the href attribute
// Timer
var tt_tShow = new Number(0);
var tt_tHide = new Number(0);
var tt_tDurt = new Number(0);
var tt_tFade = new Number(0);
var tt_tWaitMov = new Number(0);
var tt_bWait = false;
var tt_u = 'undefined';

const tt_Init = () => {
  tt_MkCmdEnum();
  // Send old browsers instantly to hell
  if (!tt_Browser() || !tt_MkMainDiv()) return;
  // Levy 06/11/2008: Important! IE doesn't fire an onscroll when a page
  // refresh is made, so we need to recalc page positions on init.
  tt_OnScrl();
  tt_IsW3cBox();
  tt_OpaSupport();
  tt_AddEvtFnc(window, 'scroll', tt_OnScrl);
  // IE doesn't fire onscroll event when switching to fullscreen;
  // fix suggested by Yoav Karpeles 14.2.2008
  tt_AddEvtFnc(window, 'resize', tt_OnScrl);
  tt_AddEvtFnc(document, 'mousemove', tt_Move);
  // In Debug mode we search for TagToTip() calls in order to notify
  // the user if they've forgotten to set the TagsToTip config flag
  if (TagsToTip || tt_Debug) tt_SetOnloadFnc();
  // Ensure the tip be hidden when the page unloads
  tt_AddEvtFnc(window, 'unload', tt_Hide);
};
// Creates command names by translating config variable names to upper case
const tt_MkCmdEnum = () => {
  let n = 0;
  for (const i in config) eval(`window.${i.toString().toUpperCase()} = ${n++}`);
  tt_aV.length = n;
};
const tt_Browser = () => {
  let n;
  let nv;
  let n6;
  let w3c;

  (n = navigator.userAgent.toLowerCase()), (nv = navigator.appVersion);
  tt_op =
    document.defaultView &&
    typeof eval('w' + 'indow' + '.' + 'o' + 'p' + 'er' + 'a') !== tt_u;
  tt_ie = n.indexOf('msie') != -1 && document.all && !tt_op;
  if (tt_ie) {
    const ieOld = !document.compatMode || document.compatMode == 'BackCompat';
    tt_db = !ieOld ? document.documentElement : document.body || null;
    if (tt_db)
      tt_ie56 =
        parseFloat(nv.substring(nv.indexOf('MSIE') + 5)) >= 5.5 &&
        typeof document.body.style.maxHeight === tt_u;
  } else {
    tt_db =
      document.documentElement ||
      document.body ||
      (document.getElementsByTagName
        ? document.getElementsByTagName('body')[0]
        : null);
    if (!tt_op) {
      n6 =
        document.defaultView &&
        typeof document.defaultView.getComputedStyle !== tt_u;
      w3c = !n6 && document.getElementById;
    }
  }
  tt_body = document.getElementsByTagName
    ? document.getElementsByTagName('body')[0]
    : document.body || null;
  if (tt_ie || n6 || tt_op || w3c) {
    if (tt_body && tt_db) {
      if (document.attachEvent || document.addEventListener) return true;
    } else
      tt_Err(
        'wz_tooltip.js must be included INSIDE the body section,' +
          ' immediately after the opening <body> tag.',
        false,
      );
  }
  tt_db = null;
  return false;
};
const tt_MkMainDiv = () => {
  // Create the tooltip DIV
  if (tt_body.insertAdjacentHTML)
    tt_body.insertAdjacentHTML('afterBegin', tt_MkMainDivHtm());
  else if (
    typeof tt_body.innerHTML !== tt_u &&
    document.createElement &&
    tt_body.appendChild
  )
    tt_body.appendChild(tt_MkMainDivDom());
  if (window.tt_GetMainDivRefs /* FireFox Alzheimer */ && tt_GetMainDivRefs())
    return true;
  tt_db = null;
  return false;
};
const tt_MkMainDivHtm = () =>
  `<div id="WzTtDiV"></div>${
    tt_ie56
      ? '<iframe id="WzTtIfRm" src="javascript:false" scrolling="no" frameborder="0" style="filter:Alpha(opacity=0);position:absolute;top:0px;left:0px;display:none;"></iframe>'
      : ''
  }`;
const tt_MkMainDivDom = () => {
  const el = document.createElement('div');
  if (el) el.id = 'WzTtDiV';
  return el;
};
const tt_GetMainDivRefs = () => {
  tt_aElt[0] = tt_GetElt('WzTtDiV');
  if (tt_ie56 && tt_aElt[0]) {
    tt_aElt[tt_aElt.length - 1] = tt_GetElt('WzTtIfRm');
    if (!tt_aElt[tt_aElt.length - 1]) tt_aElt[0] = null;
  }
  if (tt_aElt[0]) {
    const css = tt_aElt[0].style;

    css.visibility = 'hidden';
    css.position = 'absolute';
    css.overflow = 'hidden';
    return true;
  }
  return false;
};
const tt_ResetMainDiv = () => {
  tt_SetTipPos(0, 0);
  tt_aElt[0].innerHTML = '';
  tt_aElt[0].style.width = 'auto';
  tt_h = 0;
};
const tt_IsW3cBox = () => {
  const css = tt_aElt[0].style;

  css.padding = '10px';
  css.width = '40px';
  tt_bBoxOld = tt_GetDivW(tt_aElt[0]) == 40;
  css.padding = '0px';
  tt_ResetMainDiv();
};
const tt_OpaSupport = () => {
  const css = tt_body.style;

  tt_flagOpa =
    typeof css.KhtmlOpacity !== tt_u
      ? 2
      : typeof css.KHTMLOpacity !== tt_u
      ? 3
      : typeof css.MozOpacity !== tt_u
      ? 4
      : typeof css.opacity !== tt_u
      ? 5
      : typeof css.filter !== tt_u
      ? 1
      : 0;
};
// Ported from http://dean.edwards.name/weblog/2006/06/again/
// (Dean Edwards et al.)
const tt_SetOnloadFnc = () => {
  tt_AddEvtFnc(document, 'DOMContentLoaded', tt_HideSrcTags);
  tt_AddEvtFnc(window, 'load', tt_HideSrcTags);
  if (tt_body.attachEvent)
    tt_body.attachEvent('onreadystatechange', () => {
      if (tt_body.readyState == 'complete') tt_HideSrcTags();
    });
  if (/WebKit|KHTML/i.test(navigator.userAgent)) {
    var t = setInterval(() => {
      if (/loaded|complete/.test(document.readyState)) {
        clearInterval(t);
        tt_HideSrcTags();
      }
    }, 10);
  }
};
const tt_HideSrcTags = () => {
  if (!window.tt_HideSrcTags || window.tt_HideSrcTags.done) return;
  window.tt_HideSrcTags.done = true;
  if (!tt_HideSrcTagsRecurs(tt_body))
    tt_Err(
      'There are HTML elements to be converted to tooltips.\nIf you' +
        ' want these HTML elements to be automatically hidden, you' +
        ' must edit wz_tooltip.js, and set TagsToTip in the global' +
        ' tooltip configuration to true.',
      true,
    );
};
const tt_HideSrcTagsRecurs = (dad) => {
  let ovr;
  let asT2t;
  // Walk the DOM tree for tags that have an onmouseover or onclick attribute
  // containing a TagToTip('...') call.
  // (.childNodes first since .children is bugous in Safari)
  const a = dad.childNodes || dad.children || null;

  for (let i = a ? a.length : 0; i; ) {
    --i;
    if (!tt_HideSrcTagsRecurs(a[i])) return false;
    ovr = a[i].getAttribute
      ? a[i].getAttribute('onmouseover') || a[i].getAttribute('onclick')
      : typeof a[i].onmouseover === 'function'
      ? a[i].onmouseover || a[i].onclick
      : null;
    if (ovr) {
      asT2t = ovr.toString().match(/TagToTip\s*\(\s*'[^'.]+'\s*[\),]/);
      if (asT2t && asT2t.length) {
        if (!tt_HideSrcTag(asT2t[0])) return false;
      }
    }
  }
  return true;
};
const tt_HideSrcTag = (sT2t) => {
  let id;
  let el;

  // The ID passed to the found TagToTip() call identifies an HTML element
  // to be converted to a tooltip, so hide that element
  id = sT2t.replace(/.+'([^'.]+)'.+/, '$1');
  el = tt_GetElt(id);
  if (el) {
    if (tt_Debug && !TagsToTip) return false;
    el.style.display = 'none';
  } else
    tt_Err(
      `Invalid ID\n'${id}'\npassed to TagToTip().` +
        ` There exists no HTML element with that ID.`,
      true,
    );
  return true;
};
const tt_Tip = (arg, t2t) => {
  if (!tt_db) return;
  if (tt_iState) tt_Hide();
  if (!tt_Enabled) return;
  tt_t2t = t2t;
  if (!tt_ReadCmds(arg)) return;
  tt_iState = 0x1 | 0x4;
  tt_AdaptConfig1();
  tt_MkTipContent(arg);
  tt_MkTipSubDivs();
  tt_FormatTip();
  tt_bJmpVert = false;
  tt_bJmpHorz = false;
  tt_maxPosX = tt_GetClientW() + tt_scrlX - tt_w - 1;
  tt_maxPosY = tt_GetClientH() + tt_scrlY - tt_h - 1;
  tt_AdaptConfig2();
  // Ensure the tip be shown and positioned before the first onmousemove
  tt_OverInit();
  tt_ShowInit();
  tt_Move();
};
const tt_ReadCmds = (a) => {
  let i;

  // First load the global config values, to initialize also values
  // for which no command is passed
  i = 0;
  for (const j in config) tt_aV[i++] = config[j];
  // Then replace each cached config value for which a command is
  // passed (ensure the # of command args plus value args be even)
  if (a.length & 1) {
    for (i = a.length - 1; i > 0; i -= 2) tt_aV[a[i - 1]] = a[i];
    return true;
  }
  tt_Err(
    'Incorrect call of Tip() or TagToTip().\n' +
      'Each command must be followed by a value.',
    true,
  );
  return false;
};
const tt_AdaptConfig1 = () => {
  tt_ExtCallFncs(0, 'LoadConfig');
  // Inherit unspecified title formattings from body
  if (!tt_aV[TITLEBGCOLOR].length) tt_aV[TITLEBGCOLOR] = tt_aV[BORDERCOLOR];
  if (!tt_aV[TITLEFONTCOLOR].length) tt_aV[TITLEFONTCOLOR] = tt_aV[BGCOLOR];
  if (!tt_aV[TITLEFONTFACE].length) tt_aV[TITLEFONTFACE] = tt_aV[FONTFACE];
  if (!tt_aV[TITLEFONTSIZE].length) tt_aV[TITLEFONTSIZE] = tt_aV[FONTSIZE];
  if (tt_aV[CLOSEBTN]) {
    // Use title colours for non-specified closebutton colours
    if (!tt_aV[CLOSEBTNCOLORS])
      tt_aV[CLOSEBTNCOLORS] = new Array('', '', '', '');
    for (let i = 4; i; ) {
      --i;
      if (!tt_aV[CLOSEBTNCOLORS][i].length)
        tt_aV[CLOSEBTNCOLORS][i] =
          i & 1 ? tt_aV[TITLEFONTCOLOR] : tt_aV[TITLEBGCOLOR];
    }
    // Enforce titlebar be shown
    if (!tt_aV[TITLE].length) tt_aV[TITLE] = ' ';
  }
  // Circumvents broken display of images and alpha-in flicker in Geckos < 1.8
  if (
    tt_aV[OPACITY] == 100 &&
    typeof tt_aElt[0].style.MozOpacity !== tt_u &&
    !Array.every
  )
    tt_aV[OPACITY] = 99;
  // Smartly shorten the delay for alpha-in tooltips
  if (tt_aV[FADEIN] && tt_flagOpa && tt_aV[DELAY] > 100)
    tt_aV[DELAY] = Math.max(tt_aV[DELAY] - tt_aV[FADEIN], 100);
};
const tt_AdaptConfig2 = () => {
  if (tt_aV[CENTERMOUSE]) {
    tt_aV[OFFSETX] -= (tt_w - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0)) >> 1;
    tt_aV[JUMPHORZ] = false;
  }
};
// Expose content globally so extensions can modify it
const tt_MkTipContent = (a) => {
  if (tt_t2t) {
    if (tt_aV[COPYCONTENT]) tt_sContent = tt_t2t.innerHTML;
    else tt_sContent = '';
  } else tt_sContent = a[0];
  tt_ExtCallFncs(0, 'CreateContentString');
};
const tt_MkTipSubDivs = () => {
  const sCss =
    'position:relative;margin:0px;padding:0px;border-width:0px;left:0px;top:0px;line-height:normal;width:auto;';
  const sTbTrTd = ` cellspacing="0" cellpadding="0" border="0" style="${sCss}"><tbody style="${sCss}"><tr><td `;

  tt_aElt[0].innerHTML =
    `${
      tt_aV[TITLE].length
        ? `${
            '<div id="WzTiTl" style="position:relative;z-index:1;">' +
            '<table id="WzTiTlTb"'
          }${sTbTrTd}id="WzTiTlI" style="${sCss}">${tt_aV[TITLE]}</td>${
            tt_aV[CLOSEBTN]
              ? `<td align="right" style="${sCss}text-align:right;">` +
                `<span id="WzClOsE" style="position:relative;left:2px;padding-left:2px;padding-right:2px;` +
                `cursor:${
                  tt_ie ? 'hand' : 'pointer'
                };" onmouseover="tt_OnCloseBtnOver(1)" onmouseout="tt_OnCloseBtnOver(0)" onclick="tt_HideInit()">${
                  tt_aV[CLOSEBTNTEXT]
                }</span></td>`
              : ''
          }</tr></tbody></table></div>`
        : ''
    }<div id="WzBoDy" style="position:relative;z-index:0;">` +
    `<table${sTbTrTd}id="WzBoDyI" style="${sCss}">${tt_sContent}</td></tr></tbody></table></div>${
      tt_aV[SHADOW]
        ? '<div id="WzTtShDwR" style="position:absolute;overflow:hidden;"></div>' +
          '<div id="WzTtShDwB" style="position:relative;overflow:hidden;"></div>'
        : ''
    }`;
  tt_GetSubDivRefs();
  // Convert DOM node to tip
  if (tt_t2t && !tt_aV[COPYCONTENT]) tt_El2Tip();
  tt_ExtCallFncs(0, 'SubDivsCreated');
};
const tt_GetSubDivRefs = () => {
  const aId = new Array(
    'WzTiTl',
    'WzTiTlTb',
    'WzTiTlI',
    'WzClOsE',
    'WzBoDy',
    'WzBoDyI',
    'WzTtShDwB',
    'WzTtShDwR',
  );

  for (let i = aId.length; i; --i) tt_aElt[i] = tt_GetElt(aId[i - 1]);
};
const tt_FormatTip = () => {
  let css;
  let w;
  let h;
  const pad = tt_aV[PADDING];
  let padT;
  const wBrd = tt_aV[BORDERWIDTH];
  let iOffY;
  let iOffSh;
  const iAdd = (pad + wBrd) << 1;

  // --------- Title DIV ----------
  if (tt_aV[TITLE].length) {
    padT = tt_aV[TITLEPADDING];
    css = tt_aElt[1].style;
    css.background = tt_aV[TITLEBGCOLOR];
    css.paddingTop = css.paddingBottom = `${padT}px`;
    css.paddingLeft = css.paddingRight = `${padT + 2}px`;
    css = tt_aElt[3].style;
    css.color = tt_aV[TITLEFONTCOLOR];
    if (tt_aV[WIDTH] == -1) css.whiteSpace = 'nowrap';
    css.fontFamily = tt_aV[TITLEFONTFACE];
    css.fontSize = tt_aV[TITLEFONTSIZE];
    css.fontWeight = 'bold';
    css.textAlign = tt_aV[TITLEALIGN];
    // Close button DIV
    if (tt_aElt[4]) {
      css = tt_aElt[4].style;
      css.background = tt_aV[CLOSEBTNCOLORS][0];
      css.color = tt_aV[CLOSEBTNCOLORS][1];
      css.fontFamily = tt_aV[TITLEFONTFACE];
      css.fontSize = tt_aV[TITLEFONTSIZE];
      css.fontWeight = 'bold';
    }
    if (tt_aV[WIDTH] > 0) tt_w = tt_aV[WIDTH];
    else {
      tt_w = tt_GetDivW(tt_aElt[3]) + tt_GetDivW(tt_aElt[4]);
      // Some spacing between title DIV and closebutton
      if (tt_aElt[4]) tt_w += pad;
      // Restrict auto width to max width
      if (tt_aV[WIDTH] < -1 && tt_w > -tt_aV[WIDTH]) tt_w = -tt_aV[WIDTH];
    }
    // Ensure the top border of the body DIV be covered by the title DIV
    iOffY = -wBrd;
  } else {
    tt_w = 0;
    iOffY = 0;
  }

  // -------- Body DIV ------------
  css = tt_aElt[5].style;
  css.top = `${iOffY}px`;
  if (wBrd) {
    css.borderColor = tt_aV[BORDERCOLOR];
    css.borderStyle = tt_aV[BORDERSTYLE];
    css.borderWidth = `${wBrd}px`;
  }
  if (tt_aV[BGCOLOR].length) css.background = tt_aV[BGCOLOR];
  if (tt_aV[BGIMG].length) css.backgroundImage = `url(${tt_aV[BGIMG]})`;
  css.padding = `${pad}px`;
  css.textAlign = tt_aV[TEXTALIGN];
  if (tt_aV[HEIGHT]) {
    css.overflow = 'auto';
    if (tt_aV[HEIGHT] > 0) css.height = `${tt_aV[HEIGHT] + iAdd}px`;
    else tt_h = iAdd - tt_aV[HEIGHT];
  }
  // TD inside body DIV
  css = tt_aElt[6].style;
  css.color = tt_aV[FONTCOLOR];
  css.fontFamily = tt_aV[FONTFACE];
  css.fontSize = tt_aV[FONTSIZE];
  css.fontWeight = tt_aV[FONTWEIGHT];
  css.textAlign = tt_aV[TEXTALIGN];
  if (tt_aV[WIDTH] > 0) w = tt_aV[WIDTH];
  // Width like title (if existent)
  else if (tt_aV[WIDTH] == -1 && tt_w) w = tt_w;
  else {
    // Measure width of the body's inner TD, as some browsers would expand
    // the container and outer body DIV to 100%
    w = tt_GetDivW(tt_aElt[6]);
    // Restrict auto width to max width
    if (tt_aV[WIDTH] < -1 && w > -tt_aV[WIDTH]) w = -tt_aV[WIDTH];
  }
  if (w > tt_w) tt_w = w;
  tt_w += iAdd;

  // --------- Shadow DIVs ------------
  if (tt_aV[SHADOW]) {
    tt_w += tt_aV[SHADOWWIDTH];
    iOffSh = Math.floor((tt_aV[SHADOWWIDTH] * 4) / 3);
    // Bottom shadow
    css = tt_aElt[7].style;
    css.top = `${iOffY}px`;
    css.left = `${iOffSh}px`;
    css.width = `${tt_w - iOffSh - tt_aV[SHADOWWIDTH]}px`;
    css.height = `${tt_aV[SHADOWWIDTH]}px`;
    css.background = tt_aV[SHADOWCOLOR];
    // Right shadow
    css = tt_aElt[8].style;
    css.top = `${iOffSh}px`;
    css.left = `${tt_w - tt_aV[SHADOWWIDTH]}px`;
    css.width = `${tt_aV[SHADOWWIDTH]}px`;
    css.background = tt_aV[SHADOWCOLOR];
  } else iOffSh = 0;

  // -------- Container DIV -------
  tt_SetTipOpa(tt_aV[FADEIN] ? 0 : tt_aV[OPACITY]);
  tt_FixSize(iOffY, iOffSh);
};
// Fixate the size so it can't dynamically change while the tooltip is moving.
const tt_FixSize = (iOffY, iOffSh) => {
  let wIn;
  let wOut;
  let h;
  let add;
  const pad = tt_aV[PADDING];
  const wBrd = tt_aV[BORDERWIDTH];
  let i;

  tt_aElt[0].style.width = `${tt_w}px`;
  tt_aElt[0].style.pixelWidth = tt_w;
  wOut = tt_w - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0);
  // Body
  wIn = wOut;
  if (!tt_bBoxOld) wIn -= (pad + wBrd) << 1;
  tt_aElt[5].style.width = `${wIn}px`;
  // Title
  if (tt_aElt[1]) {
    wIn = wOut - ((tt_aV[TITLEPADDING] + 2) << 1);
    if (!tt_bBoxOld) wOut = wIn;
    tt_aElt[1].style.width = `${wOut}px`;
    tt_aElt[2].style.width = `${wIn}px`;
  }
  // Max height specified
  if (tt_h) {
    h = tt_GetDivH(tt_aElt[5]);
    if (h > tt_h) {
      if (!tt_bBoxOld) tt_h -= (pad + wBrd) << 1;
      tt_aElt[5].style.height = `${tt_h}px`;
    }
  }
  tt_h = tt_GetDivH(tt_aElt[0]) + iOffY;
  // Right shadow
  if (tt_aElt[8]) tt_aElt[8].style.height = `${tt_h - iOffSh}px`;
  i = tt_aElt.length - 1;
  if (tt_aElt[i]) {
    tt_aElt[i].style.width = `${tt_w}px`;
    tt_aElt[i].style.height = `${tt_h}px`;
  }
};
const tt_DeAlt = (el) => {
  let aKid;

  if (el) {
    if (el.alt) el.alt = '';
    if (el.title) el.title = '';
    aKid = el.childNodes || el.children || null;
    if (aKid) {
      for (let i = aKid.length; i; ) tt_DeAlt(aKid[--i]);
    }
  }
};
// This hack removes the native tooltips over links in Opera
const tt_OpDeHref = (el) => {
  if (!tt_op) return;
  if (tt_elDeHref) tt_OpReHref();
  while (el) {
    if (el.hasAttribute && el.hasAttribute('href')) {
      el.t_href = el.getAttribute('href');
      el.t_stats = window.status;
      el.removeAttribute('href');
      el.style.cursor = 'hand';
      tt_AddEvtFnc(el, 'mousedown', tt_OpReHref);
      window.status = el.t_href;
      tt_elDeHref = el;
      break;
    }
    el = tt_GetDad(el);
  }
};
const tt_OpReHref = () => {
  if (tt_elDeHref) {
    tt_elDeHref.setAttribute('href', tt_elDeHref.t_href);
    tt_RemEvtFnc(tt_elDeHref, 'mousedown', tt_OpReHref);
    window.status = tt_elDeHref.t_stats;
    tt_elDeHref = null;
  }
};
const tt_El2Tip = () => {
  const css = tt_t2t.style;

  // Store previous positioning
  tt_t2t.t_cp = css.position;
  tt_t2t.t_cl = css.left;
  tt_t2t.t_ct = css.top;
  tt_t2t.t_cd = css.display;
  // Store the tag's parent element so we can restore that DOM branch
  // when the tooltip is being hidden
  tt_t2tDad = tt_GetDad(tt_t2t);
  tt_MovDomNode(tt_t2t, tt_t2tDad, tt_aElt[6]);
  css.display = 'block';
  css.position = 'static';
  css.left = css.top = css.marginLeft = css.marginTop = '0px';
};
const tt_UnEl2Tip = () => {
  // Restore positioning and display
  const css = tt_t2t.style;

  css.display = tt_t2t.t_cd;
  tt_MovDomNode(tt_t2t, tt_GetDad(tt_t2t), tt_t2tDad);
  css.position = tt_t2t.t_cp;
  css.left = tt_t2t.t_cl;
  css.top = tt_t2t.t_ct;
  tt_t2tDad = null;
};
const tt_OverInit = () => {
  if (window.event) tt_over = window.event.target || window.event.srcElement;
  else tt_over = tt_ovr_;
  tt_DeAlt(tt_over);
  tt_OpDeHref(tt_over);
};
const tt_ShowInit = () => {
  tt_tShow.Timer('tt_Show()', tt_aV[DELAY], true);
  if (tt_aV[CLICKCLOSE] || tt_aV[CLICKSTICKY])
    tt_AddEvtFnc(document, 'mouseup', tt_OnLClick);
};
const tt_Show = () => {
  const css = tt_aElt[0].style;

  // Override the z-index of the topmost wz_dragdrop.js D&D item
  css.zIndex = Math.max(window.dd && dd.z ? dd.z + 2 : 0, 1010);
  if (tt_aV[STICKY] || !tt_aV[FOLLOWMOUSE]) tt_iState &= ~0x4;
  if (tt_aV[DURATION] > 0)
    tt_tDurt.Timer('tt_HideInit()', tt_aV[DURATION], true);
  tt_ExtCallFncs(0, 'Show');
  css.visibility = 'visible';
  tt_iState |= 0x2;
  if (tt_aV[FADEIN])
    tt_Fade(
      0,
      0,
      tt_aV[OPACITY],
      Math.round(tt_aV[FADEIN] / tt_aV[FADEINTERVAL]),
    );
  tt_ShowIfrm();
};
const tt_ShowIfrm = () => {
  if (tt_ie56) {
    const ifrm = tt_aElt[tt_aElt.length - 1];
    if (ifrm) {
      const css = ifrm.style;
      css.zIndex = tt_aElt[0].style.zIndex - 1;
      css.display = 'block';
    }
  }
};
const tt_Move = (e) => {
  if (e) tt_ovr_ = e.target || e.srcElement;
  e = e || window.event;
  if (e) {
    tt_musX = tt_GetEvtX(e);
    tt_musY = tt_GetEvtY(e);
  }
  if (tt_iState & 0x04) {
    // Prevent jam of mousemove events
    if (!tt_op && !tt_ie) {
      if (tt_bWait) return;
      tt_bWait = true;
      tt_tWaitMov.Timer('tt_bWait = false;', 1, true);
    }
    if (tt_aV[FIX]) {
      tt_iState &= ~0x4;
      tt_PosFix();
    } else if (!tt_ExtCallFncs(e, 'MoveBefore'))
      tt_SetTipPos(tt_Pos(0), tt_Pos(1));
    tt_ExtCallFncs([tt_musX, tt_musY], 'MoveAfter');
  }
};
const tt_Pos = (iDim) => {
  let iX;
  let bJmpMod;
  let cmdAlt;
  let cmdOff;
  let cx;
  let iMax;
  let iScrl;
  let iMus;
  let bJmp;

  // Map values according to dimension to calculate
  if (iDim) {
    bJmpMod = tt_aV[JUMPVERT];
    cmdAlt = ABOVE;
    cmdOff = OFFSETY;
    cx = tt_h;
    iMax = tt_maxPosY;
    iScrl = tt_scrlY;
    iMus = tt_musY;
    bJmp = tt_bJmpVert;
  } else {
    bJmpMod = tt_aV[JUMPHORZ];
    cmdAlt = LEFT;
    cmdOff = OFFSETX;
    cx = tt_w;
    iMax = tt_maxPosX;
    iScrl = tt_scrlX;
    iMus = tt_musX;
    bJmp = tt_bJmpHorz;
  }
  if (bJmpMod) {
    if (tt_aV[cmdAlt] && (!bJmp || tt_CalcPosAlt(iDim) >= iScrl + 16))
      iX = tt_PosAlt(iDim);
    else if (!tt_aV[cmdAlt] && bJmp && tt_CalcPosDef(iDim) > iMax - 16)
      iX = tt_PosAlt(iDim);
    else iX = tt_PosDef(iDim);
  } else {
    iX = iMus;
    if (tt_aV[cmdAlt])
      iX -= cx + tt_aV[cmdOff] - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0);
    else iX += tt_aV[cmdOff];
  }
  // Prevent tip from extending past clientarea boundary
  if (iX > iMax) iX = bJmpMod ? tt_PosAlt(iDim) : iMax;
  // In case of insufficient space on both sides, ensure the left/upper part
  // of the tip be visible
  if (iX < iScrl) iX = bJmpMod ? tt_PosDef(iDim) : iScrl;
  return iX;
};
const tt_PosDef = (iDim) => {
  if (iDim) tt_bJmpVert = tt_aV[ABOVE];
  else tt_bJmpHorz = tt_aV[LEFT];
  return tt_CalcPosDef(iDim);
};
const tt_PosAlt = (iDim) => {
  if (iDim) tt_bJmpVert = !tt_aV[ABOVE];
  else tt_bJmpHorz = !tt_aV[LEFT];
  return tt_CalcPosAlt(iDim);
};
const tt_CalcPosDef = (iDim) =>
  iDim ? tt_musY + tt_aV[OFFSETY] : tt_musX + tt_aV[OFFSETX];
const tt_CalcPosAlt = (iDim) => {
  const cmdOff = iDim ? OFFSETY : OFFSETX;
  let dx = tt_aV[cmdOff] - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0);
  if (tt_aV[cmdOff] > 0 && dx <= 0) dx = 1;
  return (iDim ? tt_musY - tt_h : tt_musX - tt_w) - dx;
};
const tt_PosFix = () => {
  let iX;
  let iY;

  if (typeof tt_aV[FIX][0] === 'number') {
    iX = tt_aV[FIX][0];
    iY = tt_aV[FIX][1];
  } else {
    if (typeof tt_aV[FIX][0] === 'string') el = tt_GetElt(tt_aV[FIX][0]);
    // First slot in array is direct reference to HTML element
    else el = tt_aV[FIX][0];
    iX = tt_aV[FIX][1];
    iY = tt_aV[FIX][2];
    // By default, vert pos is related to bottom edge of HTML element
    if (!tt_aV[ABOVE] && el) iY += tt_GetDivH(el);
    for (; el; el = el.offsetParent) {
      iX += el.offsetLeft || 0;
      iY += el.offsetTop || 0;
    }
  }
  // For a fixed tip positioned above the mouse, use the bottom edge as anchor
  // (recommended by Christophe Rebeschini, 31.1.2008)
  if (tt_aV[ABOVE]) iY -= tt_h;
  tt_SetTipPos(iX, iY);
};
const tt_Fade = (a, now, z, n) => {
  if (n) {
    now += Math.round((z - now) / n);
    if (z > a ? now >= z : now <= z) now = z;
    else
      tt_tFade.Timer(
        `tt_Fade(${a},${now},${z},${n - 1})`,
        tt_aV[FADEINTERVAL],
        true,
      );
  }
  now ? tt_SetTipOpa(now) : tt_Hide();
};
const tt_SetTipOpa = (opa) => {
  // To circumvent the opacity nesting flaws of IE, we set the opacity
  // for each sub-DIV separately, rather than for the container DIV.
  tt_SetOpa(tt_aElt[5], opa);
  if (tt_aElt[1]) tt_SetOpa(tt_aElt[1], opa);
  if (tt_aV[SHADOW]) {
    opa = Math.round(opa * 0.8);
    tt_SetOpa(tt_aElt[7], opa);
    tt_SetOpa(tt_aElt[8], opa);
  }
};
const tt_OnScrl = () => {
  tt_scrlX = tt_GetScrollX();
  tt_scrlY = tt_GetScrollY();
};
const tt_OnCloseBtnOver = (iOver) => {
  const css = tt_aElt[4].style;

  iOver <<= 1;
  css.background = tt_aV[CLOSEBTNCOLORS][iOver];
  css.color = tt_aV[CLOSEBTNCOLORS][iOver + 1];
};
const tt_OnLClick = (e) => {
  //  Ignore right-clicks
  e = e || window.event;
  if (!((e.button && e.button & 2) || (e.which && e.which == 3))) {
    if (tt_aV[CLICKSTICKY] && tt_iState & 0x4) {
      tt_aV[STICKY] = true;
      tt_iState &= ~0x4;
    } else if (tt_aV[CLICKCLOSE]) tt_HideInit();
  }
};
const tt_Int = (x) => {
  let y;

  return isNaN((y = parseInt(x))) ? 0 : y;
};
Number.prototype.Timer = function (s, iT, bUrge) {
  if (!this.value || bUrge) this.value = window.setTimeout(s, iT);
};
Number.prototype.EndTimer = function () {
  if (this.value) {
    window.clearTimeout(this.value);
    this.value = 0;
  }
};
const tt_SetOpa = (el, opa) => {
  const css = el.style;

  tt_opa = opa;
  if (tt_flagOpa == 1) {
    if (opa < 100) {
      // Hacks for bugs of IE:
      // 1.) Once a CSS filter has been applied, fonts are no longer
      // anti-aliased, so we store the previous 'non-filter' to be
      // able to restore it
      if (typeof el.filtNo === tt_u) el.filtNo = css.filter;
      // 2.) A DIV cannot be made visible in a single step if an
      // opacity < 100 has been applied while the DIV was hidden
      const bVis = css.visibility != 'hidden';
      // 3.) In IE6, applying an opacity < 100 has no effect if the
      //	   element has no layout (position, size, zoom, ...)
      css.zoom = '100%';
      if (!bVis) css.visibility = 'visible';
      css.filter = `alpha(opacity=${opa})`;
      if (!bVis) css.visibility = 'hidden';
    } else if (typeof el.filtNo !== tt_u)
      // Restore 'non-filter'
      css.filter = el.filtNo;
  } else {
    opa /= 100.0;
    switch (tt_flagOpa) {
      case 2:
        css.KhtmlOpacity = opa;
        break;
      case 3:
        css.KHTMLOpacity = opa;
        break;
      case 4:
        css.MozOpacity = opa;
        break;
      case 5:
        css.opacity = opa;
        break;
    }
  }
};
const tt_Err = (sErr, bIfDebug) => {
  if (tt_Debug || !bIfDebug) alert(`Tooltip Script Error Message:\n\n${sErr}`);
};

//= ===========  EXTENSION (PLUGIN) MANAGER  ===============//
const tt_ExtCmdEnum = () => {
  let s;

  // Add new command(s) to the commands enum
  for (const i in config) {
    s = `window.${i.toString().toUpperCase()}`;
    if (eval(`typeof(${s}) == tt_u`)) {
      eval(`${s} = ${tt_aV.length}`);
      tt_aV[tt_aV.length] = null;
    }
  }
};
const tt_ExtCallFncs = (arg, sFnc) => {
  let b = false;
  for (let i = tt_aExt.length; i; ) {
    --i;
    const fnc = tt_aExt[i][`On${sFnc}`];
    // Call the method the extension has defined for this event
    if (fnc && fnc(arg)) b = true;
  }
  return b;
};

tt_Init();
