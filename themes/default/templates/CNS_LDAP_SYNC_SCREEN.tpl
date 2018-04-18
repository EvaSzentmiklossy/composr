{TITLE}

<p>
	{!LDAP_INTRO}
</p>

<form title="{!PRIMARY_PAGE_FORM}" action="{URL*}" method="post" autocomplete="off">
	{$INSERT_SPAMMER_BLACKHOLE}

	<div>
		<p>
			{!LDAP_SYNC_GROUPS_ADD}
		</p>

		<div class="standard-indent">
			{GROUPS_ADD}
			{+START,IF_EMPTY,{GROUPS_ADD}}
				<p class="nothing-here">{!NONE}</p>
			{+END}
		</div>
	</div>

	<div>
		<p>
			{!LDAP_SYNC_GROUPS_DELETE}
		</p>

		<div class="standard-indent">
			{GROUPS_DELETE}
			{+START,IF_EMPTY,{GROUPS_DELETE}}
				<p class="nothing-here">{!NONE}</p>
			{+END}
		</div>
	</div>

	<div>
		<p>
			{!LDAP_SYNC_MEMBERS_DELETE}
		</p>

		<div class="standard-indent">
			{MEMBERS_DELETE}
			{+START,IF_EMPTY,{MEMBERS_DELETE}}
				<p class="nothing-here">{!NONE}</p>
			{+END}
		</div>
	</div>

	<p class="proceed-button">
		<button accesskey="u" data-disable-on-click="1" class="button-screen buttons--save" type="submit">{+START,INCLUDE,ICON}NAME=buttons/save{+END} {!SAVE}</button>
	</p>
</form>
