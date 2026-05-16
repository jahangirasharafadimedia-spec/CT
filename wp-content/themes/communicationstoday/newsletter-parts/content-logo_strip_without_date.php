<?php

$logo_image = get_sub_field('logo_image');
$logo_image_link = get_sub_field('logo_image_link');
if($logo_image){
 ?>
<tr>
	<td align="center">
		<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
			<tbody>
				<tr>
					<td style="width: 600px;">
						<a href="<?= $logo_image_link; ?>">
							<img src="<?= $logo_image; ?>" style="width: 600px;">
						</a>
					</td>

				</tr>
			</tbody>
		</table>
	</td>
</tr>
<?php } ?>