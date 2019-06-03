{*
 * 2007-2019  PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    itstuff.com.ar
 * @copyright Copyright (c) ItStuff [https://itstuff.com.ar]
 * @license   https://itstuff.com.ar/licenses/commercial-1.0.html Commercial License
 *}

<div class="itst-tabs">
	{if $tabs}
		<ul class="nav nav-tabs" id="itst-config-nav" role="tablist">
			{foreach $tabs as $tab}
				<li class="nav-item">
					<a class="nav-link" 
					id="{$tab.id|escape:'htmlall':'UTF-8'}-tab" 
					data-toggle="tab" 
					data-target="#{$tab.id|escape:'htmlall':'UTF-8'}-content"
					href="#{$tab.id|escape:'htmlall':'UTF-8'}" role="tab" aria-controls="{$tab.id|escape:'htmlall':'UTF-8'}" 
					aria-selected="true">{$tab.title|escape:'htmlall':'UTF-8'}</a>
				</li>
			{/foreach}			
		</ul>
		<div class="panel-body">			
			<div class="tab-content" id="myTabContent">
				{foreach $tabs as $tab}
					<div class="tab-pane" 
						id="{$tab.id|escape:'htmlall':'UTF-8'}-content" role="tabpanel" aria-labelledby="{$tab.id|escape:'htmlall':'UTF-8'}-tab">
						{html_entity_decode($tab.content|escape:'htmlall':'UTF-8')}
						</div>
				{/foreach}
			</div>
		</div>
	{/if}
</div>

<script>
  $(function () {
		$('#itst-config-nav a').on('click', function (e) {
			e.preventDefault()
			$(this).tab('show')
		})	
  })

	$(document).ready(function(){
		$('#itst-config-nav a[href="#{$selectedTab}"]').tab('show')
	})
</script>