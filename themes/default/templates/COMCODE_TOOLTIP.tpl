{+START,IF_NON_EMPTY,{URL}}
	<a title="{$STRIP_TAGS,{TOOLTIP}} {!LINK_NEW_WINDOW}" target="_blank" class="non-link" href="{URL*}"><span class="comcode_concept_inline" data-cms-tooltip="{ contents: '{TOOLTIP;^*}', width: '700px' }">{CONTENT}</span></a>
{+END}
{+START,IF_EMPTY,{URL}}
	<span title="{$STRIP_TAGS,{TOOLTIP}} {!LINK_NEW_WINDOW}" class="comcode-concept-inline" data-cms-tooltip="{ contents: '{TOOLTIP;^*}', width: '700px' }">{CONTENT}</span>
{+END}
