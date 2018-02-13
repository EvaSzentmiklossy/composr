{$SET,default_zone_page_name,{$DEFAULT_ZONE_PAGE_NAME}}
<div data-view="ZoneEditorScreen" data-view-params="{+START,PARAMS_JSON,default_zone_page_name}{_*}{+END}">
	{TITLE}

	{+START,INCLUDE,HANDLE_CONFLICT_RESOLUTION}{+END}

	{+START,IF_PASSED,WARNING_DETAILS}
		{WARNING_DETAILS}
	{+END}

	<p class="vertical-alignment">
		<img width="24" height="24" src="{$IMG*,icons/help}" alt="" /> <span>{!ZE_HOW_TO_SAVE}</span>
	</p>

	<div class="float-surrounder" id="ze-panels-wrap">
		<div id="p-panel-left" class="ze-panel" data-mouseover-class="{ 'ze-panel-expanded': 1 }" data-mouseout-class="{ 'ze-panel-expanded': 0 }">
			{LEFT_EDITOR}
		</div>

		<div id="p-panel-right" class="ze-panel" data-mouseover-class="{ 'ze-panel-expanded': 1 }" data-mouseout-class="{ 'ze-panel-expanded': 0 }">
			{RIGHT_EDITOR}
		</div>

		<div class="ze-middle">
			{MIDDLE_EDITOR}
		</div>
	</div>

	<hr class="spaced-rule" />

	<form title="{!SAVE}" action="{URL*}" method="post" target="_self" autocomplete="off" class="js-form-ze-save zone-editor-form">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div id="edit-field-store" style="display: none">
		</div>

		<p class="proceed-button vertical-alignment">
			<input class="button-screen buttons--save js-btn-fetch-and-submit" type="button" value="{!SAVE}" /> <span class="associated-details">{!ZE_CLICK_TO_EDIT}</span>
		</p>
	</form>

	<p class="vertical-alignment">
		<img width="24" height="24" src="{$IMG*,icons/help}" alt="" /> <span>{!MANY_PANEL_TYPES,{$PAGE_LINK*,cms:cms_comcode_pages:_edit:lang={LANG}:page_link={ID}%3Apanel_top},{$PAGE_LINK*,cms:cms_comcode_pages:_edit:lang={LANG}:page_link={ID}%3Apanel_bottom}}</span>
	</p>
</div>
