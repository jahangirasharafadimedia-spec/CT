<?php

$banner_image = get_sub_field('banner_image_full_width_banner');
$banner_url = get_sub_field('banner_url_full_width_banner');
if($banner_image){
 ?>
<tr>
	<td align="center">
		<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
			<tbody>
				<tr>
					<td style="width: 600px;">
						<a href="<?= $banner_url; ?>">
							<img src="<?= $banner_image; ?>" style="width: 600px;">
						</a>
					</td>

				</tr>
			</tbody>
		</table>
	</td>
</tr>
<?php } ?>