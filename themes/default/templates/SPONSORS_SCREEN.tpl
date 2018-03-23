{TITLE}

{+START,IF_NON_EMPTY,{SPONSORS}}
	<p class="lonely-label">{!SPONSORS_LABEL}</p>
	<ul>
		{+START,LOOP,SPONSORS}
			<li>
				{_loop_key*}:
				<ul>
					{+START,LOOP,AREAS}
						<li>{_loop_var*}</li>
					{+END}
				</ul>
			</li>
		{+END}
	</ul>
{+END}

{+START,IF_NON_EMPTY,{PATREON_PATRONS}}
	<p class="lonely-label">{!PATREON_PATRONS_LABEL}</p>
	<ul>
		{+START,LOOP,PATREON_PATRONS}
			<li>{NAME*}</li>
		{+END}
	</ul>
{+END}

<h2>{!CONTRIBUTE_HEADING}</h2>

<p>{!CONTRIBUTE_PARAGRAPH,{$BRAND_BASE_URL*}/contributions.htm}</p>
