<?php

$fields = get_field_objects(isset($_GET['post']) ? $_GET['post'] : null);
if (!$fields) {
	echo "Unable to view. Please try again.";
}
function getWeekdaya($date)
{
	return date('l', strtotime($date));
}
function limit_text($text, $limit)
{
	$limit = 15;
	if (str_word_count($text, 0) > $limit) {
		$words = str_word_count($text, 2);
		$pos = array_keys($words);
		$text = substr($text, 0, $pos[$limit]) . '...';
	}
	return $text;
}
// echo '<pre>';
// print_r($fields );
$utm_campaign = date('F') . (int) ceil((new Datetime())->format('d') / 7);

$headers = array('Content-Type: text/html; charset=UTF-8');
$subject_prepared = "Activate subscription for Communications Today";
$ab = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
// echo file_get_contents('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$body = '';
//$body .=file_get_contents($ab);
//wp_mail('ravipratapsingh96@gmail.com', $subject_prepared, $body, $headers);

?>
<!DOCTYPE html>
<html>

<head>
	<title></title>
	<style type="text/css">
		.button {
			border: 2px solid #1F93AC;
			padding: 5px;
			font-family: 'Roboto Condensed', sans-serif;
			color: #000;
			font-weight: 500;
			margin-right: 2px;
			font-size: 15px;
			text-decoration: none;
			margin-bottom: 10px !important;
		}

		a,
		td,
		p {
			font-size: 10px;
		}
	</style>
	<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500&family=Roboto&display=swap" rel="stylesheet">
</head>

<body>
	<table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="white">
		<!-- START HEADER/BANNER -->

		<tbody>
			<?php if (isset($fields['leader_board_banner']['value']) && !empty($fields['leader_board_banner']['value'])) { ?>
			<tr>
				<td align="center">
					<table class="col-600" width="660" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td style="width: 660px;">
									<a href="<?php echo isset($fields['leader_borad_banner_url']['value']) ? $fields['leader_borad_banner_url']['value'] : '#'; ?>">
										<img src="<?php echo $fields['leader_board_banner']['value']; ?>"
											style="width: 660px;">
									</a>
								</td>

							</tr>
							<tr>
								<td height="15"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php } ?>
			<?php if (isset($fields['top_banner']['value']) && !empty($fields['top_banner']['value'])) { ?>
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td style="width: 600px;">
                                   <a href="<?php echo isset($fields['top_banner_url']['value']) ? $fields['top_banner_url']['value'] : '#'; ?>">
									<img src="<?php echo $fields['top_banner']['value']; ?>" style="width: 600px;">
	</a>
								</td>

							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php } ?>

			<!-- END HEADER/BANNER -->

			<?php /*		<tr style="background-color:#0578BB">
							<td align="center">
								<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
									<tbody>
										<tr>
											<td width="440">
												<a href="https://www.communicationstoday.co.in/">
													<img src="<?= $fields['left_logo_with_detail']['value'] ?>" width="100%">
												</a>
											</td>
											<td align="center" style="" width="160">
												<span style="font-size:20px;"><strong style="font-size:20px;font-family: 'Roboto Condensed', sans-serif;color:#fff; "><?php  echo $fields['date']['value']; ?></strong></span><br>
												<span style="font-size:18px;font-family: 'Roboto Condensed', sans-serif; color:#fff "><?php  echo getWeekdaya($fields['date']['value']); ?></span>	
											</td>
										</tr>
									</tbody></table>
								</td>
							</tr> 
					*/?>
			<!-- Headlines-->



			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td height="5"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>



			<!-- Headlines with image -->
			<tr>
				<td height="8">

				</td>
			</tr>
			<?php if (isset($fields['header_image']['value']) && !empty($fields['header_image']['value'])) { ?>
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td style="width: 600px;">

									<img src="<?php echo $fields['header_image']['value']; ?>" style="width: 600px;">

								</td>

							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php } ?>


			<!-- Headlines-->
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td height="5"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>



			<!-- Headlines with image -->
			<tr>
				<td height="8">

				</td>
			</tr>
			<?php

			foreach ($fields['posts']['value'] as $data) {

				?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
							style="">
							<tbody>
								<tr>
									<td style="width: 600px;">
										<a
											href="<?php echo esc_url(get_permalink($data->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
											<?= get_the_post_thumbnail( $data->ID, array( 600, 0 ) ); ?>
										</a>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
							style="">
							<tbody>
								<tr>
									<td style="width: 600px;">
										<a href="<?php echo esc_url(get_permalink($data->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>"
											style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;">
											<?php echo $data->post_title; ?>
										</a>
										<p
											style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444;line-height:24px; font-weight: 300;display:block; margin: 0">
											<?php echo limit_text($data->post_content, 17); ?>
										</p>


									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td height="5"></td>
				</tr>
				<tr>
					<td height="5"></td>
				</tr>

				<!-- with image -->



			<?php } ?>
			<?php if (isset($fields['sponsor_heading_image']['value']) && !empty($fields['sponsor_heading_image']['value'])) { ?>
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td style="width: 600px;">

									<img src="<?php echo $fields['sponser_heading_image']['value']; ?>"
										style="width: 600px;">

								</td>

							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<!-- Headlines-->
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td height="5"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php } ?>


			<!-- Headlines with image -->
			<tr>
				<td height="8">

				</td>
			</tr>
			<?php if (isset($fields['sponsor_image']['value']) && !empty($fields['sponsor_image']['value'])) { ?>
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0"
						style="">
						<tbody>
							<tr>
								<td style="width: 600px;">

									<img src="<?php echo $fields['sponsor_image']['value']; ?>" style="width: 600px;">

								</td>

							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php } ?>



		</tbody>
	</table>
</body>

</html>