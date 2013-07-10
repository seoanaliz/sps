<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.07.13
 * Time: 11:05
 * To change this template use File | Settings | File Templates.
 */
class UpdateBadooUsers extends BadooParser
{
    const PARSE_INTERVAL_SECONDS = 86400;

    public function Execute() {
        $BadooUsers = $this->getUsersRange();
        $badooUsersIds = array_keys( $BadooUsers );
        $FailedUsers = array();

        $userProfiles = $this->multiget( $badooUsersIds );
        foreach( $userProfiles as $id => $profilePage ) {
            if ( !$this->parseProfile( $BadooUsers[$id], $profilePage )) {
                $FailedUsers[$id] = $BadooUsers[$id];
//                unset( $BadooUsers[$id]);
            }
        }

        //todo обработка неудачных id

        BadooUserFactory::UpdateRange( $BadooUsers );
    }

    public function getUsersRange() {
        $search = array(
            'timestampLE' => time() - self::PARSE_INTERVAL_SECONDS
        );
        return BadooUserFactory::Get( $search );
    }

}
