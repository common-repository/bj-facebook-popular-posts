<?php/*Copy this file to your theme folder and customize as you like.Do not edit this file directly. It will be overwritten on upgrades.*/?><?php if ( $query->have_posts() ) : ?>	<ol class="bj-fbpp-list">		<?php while ( $query->have_posts() ) : $query->the_post(); ?>			<?php			/*			This will give you the number of shares:			$num_shares = intval ( get_post_meta( get_the_ID(), '_bj_fb_shares', true ) );			*/			?>			<li class="bj-fbpp-item">				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( the_title() ); ?>" class="bj-fbpp-link">					<span class="bj-fbpp-title"><?php the_title(); ?></span>				</a>			</li>		<?php endwhile; ?>	</ol><?php endif; ?>