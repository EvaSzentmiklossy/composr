{$REQUIRE_JAVASCRIPT,core_form_interfaces}

<span data-tpl="formScreenInputUsername">
	<input {+START,IF,{$EQ,{NAME},edit_username}} autocomplete="off"{+START,IF,{$MOBILE}} autocorrect="off"{+END}{+END} maxlength="255" tabindex="{TABINDEX*}" class="form-control form-control-inline {+START,IF,{NEEDS_MATCH}}input-username{+END}{+START,IF,{$NOT,{NEEDS_MATCH}}}input-line{+END}{REQUIRED*} js-focus-update-ajax-member-list js-keyup-update-ajax-member-list" type="text" id="{NAME*}" name="{NAME*}" value="{DEFAULT*}" />
</span>
