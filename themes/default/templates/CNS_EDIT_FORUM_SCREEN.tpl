{TITLE}

<p>
	{!CHOOSE_FORUM_EDIT}
</p>

<form title="{!PRIMARY_PAGE_FORM}" method="post" action="{REORDER_URL*}" autocomplete="off">
	{$INSERT_SPAMMER_BLACKHOLE}

	{ROOT_FORUM}

	{+START,IF_NON_EMPTY,{REORDER_URL}}
		<p class="proceed-button">
			<input accesskey="u" data-disable-on-click="1" class="button-screen buttons--proceed" type="submit" value="{!REORDER_FORUMS}" />
		</p>
	{+END}
</form>

<div class="box box___edit_forum_screen"><div class="box-inner help-jumpout">
	<p>
		{!CHOOSE_FORUM_EDIT_2}
	</p>
</div></div>
