{$REQUIRE_JAVASCRIPT,checking}
{$REQUIRE_JAVASCRIPT,newsletter}

{+START,IF_PASSED,MSG}
	<p>{MSG}</p>
{+END}

<section class="box box---block-main-newsletter-signup" data-require-javascript="['checking', 'newsletter']" data-tpl="blockMainNewsletterSignup" data-tpl-params="{+START,PARAMS_JSON,NID}{_*}{+END}"><div class="box-inner">
	<h3>{!NEWSLETTER}{$?,{$NEQ,{NEWSLETTER_TITLE},{!GENERAL}},: {NEWSLETTER_TITLE*}}</h3>

	<form class="js-form-submit-newsletter-check-email-field" title="{!NEWSLETTER}" action="{URL*}" method="post" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<p class="accessibility-hidden"><label for="baddress">{!EMAIL_ADDRESS}</label></p>

		<div>
			<input class="form-control" id="baddress" name="address{NID*}" placeholder="{!EMAIL_ADDRESS}" />
		</div>

		<p class="proceed-button">
			<button class="btn btn-primary btn-scri" type="submit">{+START,INCLUDE,ICON}NAME=menu/site_meta/newsletters{+END} {!SUBSCRIBE}</button>
		</p>
	</form>
</div></section>
