<div class="xmstats">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="index.php"><{$index_module}></a></li>
			<{if $op == ''}>
			<li class="breadcrumb-item active" aria-current="page"><{$smarty.const._MI_XMSTATS_SUB_EXPORT}></li>
			<{/if}>
			<{if $op == 'article' || $op == 'stock' || $op == 'transfer'}>
			<li class="breadcrumb-item"><a href="<{$xoops_url}>/modules/xmstats/export.php"><{$smarty.const._MI_XMSTATS_SUB_EXPORT}></a></li>
			<{/if}>
			<{if $op == 'article'}>
			<li class="breadcrumb-item active" aria-current="page"><{$smarty.const._MA_XMSTATS_EXPORT_ARTICLE}></li>
			<{/if}>
			<{if $op == 'stock'}>
				<li class="breadcrumb-item active" aria-current="page"><{$smarty.const._MA_XMSTATS_EXPORT_ARTICLE}></li>
			<{/if}>

		</ol>
	</nav>
	<h2><{$smarty.const._MA_XMSTATS_EXPORT_TITLE}></h2>
	<{if $xmstock == true && $xmarticle == true}>
	<div class="text-center pt-2 mb-2">
		<div class="btn-group text-center" role="group">
			<{if $xmarticle == true}>
				<{if $perm_kardex == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_KARDEX}>" class="btn btn-light btn-lg <{if $op == 'kardex'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=kardex">
					<span class="fa fa-folder fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_KARDEX}></p>
				</a>
				<{/if}>
				<{if $perm_article == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_ARTICLE}>" class="btn btn-light btn-lg <{if $op == 'article'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=article">
					<span class="fa fa-server fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_ARTICLE}></p>
				</a>
				<{/if}>
			<{/if}>
			<{if $xmstock == true}>
				<{if $perm_stock == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_STOCK}>" class="btn btn-light btn-lg <{if $op == 'stock'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=stock">
					<span class="fa fa-cubes fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_STOCK}></p>
				</a>
				<{/if}>
				<{if $perm_transfer == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_TRANSFER}>" class="btn btn-light btn-lg <{if $op == 'transfer'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=transfer">
					<span class="fa fa-random fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_TRANSFER}></p>
				</a>
				<{/if}>
				<{if $perm_loan == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_LOAN}>" class="btn btn-light btn-lg <{if $op == 'loan'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=loan">
					<span class="fa fa-exchange fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_LOAN}></p>
				</a>
				<{/if}>
				<{if $perm_overdraft == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_OVERDRAFT}>" class="btn btn-light btn-lg <{if $op == 'overdraft'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=overdraft">
					<span class="fa fa-battery-quarter fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_OVERDRAFT}></p>
				</a>
				<{/if}>
				<{if $perm_order == true}>
				<a title="<{$smarty.const._MA_XMSTATS_EXPORT_ORDER}>" class="btn btn-light btn-lg <{if $op == 'order'}>disabled<{/if}>" href="<{$xoops_url}>/modules/xmstats/export.php?op=order">
					<span class="fa fa-list-alt fa-2x"></span><p class="mt-2"><{$smarty.const._MA_XMSTATS_EXPORT_ORDER}></p>
				</a>
				<{/if}>
			<{/if}>
		</div>
	</div>
	<{/if}>
	<{if $form|default:'' != ''}>
		<{$form}>
	<{/if}>
	<{if $error|default:'' != ''}>
		<div class="alert alert-danger" role="alert">
			<{$error}>
		</div>
	<{/if}>
</div><!-- .xmstats -->