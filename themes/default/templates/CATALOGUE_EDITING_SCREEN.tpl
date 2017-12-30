<div data-view="CatalogueEditingScreen">
	{TITLE}

	{$PARAGRAPH,{TEXT}}

	<form title="{!PRIMARY_PAGE_FORM}" method="post" action="{URL*}" target="_top" id="catalogue_form" class="js-form-catalogue-edit" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div>
			{HIDDEN}

			<div class="wide-table-wrap"><table class="map_table form-table wide-table scrollable-inside">
				{+START,IF,{$DESKTOP}}
					<colgroup>
						<col class="field-name-column" />
						<col class="field-input-column" />
					</colgroup>
				{+END}

				<tbody>
					{FIELDS}
				</tbody>
			</table></div>

			<h2>{!FIELDS_EXISTING}</h2>
			{FIELDS_EXISTING}

			<h2>{!FIELDS_NEW}</h2>
			<p>{!FIELDS_NEW_HELP}</p>
			{FIELDS_NEW}

			{+START,INCLUDE,FORM_STANDARD_END}
				FORM_NAME=catalogue_form
				SUPPORT_AUTOSAVE=1
			{+END}
		</div>
	</form>
</div>
