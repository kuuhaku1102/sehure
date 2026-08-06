<?php
/**
 * アーカイブ（お知らせ一覧 / 強化買取カード一覧 / カテゴリー等）
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$is_kaitori = is_post_type_archive('kaitori') || is_tax('kaitori_cat');
?>
<div class="tmf-page">
  <div class="tmf-container">
    <header class="tmf-page__head reveal">
      <span class="tmf-eyebrow" style="display:inline-flex"><?php echo $is_kaitori ? 'Buyback List' : 'News'; ?></span>
      <h1 class="tmf-page__title"><?php the_archive_title(); ?></h1>
      <?php if (get_the_archive_description()) : ?>
        <p class="tmf-lead" style="margin:16px auto 0"><?php echo wp_kses_post(get_the_archive_description()); ?></p>
      <?php endif; ?>
    </header>

    <?php if ($is_kaitori && !empty($terms = get_terms(array('taxonomy' => 'kaitori_cat', 'hide_empty' => true)))) : ?>
      <div class="tmf-center" style="margin-bottom:40px;display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
        <a class="tmf-btn tmf-btn--ghost" style="--pad:10px 20px;font-size:13px" href="<?php echo esc_url(get_post_type_archive_link('kaitori')); ?>"><span>すべて</span></a>
        <?php foreach ($terms as $t) : ?>
          <a class="tmf-btn tmf-btn--ghost" style="--pad:10px 20px;font-size:13px" href="<?php echo esc_url(get_term_link($t)); ?>"><span><?php echo esc_html($t->name); ?></span></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (have_posts()) : ?>
      <?php if ($is_kaitori) : ?>
        <div class="tmf-grid tmf-grid--4 reveal-stagger">
          <?php while (have_posts()) : the_post();
            $price = get_post_meta(get_the_ID(), '_tmf_price', true);
            $note  = get_post_meta(get_the_ID(), '_tmf_note', true);
            $badge = get_post_meta(get_the_ID(), '_tmf_badge', true);
            $cat_terms = get_the_terms(get_the_ID(), 'kaitori_cat');
            $cat_name = (!is_wp_error($cat_terms) && $cat_terms) ? $cat_terms[0]->name : '';
          ?>
            <article class="tmf-glass tmf-kaitori-card" data-tilt>
              <div class="tmf-kaitori-card__img">
                <?php if ($badge) : ?><span class="tmf-kaitori-card__badge"><?php echo esc_html($badge); ?></span><?php endif; ?>
                <?php if (has_post_thumbnail()) : the_post_thumbnail('medium', array('loading' => 'lazy')); else : ?>
                  <span class="tmf-kaitori-card__ph">◆</span>
                <?php endif; ?>
              </div>
              <div class="tmf-kaitori-card__body">
                <?php if ($cat_name) : ?><span class="tmf-kaitori-card__cat"><?php echo esc_html($cat_name); ?></span><?php endif; ?>
                <h2 class="tmf-kaitori-card__name"><?php the_title(); ?></h2>
                <?php if ($note) : ?><p class="tmf-kaitori-card__note"><?php echo esc_html($note); ?></p><?php endif; ?>
                <p class="tmf-kaitori-card__price"><?php echo esc_html(tmf_format_price($price)); ?><small>まで</small></p>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php else : ?>
        <div class="tmf-news-list reveal">
          <?php while (have_posts()) : the_post(); ?>
            <a class="tmf-news-item" href="<?php the_permalink(); ?>">
              <span class="tmf-news-item__date"><?php echo esc_html(get_the_date('Y.m.d')); ?></span>
              <span class="tmf-news-item__title"><?php the_title(); ?></span>
              <span class="tmf-news-item__arrow">→</span>
            </a>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>

      <div class="tmf-center" style="margin-top:50px">
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
      </div>
    <?php else : ?>
      <p class="tmf-center tmf-lead">まだ掲載がありません。</p>
    <?php endif; ?>

    <div class="tmf-backlink">
      <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url(home_url('/')); ?>"><span>トップへ戻る</span></a>
    </div>
  </div>
</div>
<?php get_footer(); ?>
