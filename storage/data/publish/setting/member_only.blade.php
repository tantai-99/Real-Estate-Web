;
;会員専用サイト
;
<?php foreach($view->memberOnly as $page) : ?>
[<?php echo $page['id'] ;?>]
page_id = '<?php echo $page['id'] ;?>'
path = '<?php echo $page['new_path'] ;?>'
redirect_to = '<?php echo $page['redirect_to'] ;?>'
id = '<?php echo $page['member_id'] ;?>'
pass = '<?php echo hash('sha256',$page['member_id'].$page['member_password']); ?>'

<?php endforeach ;?>