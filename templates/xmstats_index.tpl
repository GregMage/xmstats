<div class="xmstats">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="active"><{$index_module}></li>
		</ol>
	</nav>
	<div class="row">
		<{if $xmstock == true}>
		<div class="col-sm-6">
			<div class="card">
				<h5 class="card-header"><{$smarty.const._MA_XMSTATS_INDEX_XMSTOCK}></h5>
				<div class="card-body">
					<h5 class="card-title"><{$smarty.const._MA_XMSTATS_INDEX_ORDER}></h5>
					<div class="row">
						<div class="col-8">
							<{$smarty.const._MA_XMSTATS_INDEX_ALL}>
						</div>
						<div class="col-4">
							<span class="badge badge-pill badge-secondary"><{$order_all}></span>
						</div>
						<div class="col-8">
							<{$smarty.const._MA_XMSTATS_INDEX_YEAR}>
						</div>
						<div class="col-4">
							<span class="badge badge-pill badge-secondary"><{$order_year}></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<{/if}>
	</div>
</div><!-- .xmstats -->