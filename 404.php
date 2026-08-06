<?php
/**
 * 404 ページ
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="tmf-page">
  <div class="tmf-container tmf-center">
    <div class="reveal">
      <p class="tmf-hero__title" style="font-size:clamp(60px,14vw,140px);margin-bottom:0"><span class="g">404</span></p>
      <h1 class="tmf-h2">ページが見つかりません</h1>
      <p class="tmf-lead" style="margin:16px auto 34px">お探しのページは移動または削除された可能性があります。</p>
      <a class="tmf-btn tmf-btn--primary" href="<?php echo esc_url(home_url('/')); ?>"><span>トップへ戻る</span></a>
    </div>
  </div>
</div>
<?php get_footer(); ?>
