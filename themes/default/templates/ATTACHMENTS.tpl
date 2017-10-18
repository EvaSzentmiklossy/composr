{$REQUIRE_JAVASCRIPT,core_rich_media}
{$SET,IMAGE_TYPES,{IMAGE_TYPES}}

<div data-tpl="attachments" data-tpl-params="{+START,PARAMS_JSON,ATTACHMENT_TEMPLATE,POSTING_FIELD_NAME,MAX_ATTACHMENTS,FILTER,POSTING_FIELD_NAME}{_*}{+END}">
	{+START,IF,{$AND,{TRUE_ATTACHMENT_UI},{$BROWSER_MATCHES,simplified_attachments_ui}}}
		<div id="js-attachment-store" class="accessibility_hidden">
			{$,plupload will attach upload code to here}
		</div>

		<div id="attachment_progress_bars">
			<div id="fsUploadProgress" class="progressBars"></div>
		</div>
	{+END}

	{+START,IF,{$NAND,{TRUE_ATTACHMENT_UI},{$BROWSER_MATCHES,simplified_attachments_ui}}}
		{+START,IF,{TRUE_ATTACHMENT_UI}}{+START,IF,{$ADDON_INSTALLED,filedump}}{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,filedump}}{+START,IF,{$EQ,{$ZONE},cms}}
			<p>
				{!ADD_ATTACHMENTS_MEDIA_LIBRARY,{POSTING_FIELD_NAME;*}}
			</p>
		{+END}{+END}{+END}{+END}

		<div id="js-attachment-store">
			{ATTACHMENTS}
		</div>

		{+START,IF,{TRUE_ATTACHMENT_UI}}{+START,IF_NON_EMPTY,{$_GET,id}}
			<p>
				{!comcode:DELETE_ATTACHMENTS,<a class="js-click-open-attachment-popup" rel="nofollow" title="{!comcode:ATTACHMENT_POPUP} {!LINK_NEW_WINDOW}" target="_blank" href="{$FIND_SCRIPT*,attachment_popup}?field_name={POSTING_FIELD_NAME*}{$KEEP*,0,1}">{!comcode:ATTACHMENT_POPUP}</a>}
			</p>
		{+END}{+END}
	{+END}
</div>
