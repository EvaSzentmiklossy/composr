<form title="{!PRIMARY_PAGE_FORM} {!LINK_NEW_WINDOW}" action="{$URL_FOR_GET_FORM*,{EDIT_URL}}" method="get" target="_blank" autocomplete="off">
	{$HIDDENS_FOR_GET_FORM,{EDIT_URL}}

	<div>
		{TREE`}

		{HIDDEN}
	</div>

	<p class="proceed-button">
		<button data-disable-on-click="1" class="button-screen admin--edit" type="submit">{+START,INCLUDE,ICON}NAME=admin/edit{+END} {!EDIT_TEMPLATES}</button>
	</p>
</form>
