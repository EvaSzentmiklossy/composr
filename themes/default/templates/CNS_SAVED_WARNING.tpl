{$SET,question,{!CONFIRM_DELETE,{TITLE}}}
<div data-tpl="cnsSavedWarning" data-tpl-params="{+START,PARAMS_JSON,TITLE,EXPLANATION,MESSAGE,MESSAGE_HTML,question}{_*}{+END}">
	<h3>
		{TITLE*}
	</h3>
	<nav>
		<ul class="actions-list">
			<li>
				<form title="{!LOAD} {TITLE*}" action="#" method="post" class="inline" id="saved_use__{TITLE|}" autocomplete="off">
					{$INSERT_SPAMMER_BLACKHOLE}

					<div class="inline">
						<input class="button_hyperlink" type="submit" value="{!LOAD} {TITLE*}" class="js-mouseover-activate-tooltip" data-vw-tooltip="{$ESCAPE*,<h2>{EXPLANATION*}</h2>{MESSAGE_HTML},NULL_ESCAPED}" />
					</div>
				</form>
			</li>
			<li id="saved_delete__{TITLE|}">{DELETE_LINK}</li>
		</ul>
	</nav>
</div>
