{$REQUIRE_JAVASCRIPT,core_form_interfaces}
{$SET,delimiter,}
{$SET,image_sources,\{{+START,IF_PASSED,IMAGES}{+START,LOOP,IMAGES}{$GET,delimiter}"{_loop_var#/}" : "{$IMG#/,{_loop_var}}"{$SET,delimiter,\,}{+END}{+END}\}}
<select {$?,{INLINE_LIST},size="{SIZE*}"} tabindex="{TABINDEX*}" class="input-list{REQUIRED*} {$?,{INLINE_LIST},wide-field}" id="{NAME*}" name="{NAME*}" data-submit-on-enter="1" data-tpl="formScreenInputList" data-tpl-params="{+START,PARAMS_JSON,INLINE_LIST,IMAGES,NAME,image_sources}{_*}{+END}">
	{CONTENT}
</select>
