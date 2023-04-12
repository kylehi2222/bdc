<div id="bgc-form">
	<h3 class="title"><?php _e('Please enter your birth details below', 'bgc'); ?></h3>
	<div class="form-wrapper">
		<form action="<?php echo add_query_arg('bgc-generate', 1); ?>" method="post">
			<div class="input-field">
				<label for="year"><?php _e('Year', 'bgc'); ?></label>
				<select name="_year" id="year" required>
					<option value=""><?php _e('Type or Select', 'bgc'); ?></option>
					<?php for ($i = date('Y'); $i >= date('Y', strtotime('-150 years')); $i--) : ?>
						<option value="<?php echo $i; ?>" <?php selected($i, (isset($chart['date']) ? date('Y', strtotime($chart['date'])) : '')); ?>>
							<?php echo $i; ?>
						</option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="input-field">
				<label for="month"><?php _e('Month', 'bgc'); ?></label>
				<select name="_month" id="month" required>
					<option value=""><?php _e('Type or Select', 'bgc'); ?></option>
					<?php for ($i = 1; $i <= 12; $i++) : ?>
						<option value="<?php echo str_pad($i, 2, 0, STR_PAD_LEFT); ?>" <?php selected(str_pad($i, 2, 0, STR_PAD_LEFT), (isset($chart['date']) ? date('m', strtotime($chart['date'])) : '')); ?>>
							<?php echo date_i18n('F', mktime(0, 0, 0, $i, 10)); ?>
						</option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="input-field">
				<label for="day"><?php _e('Day', 'bgc'); ?></label>
				<select name="_day" id="day" required>
					<option value=""><?php _e('Type or Select', 'bgc'); ?></option>
					<?php for ($i = 1; $i <= 31; $i++) : ?>
						<?php $i = str_pad($i, 2, 0, STR_PAD_LEFT); ?>
						<option value="<?php echo $i; ?>" <?php selected($i, (isset($chart['date']) ? date('d', strtotime($chart['date'])) : '')); ?>>
							<?php echo $i; ?>
						</option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="grid-row margin-no">
				<div class="grid-6 padding-l">
					<div class="input-field">
						<label for="hour"><?php _e('Hour', 'bgc'); ?></label>
						<select name="_hour" id="hour" required>
							<option value=""></option>
							<?php for ($i = 0; $i <= 23; $i++) : ?>
								<?php $i = str_pad($i, 2, 0, STR_PAD_LEFT); ?>
								<option value="<?php echo $i; ?>">
									<?php echo $i; ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
				<div class="grid-6 padding-r">
					<div class="input-field">
						<label for="minutes"><?php _e('Minutes', 'bgc'); ?></label>
						<select name="_minutes" id="minutes" required>
							<option value=""></option>
							<?php for ($i = 0; $i <= 59; $i++) : ?>
								<?php $i = str_pad($i, 2, 0, STR_PAD_LEFT); ?>
								<option value="<?php echo $i; ?>">
									<?php echo $i; ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="input-field">
				<label for="location"><?php _e('Location', 'bgc'); ?></label>
				<input type="text" name="_location" id="location" data-api-key="<?php echo $api_key; ?>">
				<input type="hidden" name="_timezone" id="timezone">
			</div>
			<div class="buttons">
				<input type="submit" class="green-btn w-100" value="<?php _e('View Your Chart', 'bgc'); ?>">
			</div>
		</form>
	</div>
</div>
