<?php
/**
 * Home section: Statement.
 *
 * A confident brand statement built as a short "message thread": two mono
 * panels (a common objection, then the reply) leading into an accent card
 * carrying the headline statement and a framed, muted video.
 *
 * Styled as instrument panels rather than chat bubbles - hairline borders,
 * corner ticks, bracketed indices and a meta rule - over a grained field.
 * Self-contained — hard-coded copy + bundled media (no ACF).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_stmt_video  = LE_URI . '/assets/videos/typing-macbook.mp4';
$le_stmt_poster = LE_URI . '/assets/videos/typing-macbook-poster.jpg';
?>
<section class="section section--statement" aria-label="<?php esc_attr_e( 'How we build', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="statement" data-reveal>

			<div class="statement__head">
				<span class="eyebrow statement__eyebrow"><?php esc_html_e( 'Straight talk', 'lewisedward' ); ?></span>
				<span class="statement__rule" aria-hidden="true"></span>
				<span class="pulse-dot" aria-hidden="true"></span>
			</div>

			<div class="statement__thread">

				<article class="msg msg--in">
					<header class="msg__meta">
						<span class="msg__index">[01]</span>
						<span class="msg__from"><?php esc_html_e( 'Somebody', 'lewisedward' ); ?></span>
						<span class="msg__rule" aria-hidden="true"></span>
						<span class="msg__time"><?php esc_html_e( 'Now', 'lewisedward' ); ?></span>
					</header>
					<p class="msg__text"><?php esc_html_e( '“It’s just a WordPress site, right?”', 'lewisedward' ); ?></p>
				</article>

				<article class="msg msg--reply">
					<header class="msg__meta">
						<span class="msg__index">[02]</span>
						<span class="msg__from"><?php esc_html_e( 'Lewis Edward', 'lewisedward' ); ?></span>
						<span class="msg__rule" aria-hidden="true"></span>
						<span class="msg__time"><?php esc_html_e( 'Since 2011', 'lewisedward' ); ?></span>
					</header>
					<p class="msg__text"><?php esc_html_e( 'Not the way we build it.', 'lewisedward' ); ?></p>
				</article>

				<article class="msg msg--accent">
					<header class="msg__meta">
						<span class="msg__index">[03]</span>
						<span class="msg__from"><?php esc_html_e( 'Lewis Edward', 'lewisedward' ); ?></span>
						<span class="msg__rule" aria-hidden="true"></span>
						<span class="msg__time"><?php esc_html_e( 'Always', 'lewisedward' ); ?></span>
					</header>
					<h2 class="msg__statement"><?php esc_html_e( 'Design, custom code & AI — engineered in London, shipped to brands worldwide.', 'lewisedward' ); ?></h2>
					<div class="msg__frame">
						<video class="msg__video" autoplay loop muted playsinline preload="metadata" poster="<?php echo esc_url( $le_stmt_poster ); ?>" aria-hidden="true" tabindex="-1">
							<source src="<?php echo esc_url( $le_stmt_video ); ?>" type="video/mp4" />
						</video>
					</div>
				</article>

			</div>

		</div>
	</div>
</section>
