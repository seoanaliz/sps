<?php

include_once('BadooParser.php');

class UpdateBadooUsers extends BadooParser
{
    const PARSE_INTERVAL_SECONDS = 8640;

    public function Execute() {
        $BadooUsers = $this->getUsersRange();

        $badooUsersIds = array_keys( $BadooUsers );
        $FailedUsers = array();
        $now = time();
        $shortNames = array();

        $userProfiles = $this->multiget( $badooUsersIds );
        foreach( $userProfiles as $id => $profilePage ) {
            if ( !$this->parseProfile( $BadooUsers[$id], $profilePage )) {
                $FailedUsers[$id] = $BadooUsers[$id];
                $BadooUsers[$id]->updated_at = $now;
            }
        }

        foreach ( $FailedUsers as $FailedUser ) {
            if( $FailedUser->shortname ) {
                $shortNames[] = $FailedUser->shortname;
            }
        }

        $userProfiles = $this->multiget( $shortNames, true );
        foreach( $userProfiles as $id => $profilePage ) {
            $this->parseProfile( $BadooUsers[$id], $profilePage );
        }

        BadooUserFactory::UpdateRange( $BadooUsers );
    }

    /** @return BadooUser[]  */
    public function getUsersRange() {
        $search = array(
            'updated_atLE' => time() - self::PARSE_INTERVAL_SECONDS,
            'pageSize'     => 1000
        );

        return BadooUserFactory::Get( $search );
    }

}
