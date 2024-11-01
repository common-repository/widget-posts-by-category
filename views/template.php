<section class="articles_posts-by-cat">
<?php if ( $posts->have_posts() ) { ?>
	<?php while ( $posts->have_posts() ) {
		$posts->the_post(); ?>
	<article class="posts-by-cat_article-<?php the_ID(); ?>">
		<h3>
			<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
				<?php the_title(); ?>
			</a>
		</h3>

		<p><?php the_excerpt(); ?></p>
	</article>
	<?php }
} else { ?>
	<p><?php _e( 'Nothing has been posted in the selected categories.', 'posts_by_cat_widget' ); ?></p>
<?php } ?>

</section>
