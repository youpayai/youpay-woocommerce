<?php

namespace MyWorkWPMU\includes;


class MyWorkCli extends \WP_CLI_Command
{
    /**
     * Give Power User Status to User
     *
     * <user-id>
     * : ID of the user
     */
    public function powers($args = [])
    {
        $user = get_userdata($args[0]);
        if ($user === false) {
            \WP_CLI::error('Could not find user: ' . $args[0]);
            return;
        }
        update_user_meta( $args[0], 'power_user', true);
        \WP_CLI::success('User now has powers');
    }
}