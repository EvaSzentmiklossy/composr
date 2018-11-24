{$REQUIRE_JAVASCRIPT,core_form_interfaces}

<span data-tpl="formScreenInputPassword" data-tpl-params="{+START,PARAMS_JSON,VALUE,NAME}{_*}{+END}">
	{+START,IF,{$MATCH_KEY_MATCH,:join}}
		<span style="display: none" id="password-strength-{NAME*}" class="password-strength js-mouseover-activate-password-strength-tooltip">
			<span class="password-strength-inner"></span>
		</span>
	{+END}

	<input {+START,IF,{$EQ,{NAME},edit_password}} autocomplete="off"{+START,IF,{$MOBILE}} autocorrect="off"{+END}{+END} size="27" maxlength="255" tabindex="{TABINDEX*}" class="form-control input-password{REQUIRED*} js-input-change-check-password-strength" type="password" id="{NAME*}" name="{NAME*}" value="{VALUE*}" />
</span>
