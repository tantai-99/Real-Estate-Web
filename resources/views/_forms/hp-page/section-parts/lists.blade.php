<script type="text/javascript">
$(function () {
	'use strict';
	
	var token = '<?php echo csrf_token(false)?>';
	
	var $tbody = $('.section.detail-list table tbody');
	
	function updateSort(sendRequest) {
		
		var data = {};
		data._token = token;
		data.sort = [];
		
		var $children = $tbody.children();
		$children.each(function (i) {
			data.sort.push($(this).attr('data-id'));
			
			var $this = $(this);
			$this.find('.i-e-up').toggleClass('is-disable', i === 0);
			$this.find('.i-e-down').toggleClass('is-disable', i === $children.length - 1);
		});
		
		if (!sendRequest) {
			return;
		}
		
		app.api('/site-map/api-sort', data);
	}
	
	$tbody.on('click', '.i-e-up:not(.is-disable)', function () {
		var $tr = $(this).closest('tr');
		$tr.after($tr.prev());
		updateSort(true);
	});
	$tbody.on('click', '.i-e-down:not(.is-disable)', function () {
		var $tr = $(this).closest('tr');
		$tr.before($tr.next());
		updateSort(true);
	});
	
	updateSort();
});
</script>
<div class="section detail-list">
	<?php $page = $element->getPage()?>
	<h2><?php echo $page->getTypeNameJp()?><?php if ($page->page_type_code == config('constants.hp_page.TYPE_INFO_INDEX')) { ?><a href="#" data-url="<?php  echo $view->route('edit')?>?parent_id=<?php echo $page->id?>" class="btn-t-gray add-news-detail">新規作成</a><?php } else { ?><a href="<?php  echo $view->route('edit')?>?parent_id=<?php echo $page->id?>" class="btn-t-gray">新規作成</a><?php } ?></h2>
	<table class="tb-basic">
		
		<thead>
			<tr>
				<th>ページタイトル</th>
				<th>状態</th>
				<?php if ($page->hasMultiPageType()):?>
				<th>日付</th>
				<?php endif;?>
				<th>更新日</th>
				<?php if (!$page->hasMultiPageType()):?>
				<th>表示順</th>
				<?php endif;?>
				<th>操作</th>
				<th>詳細ページプレビュー</th>
			</tr>
		</thead>
		
		<tbody>
		<?php foreach ($element->getRowset() as $row):?>
			<tr data-id="<?php echo $row->id?>">
				<td><?php echo h($row->title)?></td>
				<td class="page-edit-status">
					<span class="<?php if($row->isPublic()):?>is-public<?php else:?>is-draft<?php endif;?>"><?php if($row->isPublic()):?>公開中<?php else:?>下書き<?php endif;?></span>
				</td>
				<?php if ($page->hasMultiPageType()):?>
				<td><?php echo date('Y/m/d', strtotime($row->date))?></td>
				<?php endif;?>
				<td><?php if($row->updated_at):?><?php echo date('Y/m/d', strtotime($row->updated_at))?><?php endif;?></td>
				<?php if (!$page->hasMultiPageType()):?>
				<td class="action">
					<a href="javascript:void(0);" class="i-e-up">上へ移動</a>
					<a href="javascript:void(0);" class="i-e-down">下へ移動</a>
				</td>
				<?php endif;?>
				<td><a href="<?php // echo $view->route()?>?id=<?php echo $row->id?>&parent_id=<?php echo $row->parent_page_id?>" class="btn-t-gray size-s">編集</a></td>
                <td class="page-edit-preview">
                    <?php if (!$row->notIsPageInfoDetail()):?>
					<?php if (!$row->isNew()):?>
					<a href="<?php echo urlSimple('preview-page', 'publish', 'default', array('id'=>$row->id, 'parent_id'=>(int)$row->parent_page_id,'device'=>'pc'))?>?load=1" target="_blank" class="btn-p-pc"></a>
					<a href="<?php echo urlSimple('preview-page', 'publish', 'default', array('id'=>$row->id, 'parent_id'=>(int)$row->parent_page_id,'device'=>'sp'))?>?load=1" target="_blank" class="btn-p-sp"></a>
					<?php endif;?>
                    <?php endif;?>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
		
	</table>
	
	<?php if ($paginator = $element->getPaginator()):?>
		<?php echo $paginator->links('pagination-index',['search_param' => array('id'=>$page->id)]); // echo $view->paginationControl($paginator, null, null, array('search_param' => array('id'=>$page->id))) ?>
	<?php endif;?>
</div>
