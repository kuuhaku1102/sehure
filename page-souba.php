<?php
/**
 * Template Name: 相場検索ページ
 *
 * ポケモンPSA10の相場を検索・並べ替えでき、各カードに予想相場と推移グラフを表示。
 * データは inc/price-tracker.php が蓄積したWP独自テーブルから取得。
 *
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$cards = function_exists('tmf_get_cards') ? tmf_get_cards() : array();
$updated = get_option('tmf_last_import', '');
$horizon = (int) get_option('tmf_forecast_days', 30);

// 傾向表示マップ
$trend_class = function ($t) {
    if (mb_strpos($t, '上昇') !== false || mb_strpos($t, '上') !== false) return 'up';
    if (mb_strpos($t, '下落') !== false || mb_strpos($t, '下') !== false) return 'down';
    return 'flat';
};
?>
<div class="tmf-page tmf-souba">
  <div class="tmf-container">

    <header class="tmf-page__head tmf-center reveal">
      <span class="tmf-eyebrow" style="display:inline-flex">Market Price Search</span>
      <h1 class="tmf-page__title">ポケカ PSA10 <span class="g">相場検索</span></h1>
      <p class="tmf-lead" style="margin:16px auto 0">
        カード名・品番で検索して、PSA10の最新相場と<strong>AIによる予想相場（<?php echo esc_html($horizon); ?>日先）</strong>をチェック。
        毎日データを蓄積し、価格の推移と今後の値動きを可視化します。
      </p>
      <?php if ($updated) : ?>
        <p style="color:var(--muted);font-size:12px;margin-top:10px">最終更新：<?php echo esc_html($updated); ?></p>
      <?php endif; ?>
    </header>

    <?php if (empty($cards)) : ?>
      <div class="tmf-glass" style="padding:40px;text-align:center">
        <p class="tmf-lead" style="margin:0 auto">
          まだ相場データがありません。<br>
          <?php if (current_user_can('manage_options')) : ?>
            管理画面 <b>「相場データ」</b> でCSV URLを設定し、「今すぐ取り込む」を実行してください。
          <?php else : ?>
            データ準備中です。しばらくお待ちください。
          <?php endif; ?>
        </p>
      </div>
    <?php else : ?>

      <!-- コントロール -->
      <div class="tmf-souba__controls reveal">
        <div class="tmf-souba__search">
          <span class="tmf-souba__search-icon">🔍</span>
          <input type="search" id="tmf-souba-q" placeholder="カード名・品番で検索（例：リザードン / 323/S-P）" autocomplete="off" aria-label="カード検索">
        </div>
        <div class="tmf-souba__filters" role="group" aria-label="傾向で絞り込み">
          <button class="tmf-chip is-active" data-trend="all">すべて</button>
          <button class="tmf-chip" data-trend="up">📈 上昇</button>
          <button class="tmf-chip" data-trend="flat">➖ 横ばい</button>
          <button class="tmf-chip" data-trend="down">📉 下落</button>
        </div>
        <select id="tmf-souba-sort" class="tmf-souba__sort" aria-label="並べ替え">
          <option value="price-desc">価格が高い順</option>
          <option value="price-asc">価格が安い順</option>
          <option value="fc-desc">予想上昇率が高い順</option>
          <option value="fc-asc">予想下落率が高い順</option>
          <option value="name-asc">名前順</option>
        </select>
      </div>
      <p class="tmf-souba__count"><span id="tmf-souba-shown"><?php echo count($cards); ?></span> 件を表示中</p>

      <!-- テーブル -->
      <div class="tmf-souba__table-wrap reveal">
        <table class="tmf-souba__table" id="tmf-souba-table">
          <thead>
            <tr>
              <th class="col-card">カード</th>
              <th class="col-price">最新相場</th>
              <th class="col-trend">傾向 / 7日</th>
              <th class="col-chart">推移</th>
              <th class="col-fc">予想相場<br><small><?php echo esc_html($horizon); ?>日先</small></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cards as $c) :
              $tc = $trend_class($c->trend);
              $fc_dir = $c->forecast_dir ?: 'flat';
              $search = mb_strtolower($c->name . ' ' . $c->code . ' ' . $c->grade);
            ?>
            <tr class="tmf-souba__row"
                data-name="<?php echo esc_attr($search); ?>"
                data-trend="<?php echo esc_attr($tc); ?>"
                data-price="<?php echo esc_attr((int)$c->price); ?>"
                data-fc="<?php echo esc_attr((float)$c->forecast_pct); ?>"
                data-sort-name="<?php echo esc_attr(mb_strtolower($c->name)); ?>"
                data-code="<?php echo esc_attr($c->code); ?>"
                data-series="<?php echo esc_attr($c->series); ?>">
              <td class="col-card">
                <div class="tmf-souba__card">
                  <div class="tmf-souba__thumb">
                    <?php if ($c->image) : ?>
                      <img src="<?php echo esc_url($c->image); ?>" alt="<?php echo esc_attr($c->name); ?>" loading="lazy">
                    <?php else : ?><span>◆</span><?php endif; ?>
                  </div>
                  <div>
                    <span class="tmf-souba__name"><?php echo esc_html($c->name); ?></span>
                    <span class="tmf-souba__meta"><?php echo esc_html($c->code); ?><?php if ($c->grade) : ?> ・ <?php echo esc_html(strtoupper($c->grade)); ?><?php endif; ?></span>
                  </div>
                </div>
              </td>
              <td class="col-price">
                <span class="tmf-souba__price"><?php echo esc_html(tmf_format_price($c->price)); ?></span>
              </td>
              <td class="col-trend">
                <span class="tmf-badge tmf-badge--<?php echo esc_attr($tc); ?>"><?php echo esc_html($c->trend ?: '—'); ?></span>
                <span class="tmf-souba__delta <?php echo $c->d7 >= 0 ? 'up' : 'down'; ?>"><?php echo ($c->d7 >= 0 ? '+' : '') . esc_html($c->d7); ?>%</span>
              </td>
              <td class="col-chart">
                <canvas class="tmf-spark" width="120" height="40" aria-hidden="true"></canvas>
              </td>
              <td class="col-fc">
                <span class="tmf-souba__fc tmf-souba__fc--<?php echo esc_attr($fc_dir); ?>">
                  <?php echo esc_html(tmf_format_price($c->forecast)); ?>
                </span>
                <span class="tmf-souba__fcpct tmf-souba__fc--<?php echo esc_attr($fc_dir); ?>">
                  <?php echo $fc_dir === 'up' ? '▲' : ($fc_dir === 'down' ? '▼' : '→'); ?>
                  <?php echo ($c->forecast_pct >= 0 ? '+' : '') . esc_html($c->forecast_pct); ?>%
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="tmf-souba__empty" id="tmf-souba-empty" hidden>該当するカードが見つかりませんでした。</p>
      </div>

      <p class="tmf-souba__note">
        ※ 予想相場は過去の値動き（線形回帰・移動平均・傾向）から自動算出した参考値です。実際の買取価格は状態・在庫状況により変動します。正確な査定は
        <?php if (tmf_opt('tmf_line')) : ?><a href="<?php echo esc_url(tmf_opt('tmf_line')); ?>" target="_blank" rel="noopener">LINE査定</a><?php else : ?>店頭<?php endif; ?>
        をご利用ください。
      </p>

    <?php endif; ?>

    <?php
    // ページ本文（任意の追記説明）
    while (have_posts()) : the_post();
      if (trim(get_the_content()) !== '') {
        echo '<div class="tmf-content reveal" style="max-width:820px;margin:60px auto 0">';
        the_content();
        echo '</div>';
      }
    endwhile;
    ?>
  </div>
</div>
<?php get_footer(); ?>
