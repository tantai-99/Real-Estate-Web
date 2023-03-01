<?php
if($paginator->total() > 1): ?>
<?php
$current = $paginator->currentPage();
$lastPage = $paginator->lastPage();
$start = 1;
$end = $lastPage;
if ($lastPage > 10) {
	$end = 10;
	if ($current > 5) {
		$start = $current - 4;
		$end = $current + 5;
	}
	if ($current > $lastPage - 5) {
		$start = $lastPage - 9;
		$end = $lastPage;
	}
}
$searchParam = '';
if ($search_param) 
{
	$searchParam .= '&' . http_build_query($search_param);
}
?>
    <!-- Pagination -->
	@if ($paginator->hasPages())
		<div class="section">
			<ul class="paging">
				{{-- Prev Page Link --}}
				@if($current != 1)
					<li class="prev">
						<a href="{{ $paginator->previousPageUrl()}}{{$searchParam}}" rel="prev" aria-label="@lang('pagination.previous')"></a>
					</li>
				@endif
				{{-- Pagination Elements --}}
				@foreach ($paginator->getUrlRange($start, $end) as $page => $url)
					@if ($page == $current)
						<li class="is-active" aria-current="page" ><a>{{ $page }}</a></li>
					@else
						<li><a href="{{$url}}{{$searchParam}}">{{$page}}</a></li>
					@endif
				@endforeach
				{{-- Next Page Link --}}
				@if($current != $lastPage)
					<li class="next">
						<a href="{{ $paginator->nextPageUrl()}}{{$searchParam}}" rel="next" aria-label="@lang('pagination.next')"></a>
					</li>
				@endif
			</ul>
		</div>
	@endif
    <!-- Pagination -->
<?php endif;?>
