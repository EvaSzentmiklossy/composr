{$REQUIRE_JAVASCRIPT,core_cns}

<div data-tpl="cnsJoinStep1Screen">
	{TITLE}

	{HIDDEN}

	<p>
		{!DESCRIPTION_I_AGREE_RULES}
	</p>

	<div class="box box---cns-join-step1-screen"><div class="box-inner">
		<div class="cns-join-rules">
			{RULES}
		</div>
	</div></div>

	<form title="{!PRIMARY_PAGE_FORM}" class="cns-join-1" method="post" action="{URL*}" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<p>
			<input type="checkbox" id="confirm" name="confirm" value="1" class="js-chb-click-toggle-proceed-btn" /><label for="confirm">{!I_AGREE}</label>
		</p>

		{+START,IF_NON_EMPTY,{GROUP_SELECT}}
			<p>
				<label for="primary_group">{!CHOOSE_JOIN_USERGROUP}
					<select id="primary_group" name="primary_group">
						{GROUP_SELECT}
					</select>
				</label>
			</p>
		{+END}

		<p>
			<button type="button" data-disable-on-click="1" class="button-screen buttons--no js-click-set-top-location" data-tp-top-location="{$PAGE_LINK*,:}">{!I_DISAGREE}</button>

			<button accesskey="u" data-disable-on-click="1" class="button-screen buttons--yes" type="submit" disabled="disabled" id="proceed-button">{!PROCEED}</button>
		</p>
	</form>
</div>
