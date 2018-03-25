<aside class="screen-actions-outer box" data-tpl="blockMainScreenActions" data-tpl-params="{+START,PARAMS_JSON,EASY_SELF_URL}{_*}{+END}"><nav class="screen-actions box-inner">
	<div class="print"><a class="link-exempt js-click-print-screen" rel="print nofollow" target="_blank" title="{!PRINT_THIS_SCREEN} {!LINK_NEW_WINDOW}" href="{PRINT_URL*}"><span>{!PRINT_THIS_SCREEN}</span></a></div>
	<div class="recommend"><a data-open-as-overlay="{}" class="link-exempt" rel="nofollow" target="_blank" title="{!RECOMMEND_LINK} {!LINK_NEW_WINDOW}" href="{RECOMMEND_URL*}"><span>{!RECOMMEND_LINK}</span></a></div>
	<div class="facebook"><a class="link-exempt js-click-add-to-facebook" target="_blank" title="{!ADD_TO_FACEBOOK} {!LINK_NEW_WINDOW}" href="http://www.facebook.com/sharer.php?u={EASY_SELF_URL.*}"><span>{!ADD_TO_FACEBOOK}</span></a></div>
	<div class="twitter"><a class="link-exempt js-click-action-add-to-twitter" target="_blank" title="{!ADD_TO_TWITTER} {!LINK_NEW_WINDOW}" href="http://twitter.com/home?status={TITLE*}%20{EASY_SELF_URL.*}"><span>{!ADD_TO_TWITTER}</span></a></div>
	<div class="stumbleupon"><a class="link-exempt js-click-add-to-stumbleupon" target="_blank" title="{!ADD_TO_STUMBLEUPON} {!LINK_NEW_WINDOW}" href="http://www.stumbleupon.com/submit?url={EASY_SELF_URL.*}"><span>{!ADD_TO_STUMBLEUPON}</span></a></div>
	<div class="linkedin"><a class="link-exempt js-click-add-to-linkedin" target="_blank" title="{!ADD_TO_LINKEDIN} {!LINK_NEW_WINDOW}" href="https://www.linkedin.com/shareArticle?url={EASY_SELF_URL.*}&amp;title={$METADATA.*,title}&amp;mini=true"><span>{!ADD_TO_LINKEDIN}</span></a></div>
	{+START,IF_NON_EMPTY,{$METADATA,image}}
		<div class="pinterest"><a class="link-exempt js-click-add-to-pinterest" target="_blank" title="{!ADD_TO_PINTEREST} {!LINK_NEW_WINDOW}" href="https://pinterest.com/pin/create/button/?url={EASY_SELF_URL.*}&amp;media={$METADATA.*,image}&amp;description={$METADATA.*,title}"><span>{!ADD_TO_PINTEREST}</span></a></div>
	{+END}
	<div class="tumblr"><a class="link-exempt js-click-add-to-tumblr" target="_blank" title="{!ADD_TO_TUMBLR} {!LINK_NEW_WINDOW}" href="http://tumblr.com/widgets/share/tool?canonicalUrl={EASY_SELF_URL*}&amp;title={$METADATA.*,title}"><span>{!ADD_TO_TUMBLR}</span></a></div>
	<div class="vk"><a class="link-exempt js-click-add-to-vk" target="_blank" title="{!ADD_TO_VK} {!LINK_NEW_WINDOW}" href="https://vk.com/share.php?url={EASY_SELF_URL.*}"><span>{!ADD_TO_VK}</span></a></div>
	<div class="sina-weibo"><a class="link-exempt js-click-add-to-sina-weibo" target="_blank" title="{!ADD_TO_SINA_WEIBO} {!LINK_NEW_WINDOW}" href="http://service.weibo.com/share/share.php?url={EASY_SELF_URL.*}&amp;title={$METADATA.*,title}"><span>{!ADD_TO_SINA_WEIBO}</span></a></div>
	<div class="tencent-weibo"><a class="link-exempt js-click-add-to-tencent-weibo" target="_blank" title="{!ADD_TO_TENCENT_WEIBO} {!LINK_NEW_WINDOW}" href="http://v.t.qq.com/share/share.php?url={EASY_SELF_URL.*}&amp;title={$METADATA.*,title}"><span>{!ADD_TO_TENCENT_WEIBO}</span></a></div>
	<div class="qzone"><a class="link-exempt js-click-add-to-qzone" target="_blank" title="{!ADD_TO_QZONE} {!LINK_NEW_WINDOW}" href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url={EASY_SELF_URL.*}&amp;title={$METADATA.*,title}"><span>{!ADD_TO_QZONE}</span></a></div>

	<div class="google-plusone">
		<div class="g-plusone" data-size="medium" data-count="true" data-href="{EASY_SELF_URL*}" data-callback="$cms.googlePlusTrack"></div>
		{$EXTRA_FOOT,<script {$CSP_NONCE_HTML} defer="defer" src="https://apis.google.com/js/plusone.js"></script>}
	</div>
</nav></aside>
