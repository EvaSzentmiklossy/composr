{TITLE}

{RESULTS_TABLE}

<div class="buttons-group">
	<form title="{!DELETE_ALL}" class="right" action="{MASS_DELETE_URL*}" method="post" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div class="inline">
			<button class="button-screen admin--delete3" type="submit">{!DELETE_ALL}</button>
		</div>
	</form>
	<form title="{!SEND_ALL}" class="right" action="{MASS_SEND_URL*}" method="post" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div class="inline">
			<button class="button-screen buttons--send" type="submit">{!SEND_ALL}</button>
		</div>
	</form>
</div>
