<?php
/**
 * フロントページ（トップ）
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$line = tmf_opt('tmf_line');
$tel  = tmf_opt('tmf_tel');
$cta1_url = tmf_opt('tmf_cta1_url') ?: ($line ?: '#access');
$cta2_url = tmf_opt('tmf_cta2_url', '#flow');
?>

<!-- ============ ヒーロー ============ -->
<section class="tmf-hero" id="top">
  <?php if (tmf_opt('tmf_hero_bg')) : ?>
    <div class="tmf-hero__bg" style="background-image:url('<?php echo esc_url(tmf_opt('tmf_hero_bg')); ?>')"></div>
  <?php endif; ?>
  <div class="tmf-hero__inner">
    <div class="tmf-hero__copy">
      <span class="tmf-hero__kicker"><?php echo esc_html(tmf_opt('tmf_hero_kicker', '山口・広島エリア 最高水準の買取')); ?></span>
      <h1 class="tmf-hero__title"><span class="g"><?php echo wp_kses_post(nl2br(tmf_opt('tmf_hero_title', 'その一枚、<br>東京基準で買い取る。'))); ?></span></h1>
      <p class="tmf-hero__sub"><?php echo wp_kses_post(tmf_opt('tmf_hero_sub', '秋葉原水準の買取表 × 迅速査定 × 即日お支払い。ポケカ・ワンピ・遊戯王、高価買取はトレカマフィア山口店へ。')); ?></p>
      <div class="tmf-hero__btns">
        <a class="tmf-btn tmf-btn--primary" href="<?php echo esc_url($cta1_url); ?>" <?php echo (strpos($cta1_url, 'http') === 0 ? 'target="_blank" rel="noopener"' : ''); ?>>
          <span><?php echo esc_html(tmf_opt('tmf_cta1_text', 'LINEで無料査定')); ?></span>
        </a>
        <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url($cta2_url); ?>">
          <span><?php echo esc_html(tmf_opt('tmf_cta2_text', '買取の流れを見る')); ?></span>
        </a>
      </div>
    </div>
    <div class="tmf-hero__visual" aria-hidden="true">
      <div class="tmf-card3d tmf-card3d--1"></div>
      <div class="tmf-card3d tmf-card3d--2"></div>
      <div class="tmf-card3d tmf-card3d--3"></div>
    </div>
  </div>
  <div class="tmf-scrolldown"><span></span>SCROLL</div>
</section>

<!-- ============ 実績カウンター ============ -->
<section class="tmf-section" style="padding-top:0" aria-label="実績">
  <div class="tmf-container">
    <div class="tmf-stats reveal-stagger">
      <?php
      for ($i = 1; $i <= 3; $i++) :
        $num  = tmf_opt("tmf_stat{$i}_num");
        $unit = tmf_opt("tmf_stat{$i}_unit");
        $lbl  = tmf_opt("tmf_stat{$i}_lbl");
        if ($num === '') continue;
        $is_numeric = is_numeric($num);
      ?>
        <div class="tmf-stat">
          <div class="tmf-stat__num">
            <span class="g"><?php if ($is_numeric) : ?><span data-count="<?php echo esc_attr($num); ?>">0</span><?php else : echo esc_html($num); endif; ?><span class="tmf-stat__unit"><?php echo esc_html($unit); ?></span></span>
          </div>
          <div class="tmf-stat__label"><?php echo esc_html($lbl); ?></div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ============ 選ばれる理由 ============ -->
<section class="tmf-section" id="strength">
  <div class="tmf-container">
    <div class="tmf-section__head tmf-center reveal">
      <span class="tmf-eyebrow">Why TOREKAMAFIA</span>
      <h2 class="tmf-h2">トレカマフィアが<span class="g">選ばれる理由</span></h2>
      <p class="tmf-lead">「せっかく売るなら、一番高いところで。」その願いに、地方だからと妥協させない。都市部の相場をそのまま山口へ持ち込みます。</p>
    </div>
    <div class="tmf-grid tmf-grid--3 reveal-stagger">
      <?php
      $strengths = array(
        array('01', '💹', '秋葉原水準の買取表', '相場の中心地・東京の買取価格を基準に査定。地方相場ではなく、全国トップクラスの金額でお値付けします。'),
        array('02', '⚡', '迅速査定・即日お支払い', 'ショーケースの人気カードは最短10分。査定後はその場で現金にてお支払い。待たせません。'),
        array('03', '🛡️', '査定・キャンセル無料', '査定は完全無料。金額にご納得いただけなければキャンセルもOK。安心してお持ち込みください。'),
      );
      foreach ($strengths as $s) : ?>
        <article class="tmf-glass tmf-strength">
          <span class="tmf-strength__no"><?php echo esc_html($s[0]); ?></span>
          <div class="tmf-strength__icon"><?php echo $s[1]; ?></div>
          <h3 class="tmf-strength__title"><?php echo esc_html($s[2]); ?></h3>
          <p class="tmf-strength__text"><?php echo esc_html($s[3]); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 買取カテゴリー ============ -->
<section class="tmf-section" id="category">
  <div class="tmf-container">
    <div class="tmf-section__head tmf-center reveal">
      <span class="tmf-eyebrow">Buyback Category</span>
      <h2 class="tmf-h2"><span class="g">なんでも</span>高価買取</h2>
      <p class="tmf-lead">ポケカを中心に、主要TCGを幅広く強化買取中。ジャンルを問わずまとめてお売りいただけます。</p>
    </div>
    <div class="tmf-grid tmf-grid--3 reveal-stagger">
      <?php
      $cats = array(
        array('⚡', 'ポケモンカード', 'ポケカ / PSA / シングル'),
        array('🏴‍☠️', 'ワンピースカード', 'リーダー / パラレル'),
        array('🐉', '遊戯王', '初期 / レリーフ / 高レア'),
        array('🔥', 'デュエル・マスターズ', 'スーパーレア他'),
        array('🌸', 'ヴァイスシュヴァルツ', 'SP / サイン'),
        array('✨', 'その他TCG・BOX', 'MTG / 未開封BOX'),
      );
      // カスタム分類が登録されていれば優先表示
      $terms = get_terms(array('taxonomy' => 'kaitori_cat', 'hide_empty' => false));
      if (!is_wp_error($terms) && !empty($terms) && get_option('tmf_seeded_cats')) {
        $icons = array('⚡', '🏴‍☠️', '🐉', '🔥', '🌸', '✨', '🎴', '💎');
        $cats = array();
        foreach ($terms as $idx => $t) {
          $cats[] = array($icons[$idx % count($icons)], $t->name, ($t->count > 0 ? $t->count . '点 掲載中' : '強化買取中'));
        }
      }
      foreach ($cats as $c) : ?>
        <a class="tmf-glass tmf-cat" href="<?php echo esc_url(get_post_type_archive_link('kaitori') ?: '#kaitori'); ?>">
          <span class="tmf-cat__icon"><?php echo $c[0]; ?></span>
          <span class="tmf-cat__body">
            <h3><?php echo esc_html($c[1]); ?></h3>
            <p><?php echo esc_html($c[2]); ?></p>
          </span>
          <span class="tmf-cat__arrow">→</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 強化買取カード ============ -->
<?php
$kaitori = new WP_Query(array(
  'post_type'      => 'kaitori',
  'posts_per_page' => 8,
  'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
));
if ($kaitori->have_posts()) : ?>
<section class="tmf-section" id="kaitori">
  <div class="tmf-container">
    <div class="tmf-section__head tmf-center reveal">
      <span class="tmf-eyebrow">Featured Buyback</span>
      <h2 class="tmf-h2"><span class="g">強化買取</span>カード</h2>
      <p class="tmf-lead">現在とくに高価買取中の注目カード。掲載外のカードもぜひ一度ご相談ください。</p>
    </div>
    <div class="tmf-grid tmf-grid--4 reveal-stagger">
      <?php while ($kaitori->have_posts()) : $kaitori->the_post();
        $price = get_post_meta(get_the_ID(), '_tmf_price', true);
        $note  = get_post_meta(get_the_ID(), '_tmf_note', true);
        $badge = get_post_meta(get_the_ID(), '_tmf_badge', true);
        $cat_terms = get_the_terms(get_the_ID(), 'kaitori_cat');
        $cat_name = (!is_wp_error($cat_terms) && $cat_terms) ? $cat_terms[0]->name : '';
      ?>
        <article class="tmf-glass tmf-kaitori-card" data-tilt>
          <div class="tmf-kaitori-card__img">
            <?php if ($badge) : ?><span class="tmf-kaitori-card__badge"><?php echo esc_html($badge); ?></span><?php endif; ?>
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('medium', array('loading' => 'lazy', 'alt' => get_the_title())); ?>
            <?php else : ?>
              <span class="tmf-kaitori-card__ph">◆</span>
            <?php endif; ?>
          </div>
          <div class="tmf-kaitori-card__body">
            <?php if ($cat_name) : ?><span class="tmf-kaitori-card__cat"><?php echo esc_html($cat_name); ?></span><?php endif; ?>
            <h3 class="tmf-kaitori-card__name"><?php the_title(); ?></h3>
            <?php if ($note) : ?><p class="tmf-kaitori-card__note"><?php echo esc_html($note); ?></p><?php endif; ?>
            <p class="tmf-kaitori-card__price"><?php echo esc_html(tmf_format_price($price)); ?><small>まで</small></p>
          </div>
        </article>
      <?php endwhile; ?>
    </div>
    <div class="tmf-center" style="margin-top:40px">
      <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url(get_post_type_archive_link('kaitori')); ?>"><span>買取価格一覧を見る</span></a>
    </div>
  </div>
</section>
<?php endif; wp_reset_postdata(); ?>

<!-- ============ 買取の流れ ============ -->
<section class="tmf-section" id="flow">
  <div class="tmf-container">
    <div class="tmf-section__head tmf-center reveal">
      <span class="tmf-eyebrow">How it works</span>
      <h2 class="tmf-h2">かんたん<span class="g">4ステップ</span></h2>
      <p class="tmf-lead">ご来店でもLINEでも。面倒な手続きなしで、その場でスピード現金化。</p>
    </div>
    <div class="tmf-flow reveal-stagger">
      <?php
      $flow = array(
        array('01', 'ご来店 / LINE', 'カードをお持ち込み、または写真をLINEで送るだけ。事前予約は不要です。'),
        array('02', 'スピード査定', '秋葉原水準の買取表で、その場でプロが査定。相場もしっかりご説明します。'),
        array('03', '金額のご提示', '査定額にご納得いただけたら成約。ご納得いかなければキャンセルも無料。'),
        array('04', '即日お支払い', 'ご成約後はその場で現金にてお支払い。待ち時間なくお受け取りいただけます。'),
      );
      foreach ($flow as $f) : ?>
        <div class="tmf-flow__step">
          <div class="tmf-flow__num"><?php echo esc_html($f[0]); ?></div>
          <h3 class="tmf-flow__title"><?php echo esc_html($f[1]); ?></h3>
          <p class="tmf-flow__text"><?php echo esc_html($f[2]); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ お知らせ / イベント ============ -->
<?php
$news = new WP_Query(array('post_type' => 'news', 'posts_per_page' => 5));
if ($news->have_posts()) : ?>
<section class="tmf-section" id="news">
  <div class="tmf-container">
    <div class="tmf-section__head tmf-center reveal">
      <span class="tmf-eyebrow">News &amp; Event</span>
      <h2 class="tmf-h2">お知らせ・<span class="g">イベント</span></h2>
      <p class="tmf-lead">オリパ情報やキャンペーン、買取強化など最新情報をお届けします。</p>
    </div>
    <div class="tmf-news-list reveal">
      <?php while ($news->have_posts()) : $news->the_post(); ?>
        <a class="tmf-news-item" href="<?php the_permalink(); ?>">
          <span class="tmf-news-item__date"><?php echo esc_html(get_the_date('Y.m.d')); ?></span>
          <span class="tmf-news-item__title"><?php the_title(); ?></span>
          <span class="tmf-news-item__arrow">→</span>
        </a>
      <?php endwhile; ?>
    </div>
    <div class="tmf-center" style="margin-top:34px">
      <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url(get_post_type_archive_link('news')); ?>"><span>お知らせ一覧</span></a>
    </div>
  </div>
</section>
<?php endif; wp_reset_postdata(); ?>

<!-- ============ 店舗情報 / アクセス ============ -->
<section class="tmf-section" id="access">
  <div class="tmf-container">
    <div class="tmf-section__head tmf-center reveal">
      <span class="tmf-eyebrow">Shop Information</span>
      <h2 class="tmf-h2"><span class="g">店舗情報</span>・アクセス</h2>
    </div>
    <div class="tmf-access reveal">
      <div class="tmf-access__map<?php echo tmf_opt('tmf_map_src') ? '' : ' tmf-access__map--placeholder'; ?>">
        <?php if (tmf_opt('tmf_map_src')) : ?>
          <iframe src="<?php echo esc_url(tmf_opt('tmf_map_src')); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="店舗地図" allowfullscreen></iframe>
        <?php else : ?>
          <div>
            <p>📍</p>
            <p><?php echo esc_html(tmf_opt('tmf_address')); ?></p>
            <p style="font-size:13px;margin-top:10px">※ 管理画面 &gt; 外観 &gt; カスタマイズ &gt; 店舗基本情報 からGoogleマップを設定できます</p>
          </div>
        <?php endif; ?>
      </div>
      <div class="tmf-glass tmf-info">
        <dl>
          <div class="tmf-info__row"><dt>店舗名</dt><dd><?php echo esc_html(tmf_opt('tmf_shop_name_ja', 'トレカマフィア 山口店')); ?></dd></div>
          <div class="tmf-info__row"><dt>住所</dt><dd><?php echo esc_html(tmf_opt('tmf_address')); ?></dd></div>
          <div class="tmf-info__row"><dt>アクセス</dt><dd><?php echo esc_html(tmf_opt('tmf_access')); ?></dd></div>
          <div class="tmf-info__row"><dt>営業時間</dt><dd><?php echo esc_html(tmf_opt('tmf_hours')); ?></dd></div>
          <div class="tmf-info__row"><dt>定休日</dt><dd><?php echo esc_html(tmf_opt('tmf_holiday')); ?></dd></div>
          <?php if ($tel) : ?><div class="tmf-info__row"><dt>電話</dt><dd><?php echo esc_html($tel); ?></dd></div><?php endif; ?>
        </dl>
        <div class="tmf-info__btns">
          <?php if ($line) : ?><a class="tmf-btn tmf-btn--gold" href="<?php echo esc_url($line); ?>" target="_blank" rel="noopener"><span>LINEで査定</span></a><?php endif; ?>
          <?php if ($tel) : ?><a class="tmf-btn tmf-btn--ghost" href="tel:<?php echo esc_attr(tmf_opt('tmf_tel_link', preg_replace('/[^0-9]/', '', $tel))); ?>"><span>電話する</span></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
