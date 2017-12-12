{$REQUIRE_JAVASCRIPT,search}

<section class="box box___block_main_search" data-view="BlockMainSearch" data-view-params="{+START,PARAMS_JSON,SEARCH_TYPE}{_*}{+END}"><div class="box-inner">
	<h3>{TITLE*}</h3>

	<form role="search" title="{TITLE*}" class="js-form-submit-main-search" action="{$URL_FOR_GET_FORM*,{URL}}" method="get" autocomplete="off">
		{$HIDDENS_FOR_GET_FORM,{URL},content}

		<div>
			{+START,IF,{$EQ,{INPUT_FIELDS},1}}
				<div>
					<label class="accessibility_hidden" for="main_search_content">{!SEARCH}</label>
					<input {+START,IF,{$MOBILE}} autocorrect="off"{+END} autocomplete="off" maxlength="255" class="wide_field js-keyup-update-ajax-search-list-with-type" type="search" id="main_search_content" name="content" />
				</div>
			{+END}
			{+START,IF,{$NEQ,{INPUT_FIELDS},1}}
				{+START,LOOP,INPUT_FIELDS}
					{+START,IF_EMPTY,{INPUT}}
						<div class="search_option float-surrounder">
							<label for="search_{_loop_key*}">{LABEL*}:</label><br />
							{+START,IF,{$EQ,{_loop_key},content}}
								<input {+START,IF,{$MOBILE}} autocorrect="off"{+END} autocomplete="off" maxlength="255" class="js-keyup-update-ajax-search-list" type="text" id="search_{_loop_key*}" name="content" value="{$_GET*,content}" />
							{+END}
							{+START,IF,{$NEQ,{_loop_key},content}}
								<input {+START,IF,{$MOBILE}} autocorrect="off"{+END} autocomplete="off" maxlength="255" type="text" id="search_{_loop_key*}" name="option_{_loop_key*}" value="{$_GET*,option_{_loop_key}}" />
							{+END}
						</div>
					{+END}
					{+START,IF_NON_EMPTY,{INPUT}}
						{INPUT}
					{+END}
				{+END}
			{+END}

			<p class="proceed_button">
				<input class="button_screen_item buttons--search" type="submit" value="{!SEARCH}" />
			</p>

			{+START,LOOP,LIMIT_TO}
				<input type="hidden" name="{_loop_var*}" value="1" />
			{+END}
			{+START,LOOP,EXTRA}
				<input type="hidden" name="{_loop_key*}" value="{_loop_var*}" />
			{+END}
			<input type="hidden" name="author" value="{AUTHOR*}" />
			<input type="hidden" name="days" value="{DAYS*}" />
			<input type="hidden" name="sort" value="{SORT*}" />
			<input type="hidden" name="direction" value="{DIRECTION*}" />
			<input type="hidden" name="only_titles" value="{ONLY_TITLES*}" />
			<input type="hidden" name="only_search_meta" value="{ONLY_SEARCH_META*}" />
			<input type="hidden" name="boolean_search" value="{BOOLEAN_SEARCH*}" />
			<input type="hidden" name="conjunctive_operator" value="{CONJUNCTIVE_OPERATOR*}" />
		</div>
	</form>

	{+START,IF_NON_EMPTY,{FULL_SEARCH_URL}}
		<ul class="horizontal_links associated-links-block-group">
			<li><a href="{FULL_SEARCH_URL*}" title="{!MORE_OPTIONS}: {!SEARCH_TITLE}">{!MORE_OPTIONS}</a></li>
		</ul>
	{+END}
</div></section>
