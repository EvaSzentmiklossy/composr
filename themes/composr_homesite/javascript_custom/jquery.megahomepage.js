/*{$,Parser hint: .innerHTML okay}*/
/*{$,Parser hint: pure}*/

!function(t){var e={current:0,delay:5e3,speed:500,next:function(){this.current<this.quotes.length-1?this.current++:this.current=0,this.quotes.not("li:eq("+this.current+")").fadeOut(this.speed),this.quotes.filter("li:eq("+this.current+")").fadeIn(this.speed)},init:function(){if(this.context=t("#testimonial_scroller"),this.context.length<1)return!1;this.quotes=this.context.find("ul.quotes li"),this.quotes.each(function(){var e=t(this).find("blockquote"),i=e.outerHeight();e.css({position:"absolute",top:"50%",marginTop:-Math.ceil(i/2+2)})}),this.quotes.not("li:eq(0)").hide(),setInterval(function(){e.next()},this.delay)}},i={init:function(){t("a.scrollto").length&&t("a.scrollto").click(function(e){e.preventDefault();var i=t(this).attr("href");t.scrollTo(t(i),500,{offset:{top:0}})})}},n={init:function(){t("#oob_slidedeck_frame a.view-demo").length&&t("#oob_slidedeck_frame a.view-demo").fancybox({type:"iframe",padding:0,width:820,height:600,scrolling:"no"})}},o={init:function(){t("#sticky_nav_wrapper").length&&(t("#sticky_nav_map_link").click(function(t){t.preventDefault()}).mouseenter(function(){t("#sticky_nav").animate({left:0},150)}),t("#sticky_nav").mouseleave(function(){t("#sticky_nav").animate({left:-200},150)}))}};t(document).ready(function(){e.init(),i.init(),n.init(),o.init(),t("a.fancy-image").length&&t("a.fancy-image").fancybox({padding:0,overlayOpacity:.7,overlayColor:"#777"})})}(jQuery);
