<?php
$utm_campaign = date('F').(int) ceil((new Datetime())->format('d') / 7);
$featured_posts = get_sub_field('select_posts');
if( $featured_posts ): ?>
    <ul>
    <?php foreach( $featured_posts as $post ): 

        // Setup this post for WP functions (variable must be named $post).
        setup_postdata($post);
$post_id = get_the_ID();
         ?>
       <tr>
					<td height="8">

					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td style="width: 600px;">
										<a href="<?php echo esc_url( get_permalink($post_id)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
											<?= get_the_post_thumbnail( $post_id, array( 600) ); ?>												</a>
										</td>

									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<tr>
							<td align="center">
								<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
									<tbody>
										<tr>
											<td style="width: 600px;">
												<a href="<?php echo esc_url( get_permalink($post_id)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo get_the_title();  ?></a>
												<p  style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444;line-height:24px; font-weight: 300;display:block; margin: 0"><?php echo limit_text(get_the_content(),17); ?></p>


											</td>

										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tr>
					<tr>
						<td height="5"></td>
					</tr>
    <?php endforeach; ?>
    </ul>
    <?php 
    // Reset the global post object so that the rest of the page works correctly.
    wp_reset_postdata(); ?>
<?php endif; ?>