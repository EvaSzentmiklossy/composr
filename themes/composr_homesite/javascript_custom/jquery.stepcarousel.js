/*{$,Parser hint: .innerHTML okay}*/
/*{$,Parser hint: pure}*/

var stepcarousel={ajaxloadingmsg:'<div style="margin: 1em; font-weight: bold"><img src="ajaxloadr.gif" style="vertical-align: middle" /> Fetching Content. Please wait...</div>',defaultbuttonsfade:.4,configholder:{},getCSSValue:function(t){return"auto"==t?0:parseInt(t)},getremotepanels:function(t,e){e.$belt.html(this.ajaxloadingmsg),t.ajax({url:e.contenttype[1],async:!0,error:function(t){e.$belt.html("Error fetching content.<br />Server Response: "+t.responseText)},success:function(a){e.$belt.html(a),e.$panels=e.$gallery.find("."+e.panelclass),stepcarousel.alignpanels(t,e)}})},getoffset:function(t,e){return t.offsetParent?t[e]+this.getoffset(t.offsetParent,e):t[e]},getCookie:function(t){var e=new RegExp(t+"=[^;]+","i");return document.cookie.match(e)?document.cookie.match(e)[0].split("=")[1]:null},setCookie:function(t,e){document.cookie=t+"="+e},fadebuttons:function(t,e){t.$leftnavbutton.fadeTo("fast",0==e?this.defaultbuttonsfade:1),t.$rightnavbutton.fadeTo("fast",e==t.lastvisiblepanel?this.defaultbuttonsfade:1),e==t.lastvisiblepanel&&stepcarousel.stopautostep(t)},addnavbuttons:function(t,e,a){return e.$leftnavbutton=t('<img src="'+e.defaultbuttons.leftnav[0]+'">').css({zIndex:50,position:"absolute",left:e.offsets.left+e.defaultbuttons.leftnav[1]+"px",top:e.offsets.top+e.defaultbuttons.leftnav[2]+"px",cursor:"hand",cursor:"pointer"}).attr({title:"Back "+e.defaultbuttons.moveby+" panels"}).appendTo("body"),e.$rightnavbutton=t('<img src="'+e.defaultbuttons.rightnav[0]+'">').css({zIndex:50,position:"absolute",left:e.offsets.left+e.$gallery.get(0).offsetWidth+e.defaultbuttons.rightnav[1]+"px",top:e.offsets.top+e.defaultbuttons.rightnav[2]+"px",cursor:"hand",cursor:"pointer"}).attr({title:"Forward "+e.defaultbuttons.moveby+" panels"}).appendTo("body"),e.$leftnavbutton.bind("click",function(){stepcarousel.stepBy(e.galleryid,-e.defaultbuttons.moveby)}),e.$rightnavbutton.bind("click",function(){stepcarousel.stepBy(e.galleryid,e.defaultbuttons.moveby)}),0==e.panelbehavior.wraparound&&this.fadebuttons(e,a),e.$leftnavbutton.add(e.$rightnavbutton)},alignpanels:function(t,e){var a=0;e.paneloffsets=[a],e.panelwidths=[],e.$panels.each(function(s){var n=t(this);n.css({float:"none",position:"absolute",left:a+"px"}),n.bind("click",function(t){return e.onpanelclick(t.target)}),a+=stepcarousel.getCSSValue(n.css("marginRight"))+parseInt(n.get(0).offsetWidth||n.css("width")),e.paneloffsets.push(a),e.panelwidths.push(a-e.paneloffsets[e.paneloffsets.length-2])}),e.paneloffsets.pop();var s=0,n=e.$panels.length-1;e.lastvisiblepanel=n;for(var o=e.$panels.length-1;o>=0;o--)s+=o==n?e.panelwidths[n]:e.paneloffsets[o+1]-e.paneloffsets[o],e.gallerywidth>s&&(e.lastvisiblepanel=o);e.$belt.css({width:a+"px"}),e.currentpanel=e.panelbehavior.persist?parseInt(this.getCookie(e.galleryid+"persist")):0,e.currentpanel="number"==typeof e.currentpanel&&e.currentpanel<e.$panels.length?e.currentpanel:0;var l=e.paneloffsets[e.currentpanel]+(0==e.currentpanel?0:e.beltoffset);if(e.$belt.css({left:-l+"px"}),1==e.defaultbuttons.enable){var r=this.addnavbuttons(t,e,e.currentpanel);t(window).bind("load resize",function(){e.offsets={left:stepcarousel.getoffset(e.$gallery.get(0),"offsetLeft"),top:stepcarousel.getoffset(e.$gallery.get(0),"offsetTop")},e.$leftnavbutton.css({left:e.offsets.left+e.defaultbuttons.leftnav[1]+"px",top:e.offsets.top+e.defaultbuttons.leftnav[2]+"px"}),e.$rightnavbutton.css({left:e.offsets.left+e.$gallery.get(0).offsetWidth+e.defaultbuttons.rightnav[1]+"px",top:e.offsets.top+e.defaultbuttons.rightnav[2]+"px"})})}if(e.autostep&&e.autostep.enable){var i=e.$gallery.add(void 0!==r?r:null);i.bind("click",function(){e.autostep.status="stopped",stepcarousel.stopautostep(e)}),i.hover(function(){stepcarousel.stopautostep(e),e.autostep.hoverstate="over"},function(){e.steptimer&&"over"==e.autostep.hoverstate&&"stopped"!=e.autostep.status&&(e.steptimer=setInterval(function(){stepcarousel.autorotate(e.galleryid)},e.autostep.pause),e.autostep.hoverstate="out")}),e.steptimer=setInterval(function(){stepcarousel.autorotate(e.galleryid)},e.autostep.pause)}this.createpaginate(t,e),this.statusreport(e.galleryid),e.oninit(),e.onslideaction(this)},stepTo:function(t,e){var a=stepcarousel.configholder[t];if(void 0!==a){stepcarousel.stopautostep(a);var e=Math.min(e-1,a.paneloffsets.length-1),s=a.paneloffsets[e]+(0==e?0:a.beltoffset);0==a.panelbehavior.wraparound&&1==a.defaultbuttons.enable&&this.fadebuttons(a,e),a.$belt.animate({left:-s+"px"},a.panelbehavior.speed,function(){a.onslideaction(this)}),a.currentpanel=e,this.statusreport(t)}},stepBy:function(t,e,a){var s=stepcarousel.configholder[t];if(void 0!==s){a||stepcarousel.stopautostep(s);var n=e>0?"forward":"back",o=s.currentpanel+e;0==s.panelbehavior.wraparound?(o="back"==n&&o<=0?0:"forward"==n?Math.min(o,s.lastvisiblepanel):o,1==s.defaultbuttons.enable&&stepcarousel.fadebuttons(s,o)):o>s.lastvisiblepanel&&"forward"==n?o=s.currentpanel<s.lastvisiblepanel?s.lastvisiblepanel:0:o<0&&"back"==n&&(o=s.currentpanel>0?0:s.lastvisiblepanel);var l=s.paneloffsets[o]+(0==o?0:s.beltoffset);1==s.panelbehavior.wraparound&&"pushpull"==s.panelbehavior.wrapbehavior&&(0==o&&"forward"==n||0==s.currentpanel&&"back"==n)?s.$belt.animate({left:-s.paneloffsets[s.currentpanel]-("forward"==n?100:-30)+"px"},"normal",function(){s.$belt.animate({left:-l+"px"},s.panelbehavior.speed,function(){s.onslideaction(this)})}):s.$belt.animate({left:-l+"px"},s.panelbehavior.speed,function(){s.onslideaction(this)}),s.currentpanel=o,this.statusreport(t)}},autorotate:function(t){var e=stepcarousel.configholder[t];e.$belt.stop(!0,!0),this.stepBy(t,e.autostep.moveby,!0)},stopautostep:function(t){clearTimeout(t.steptimer)},statusreport:function(t){var e=stepcarousel.configholder[t];if(3==e.statusvars.length){for(var a=e.currentpanel,s=0,n=a;n<e.paneloffsets.length&&!((s+=e.panelwidths[n])>e.gallerywidth);n++);for(var o=[a+=1,n=n+1==a?a:n,e.panelwidths.length],l=0;l<e.statusvars.length;l++)window[e.statusvars[l]]=o[l],e.$statusobjs[l].text(o[l]+" ")}stepcarousel.selectpaginate(jQuery,t)},createpaginate:function(t,e){if(1==e.$paginatediv.length){var a=e.$paginatediv.find('img["data-over"]:eq(0)'),s=[],n=[],o=a.attr("data-moveby")||1,l=(1==o?0:1)+Math.floor((e.lastvisiblepanel+1)/o),r=t("<div>").append(a.clone()).html();srcs=[a.attr("src"),a.attr("data-over"),a.attr("data-select")];for(var i=0;i<l;i++){var p=Math.min(i*o,e.lastvisiblepanel);n.push(r.replace(/>$/,' data-index="'+i+'" data-moveto="'+p+'" title="Move to Panel '+(p+1)+'">')+"\n"),s.push(p)}var u=t("<span></span>").replaceAll(a).append(n.join("")).find("img");u.css({cursor:"pointer"}),e.$paginatediv.bind("click",function(a){var s=t(a.target);s.is("img")&&s.attr("data-over")&&stepcarousel.stepTo(e.galleryid,parseInt(s.attr("data-moveto"))+1)}),e.$paginatediv.bind("mouseover mouseout",function(a){var s=t(a.target);s.is("img")&&s.attr("data-over")&&parseInt(s.attr("data-index"))!=e.pageinfo.curselected&&s.attr("src",srcs["mouseover"==a.type?1:0])}),e.pageinfo={controlpoints:s,$controls:u,srcs:srcs,prevselected:null,curselected:null}}},selectpaginate:function(t,e){var a=stepcarousel.configholder[e];if(1==a.$paginatediv.length){for(var s=0;s<a.pageinfo.controlpoints.length;s++)a.pageinfo.controlpoints[s]<=a.currentpanel&&(a.pageinfo.curselected=s);null!=typeof a.pageinfo.prevselected&&a.pageinfo.$controls.eq(a.pageinfo.prevselected).attr("src",a.pageinfo.srcs[0]),a.pageinfo.$controls.eq(a.pageinfo.curselected).attr("src",a.pageinfo.srcs[2]),a.pageinfo.prevselected=a.pageinfo.curselected}},loadcontent:function(t,e){var a=stepcarousel.configholder[t];a.contenttype=["ajax",e],stepcarousel.stopautostep(a),stepcarousel.resetsettings($,a),stepcarousel.init(jQuery,a)},init:function(t,e){e.gallerywidth=e.$gallery.width(),e.offsets={left:stepcarousel.getoffset(e.$gallery.get(0),"offsetLeft"),top:stepcarousel.getoffset(e.$gallery.get(0),"offsetTop")},e.$belt=e.$gallery.find("."+e.beltclass),e.$panels=e.$gallery.find("."+e.panelclass),e.panelbehavior.wrapbehavior=e.panelbehavior.wrapbehavior||"pushpull",e.$paginatediv=t("#"+e.galleryid+"-paginate"),e.autostep&&(e.autostep.pause+=e.panelbehavior.speed),e.onpanelclick=void 0===e.onpanelclick?function(t){}:e.onpanelclick,e.onslideaction=void 0===e.onslide?function(){}:function(a){t(a).stop(),e.onslide()},e.oninit=void 0===e.oninit?function(){}:e.oninit,e.beltoffset=stepcarousel.getCSSValue(e.$belt.css("marginLeft")),e.statusvars=e.statusvars||[],e.$statusobjs=[t("#"+e.statusvars[0]),t("#"+e.statusvars[1]),t("#"+e.statusvars[2])],e.currentpanel=0,stepcarousel.configholder[e.galleryid]=e,"ajax"==e.contenttype[0]&&void 0!==e.contenttype[1]?stepcarousel.getremotepanels(t,e):stepcarousel.alignpanels(t,e)},resetsettings:function(t,e){e.$gallery.unbind(),e.$belt.stop(),e.$panels.remove(),e.$leftnavbutton&&(e.$leftnavbutton.remove(),e.$rightnavbutton.remove()),1==e.$paginatediv.length&&(e.$paginatediv.unbind(),e.pageinfo.$controls.eq(0).attr("src",e.pageinfo.srcs[0]).removeAttr("data-index").removeAttr("data-moveto").removeAttr("title").end().slice(1).remove()),e.autostep&&(e.autostep.status=null),e.panelbehavior.persist&&stepcarousel.setCookie(window[e.galleryid+"persist"],0)},setup:function(t){jQuery(document).ready(function(e){t.$gallery=e("#"+t.galleryid),stepcarousel.init(e,t)}),jQuery(window).bind("unload",function(){stepcarousel.resetsettings($,t),t.panelbehavior.persist&&stepcarousel.setCookie(t.galleryid+"persist",t.currentpanel),jQuery.each(t,function(t,e){null}),t=null})}};
