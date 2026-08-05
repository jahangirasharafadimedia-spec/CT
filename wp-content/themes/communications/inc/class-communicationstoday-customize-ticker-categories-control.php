<?php
/**
 * Customizer control: multiple category checkboxes for the news ticker.
 *
 * @package Communicationstoday
 */

if ( ! class_exists( 'Communicationstoday_Customize_Ticker_Categories_Control' ) && class_exists( 'WP_Customize_Control' ) ) {

	/**
	 * Multi-select categories via checkboxes (stored as comma-separated term IDs).
	 */
	class Communicationstoday_Customize_Ticker_Categories_Control extends WP_Customize_Control {

		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'communicationstoday_ticker_categories';

		/**
		 * Render checkboxes for each category.
		 */
		public function render_content() {
			if ( empty( $this->label ) && empty( $this->description ) ) {
				return;
			}

			$selected = array();
			$value    = $this->value();
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$selected = array_map( 'absint', explode( ',', $value ) );
			}
			$selected = array_filter( array_unique( $selected ) );

			$categories = get_categories(
				array(
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
			?>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<div class="communicationstoday-ticker-categories-list">
				<?php if ( empty( $categories ) ) : ?>
					<p><?php esc_html_e( 'No categories found.', 'communicationstoday' ); ?></p>
				<?php else : ?>
					<?php foreach ( $categories as $category ) : ?>
						<label class="communicationstoday-ticker-category-option">
							<input
								type="checkbox"
								value="<?php echo esc_attr( (string) (int) $category->term_id ); ?>"
								<?php checked( in_array( (int) $category->term_id, $selected, true ) ); ?>
							/>
							<?php echo esc_html( $category->name ); ?>
						</label>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
