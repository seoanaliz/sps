<?php

class GetNewBadooUsers extends BadooParser
{
    const START_USER_ID = 328209453;
    const USERS_RANGE   = 1000;

    public function Execute() {
        $lastUserId = $this->getLastUser();
        $usersIds = range( $lastUserId + 1, $lastUserId + 1 + self::USERS_RANGE);

        $userProfiles = $this->multiget( $usersIds );
        foreach( $userProfiles as $id => $profilePage ) {
            if ( !strpos( $profilePage, 'class="crt m_meet"')) {
                //todo сделать обработку имен, ошибок
                continue;
            }

            try {
                $BadooUser = $this->addUserFromProfile( $this->addUserFromProfile( $profilePage ));
            } catch ( Exception $e ) {
                //todo log
                continue;
            }
            if ( !$this->parseProfile( $BadooUser, $profilePage )) {
                //todo log
                continue;
            }
            BadooUserFactory::Add($BadooUser);
        }
    }

    public function getLastUser() {
        $LastUser =  BadooUserFactory::GetOne( null, array( BaseFactory::OrderBy => ' "externalId" DESC '));
        if( !empty( $LastUser )) {
            return $LastUser->externalId;
        } else {
            return self::START_USER_ID;
        }
    }

    public function addUserFromProfile( $page ) {
        $BadooUser = new BadooUser();

        if( !preg_match('/pf_hd_h\".id=\"uid(\d+?)\">(.+)</', $page, $matches) || count($matches) != 3 ) {
            throw new Exception( ' Can\'t read usert \\ pa');
        }
        $externalId = $matches[1];
        list($name, $age)  = explode(',', $matches[2]);

        $city = $country = null;
        if ( preg_match('/ic_dstc-hd\"><\/i>(.+?)</', $page, $matches)) {
            list($city, $country) = explode( ',', $matches[1]);
        }

        $BadooUser->externalId  = $externalId;
        $BadooUser->name        = $name;
        $BadooUser->age         = $age;
        $BadooUser->city        = $city;
        $BadooUser->country     = $country;
        $BadooUser->registeredAt= time();

        return $BadooUser;
    }

}
