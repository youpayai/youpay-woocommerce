<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" >

	<h1>YouPay Login</h1>
	<input type="hidden" name="action" value="process_youpay_login">

	<div>
		<label for="email">Email:</label>
		<input type="text" id="email" name="email" />
	</div>

	<div>
		<label for="password">Password:</label>
		<input type="text" id="password" name="password" />
	</div>

	<button type="submit" class="btn btn-primary">Login</button>
</form>
