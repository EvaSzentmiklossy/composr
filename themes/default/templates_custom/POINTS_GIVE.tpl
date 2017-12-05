{$REQUIRE_JAVASCRIPT,idolisr}

<div data-require-javascript="idolisr" data-tpl="pointsGive">
	{+START,SET,roles}
		<option value="">(Please select)</option>
		<option value="Helpful soul">Helpful soul</option>
		<option value="Support expert">Support expert</option>
		<option value="Programming god">Programming god</option>
		<option value="Themeing genius">Themeing genius</option>
		<option value="Community ambassador">Community ambassador</option>
	{+END}

	{+START,IF,{$NOT,{$HAS_ACTUAL_PAGE_ACCESS,admin_points}}}
		{$,Regular member}
		<p class="points_give_box_header">
			<span>{!GIVE_TO,{$USERNAME*,{MEMBER},1}}</span>
			{+START,IF_NON_EMPTY,{VIEWER_GIFT_POINTS_AVAILABLE}}
				{!GIVE_TEXT,{VIEWER_GIFT_POINTS_AVAILABLE*}}
			{+END}
			{+START,IF_EMPTY,{VIEWER_GIFT_POINTS_AVAILABLE}}
				{!GIVE_TEXT_UNLIMITED}
			{+END}
		</p>

		<form title="{!GIVE_POINTS}" method="post" class="js-submit-check-form" action="{GIVE_URL*}#tab__points" autocomplete="off">
			{$INSERT_SPAMMER_BLACKHOLE}

			<p>
				<label for="give_reason_pre">
					Their role
					<select id="give_reason_pre" class="js-click-check-reason js-change-check-reason" name="reason_pre">
						{$GET,roles}
					</select>
				</label>:

				<label class="accessibility_hidden" for="give_reason">{!REASON}</label>
				<input maxlength="255" size="26" id="give_reason" class="input_line_required" placeholder="{!REASON}" type="text" name="reason" />
			</p>

			<p>
				<label class="accessibility_hidden" for="give_amount">{!AMOUNT}</label>
				<input maxlength="8" data-cms-invalid-pattern="[^\-\d{$BACKSLASH}{$DECIMAL_POINT*}]" size="7" id="give_amount" class="input_integer_required" placeholder="{!AMOUNT}" type="text" name="amount" />

				<label class="points_anon" for="give_anonymous">{!TICK_ANON}: <input type="checkbox" id="give_anonymous" name="anonymous" value="1" /></label>

				<input id="give_points_submit" class="button_screen_item buttons--points" type="submit" value="{!GIVE_POINTS}" />
			</p>
		</form>
	{+END}

	{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,admin_points}}
		{$,Admin}
		<p class="points_give_box_header">
			<span>{!MODIFY_POINTS}</span>
			{+START,IF_NON_EMPTY,{VIEWER_GIFT_POINTS_AVAILABLE}}
				{!GIVE_TEXT,{VIEWER_GIFT_POINTS_AVAILABLE*}}
			{+END}
			{+START,IF_EMPTY,{VIEWER_GIFT_POINTS_AVAILABLE}}
				{!GIVE_TEXT_UNLIMITED}
			{+END}
		</p>

		<form title="{!GIVE_POINTS}" method="post" class="js-submit-check-form" action="{GIVE_URL*}#tab__points" autocomplete="off">
			{$INSERT_SPAMMER_BLACKHOLE}

			<div>
				<div class="points_give_shared_options" style="margin-top: 0">
					<label for="give_reason_pre">
						Their role
						<select id="give_reason_pre" class="js-click-check-reason js-change-check-reason" name="reason_pre">
							{$GET,roles}
						</select>
					</label>:

					<p>
						<label class="accessibility_hidden" for="give_reason">{!REASON}</label>
						<input maxlength="255" size="26" id="give_reason" class="input_line_required" placeholder="{!REASON}" type="text" name="reason" />
					</p>

					<p>
						<label class="accessibility_hidden" for="give_amount">{!AMOUNT}</label>
						<input maxlength="8" data-cms-invalid-pattern="[^\-\d{$BACKSLASH}{$DECIMAL_POINT*}]" size="7" id="give_amount" class="input_integer_required" placeholder="{!AMOUNT}" type="text" name="amount" />

						<input id="give_points_submit" class="button_micro buttons--points" type="submit" value="{!PROCEED_SHORT}" />
					</p>
				</div>

				<div class="points_give_choices">
					<p class="points_give_choice_line first">
						<label for="trans_type_gift"><strong>{!GIVE_POINTS}</strong> <input checked="checked" type="radio" id="trans_type_gift" name="trans_type" value="gift" /></label> <span class="arr">&rarr;</span>&nbsp;
						<label class="sub_option points_anon" for="give_anonymous">{!TICK_ANON}: <input type="checkbox" id="give_anonymous" name="anonymous" value="1" /></label>
					</p>

					<p class="points_give_choice_line">
						<strong>{!MODIFY_POINTS}</strong> <span class="arr">&rarr;</span>&nbsp;
						<label class="sub_option" for="trans_type_charge">{!CHARGE} <input type="radio" id="trans_type_charge" name="trans_type" value="charge" /></label>
						<label class="sub_option" for="trans_type_refund">{!REFUND} <input type="radio" id="trans_type_refund" name="trans_type" value="refund" /></label>
					</p>
				</div>
			</div>
		</form>
	{+END}
</div>
