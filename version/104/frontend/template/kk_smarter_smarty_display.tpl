{if $oPlugin_kk_smarter_smarty_debug->oPluginEinstellungAssoc_arr.kk_smarter_smarty_debug_show_text_links === 'Y'}
	<a id="kk-debug-show" href="#">{$oPlugin_kk_smarter_smarty_debug->oPluginSprachvariableAssoc_arr.textlink_show}</a></div>
{/if}
<div id="kk-debug-content">
	<div class="kk-debug-search">
		{if $oPlugin_kk_smarter_smarty_debug->oPluginEinstellungAssoc_arr.kk_smarter_smarty_debug_show_text_links === 'Y'}
			<a id="kk-debug-hide" href="#">{$oPlugin_kk_smarter_smarty_debug->oPluginSprachvariableAssoc_arr.textlink_hide}</a>
		{/if}
		<input type="text" id="kk-debug-searchbox" placeholder="{$oPlugin_kk_smarter_smarty_debug->oPluginSprachvariableAssoc_arr.enter_search_term}" />
		<span id="kk-debug-search-results"></span>
		<span id="kk-debug-info-area">Fetching Debug Objects...</span>
	</div>
	{$kk_debug_string}
</div>