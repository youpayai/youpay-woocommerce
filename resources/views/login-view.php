<div class="w-full h-full">
	<div class="p-8 mx-auto w-4/5 lg:w-1/4 text-gray-800 bg-gray-300 rounded-lg mt-10 lg:mt-20 shadow-inner">

		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="process_youpay_login">

			<h2 class="text-xl m-0 text-gray-800 text-center p-4">YouPay Login</h2>
			<label for="email" class="text-xs text-gray-800">Username</label>
			<input id="email" name="email"
				   class="bg-transparent border-b m-auto block border-gray-800 w-full mb-6 text-gray-700 pb-1"
				   type="text"
				   placeholder=""/>
			<label id="password" class="text-xs text-gray-800">Password</label>
			<input id="password" name="password" type="password" placeholder=""
				   class="bg-transparent border-b m-auto block border-gray-500 w-full mb-6 text-grey-700 pb-1" />
			<input class="shadow-lg mx-auto block w-1/3 pt-3 pb-3 text-white bg-indigo-500 hover:bg-indigo-400 rounded-full cursor-pointer border-none" type="submit" value="Login">
		</form>
	</div>
</div>
