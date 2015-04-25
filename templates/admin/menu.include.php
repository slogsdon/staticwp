<?php
$pages = array(
  'StaticWP' => '',
  'Preload'  => 'preload',
);
$count = count($pages);
$current = 0;
$sub = isset($_GET['sub']) ? $_GET['sub'] : '';
?>

<ul class="subsubsub">
  <?php foreach ($pages as $label => $page): ?>
    <?php $current++; ?>
    <?php $url = \StaticWP\Admin::url($page); ?>
    <li>
      <a <?php if ($page === $sub): ?>class="current" <?php endif; ?>href="<?php echo $url; ?>"><?php echo $label; ?></a>
      <?php if ($current !== $count): ?>
        <span class="sep">|</span>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
