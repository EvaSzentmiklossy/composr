{$REQUIRE_JAVASCRIPT,shopping}

<div data-tpl="ecomAdminOrdersScreen">
	{TITLE}

	<div class="wide-table-wrap">
		{RESULTS_TABLE}

		{+START,IF_NON_EMPTY,{PAGINATION}}
			<div class="pagination-spacing clearfix">
				{PAGINATION}
			</div>
		{+END}
	</div>

	<h2 class="force-margin">{!SEARCH}</h2>

	<form title="{!SEARCH}" target="_self" method="get" action="{SEARCH_URL*}" class="js-submit-scroll-to-top" autocomplete="off">
		<div>
			{HIDDEN}

			<label for="order-filter">
				<span class="invisible-ref-point"></span>
				<input maxlength="255" type="text" id="order-filter" class="form-control form-control-inline" name="search" value="{SEARCH_VAL*}" />
				<button data-disable-on-click="1" class="btn btn-primary btn-sm buttons--search" type="submit">{+START,INCLUDE,ICON}NAME=buttons/search{+END} {!SEARCH}</button> ({!SEARCH_ORDERS})
			</label>
		</div>
	</form>

	<h2 class="force-margin">{!MORE} / {!ADVANCED}</h2>

	<p class="lonely-label">
		{!ACTIONS}:
	</p>
	<nav>
		<ul class="actions-list">
			<li class="actions-list-strong">
				{+START,INCLUDE,ICON}NAME=buttons/proceed2{+END} <a href="{$PAGE_LINK*,_SELF:_SELF:order_export}">{!EXPORT_ORDER_LIST}</a>
			</li>
		</ul>
	</nav>
</div>
