{$REQUIRE_JAVASCRIPT,core_configuration}

<div data-tpl="configCategoryScreen">
	{TITLE}

	{+START,INCLUDE,HANDLE_CONFLICT_RESOLUTION}{+END}
	{+START,IF_PASSED,WARNING_DETAILS}
		{WARNING_DETAILS}
	{+END}

	<p>{CATEGORY_DESCRIPTION*}</p>

	<h2>{!CONTENTS}</h2>

	<ul>
		{+START,LOOP,_GROUPS}
			<li><a href="#group_{_loop_key*}">{_loop_var}</a></li>
		{+END}
	</ul>

	<h2>{!OPTION_GROUPS}</h2>

	<form title="{!PRIMARY_PAGE_FORM}" class="js-form-primary-page" action="{URL*}" method="post" autocomplete="off" data-submit-pd="1">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div>
			{+START,LOOP,GROUPS}
				<a id="group_{GROUP_NAME*}"></a>

				<h3>{GROUP_TITLE*}</h3>

				<div class="wide-table-wrap"><table class="map_table form-table wide-table">
					{+START,IF,{$DESKTOP}}
						<colgroup>
							<col class="field-name-column" />
							<col class="field-input-column" />
						</colgroup>
					{+END}

					<tbody>
						{+START,IF_NON_EMPTY,{GROUP_DESCRIPTION}}
							<tr class="form-table-field-spacer">
								<th colspan="2" class="table_heading_cell">
									{GROUP_DESCRIPTION*}
								</th>
							</tr>
						{+END}

						{GROUP}
					</tbody>
				</table></div>
			{+END}
		</div>

		{+START,INCLUDE,FORM_STANDARD_END}{+END}
	</form>
</div>
