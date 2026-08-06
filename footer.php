<?php if (!defined('ABSPATH')) { exit; } ?>
</main><!-- /#tmf-main -->

<?php
$tel   = tmf_opt('tmf_tel');
$line  = tmf_opt('tmf_line');
$x     = tmf_opt('tmf_x');
$insta = tmf_opt('tmf_instagram');
?>

<!-- 最終CTA -->
<section class="tmf-finalcta reveal">
  <div class="tmf-finalcta__inner">
    <p class="tmf-finalcta__kicker">まずは気軽に査定から</p>
    <h2 class="tmf-finalcta__title">その一枚の価値、<br>今すぐ確かめませんか？</h2>
    <p class="tmf-finalcta__sub">写真を送るだけのカンタンLINE査定。ご来店なら最短10分でお支払いまで完結します。</p>
    <div class="tmf-finalcta__btns">
      <?php if ($line) : ?>
        <a class="tmf-btn tmf-btn--primary" href="<?php echo esc_url($line); ?>" target="_blank" rel="noopener">
          <span>LINEで無料査定</span>
        </a>
      <?php endif; ?>
      <?php if ($tel) : ?>
        <a class="tmf-btn tmf-btn--ghost" href="tel:<?php echo esc_attr(tmf_opt('tmf_tel_link', preg_replace('/[^0-9]/', '', $tel))); ?>">
          <span>電話で相談 <?php echo esc_html($tel); ?></span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer class="tmf-footer">
  <div class="tmf-footer__inner">
    <div class="tmf-footer__brand">
      <div class="tmf-footer__name">
        <span class="tmf-brand__mark">◆</span>
        <?php echo esc_html(tmf_opt('tmf_shop_name', 'TOREKAMAFIA 山口店')); ?>
      </div>
      <p class="tmf-footer__desc"><?php echo esc_html(tmf_opt('tmf_shop_name_ja', 'トレカマフィア 山口店')); ?><br>山口・広島エリア最高水準のトレカ買取専門店</p>
      <div class="tmf-footer__social">
        <?php if ($x) : ?><a href="<?php echo esc_url($x); ?>" target="_blank" rel="noopener" aria-label="X">X</a><?php endif; ?>
        <?php if ($insta) : ?><a href="<?php echo esc_url($insta); ?>" target="_blank" rel="noopener" aria-label="Instagram">Instagram</a><?php endif; ?>
        <?php if ($line) : ?><a href="<?php echo esc_url($line); ?>" target="_blank" rel="noopener" aria-label="LINE">LINE</a><?php endif; ?>
      </div>
    </div>

    <div class="tmf-footer__info">
      <dl>
        <div><dt>住所</dt><dd><?php echo esc_html(tmf_opt('tmf_address')); ?></dd></div>
        <div><dt>営業時間</dt><dd><?php echo esc_html(tmf_opt('tmf_hours')); ?></dd></div>
        <div><dt>定休日</dt><dd><?php echo esc_html(tmf_opt('tmf_holiday')); ?></dd></div>
        <?php if ($tel) : ?><div><dt>電話</dt><dd><?php echo esc_html($tel); ?></dd></div><?php endif; ?>
      </dl>
    </div>

    <?php if (has_nav_menu('footer')) : ?>
      <nav class="tmf-footer__nav" aria-label="フッターメニュー">
        <?php wp_nav_menu(array('theme_location' => 'footer', 'container' => false, 'menu_class' => 'tmf-footer__list', 'depth' => 1, 'fallback_cb' => false)); ?>
      </nav>
    <?php endif; ?>
  </div>

  <div class="tmf-footer__bottom">
    <small>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html(tmf_opt('tmf_shop_name', 'TOREKAMAFIA 山口店')); ?>. All Rights Reserved.</small>
  </div>
</footer>

<button class="tmf-totop" id="tmf-totop" aria-label="ページ上部へ戻る">↑</button>

<?php wp_footer(); ?>
</body>
</html>
