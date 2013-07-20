<?php
include_once('BadooParser.php');

class GetNewBadooUsers extends BadooParser
{
    const START_USER_ID = 330535850;
    const USERS_RANGE   = 1000;

    /** key is  a badooUser id, value - shortname */
    public $falseArray = array();

    public function Execute() {
        $lastUserId = $this->getLastUser();
        $usersIds = range( $lastUserId + 1, $lastUserId + self::USERS_RANGE);

        $start = microtime(1);
        $this->getUsers( $usersIds );

        if( !empty($this->falseArray)) {
            $userShortNames = array_values( $this->falseArray);
            $this->getUsers( $userShortNames, true );
        }
        echo round( microtime(1) - $start , 2 ) . '<br>';

    }

    public function getLastUser() {
        $LastUser =  BadooUserFactory::Get( array('pageSize' => 1), array( BaseFactory::OrderBy => ' "external_id" DESC '));
        if( !empty( $LastUser )) {
            return current($LastUser)->external_id;
        } else {
            return self::START_USER_ID;
        }
    }

    public function addUserFromProfile( $page ) {
        $BadooUser = new BadooUser();

        if( !preg_match('/pf_hd_h\".id=\"uid(\d+?)\">(.+?)</', $page, $matches) || count($matches) != 3 ) {
            throw new Exception( ' Can\'t read user page');
        }
        $externalId = $matches[1];
        list($name, $age)  = explode(',', $matches[2]);

        $city = $country = null;
        if ( preg_match('/ic_dstc-hd\"><\/i>(.+?)</', $page, $matches)) {
            list($city, $country) = explode( ',', $matches[1]);
        }

        $BadooUser->external_id  = $externalId;
        $BadooUser->name        = $name;
        $BadooUser->age         = $age;
        $BadooUser->city        = $city;
        $BadooUser->country     = $country;
        $BadooUser->registered_at= time();

        return $BadooUser;
    }


    public function getUsers( $usersIds, $shortname = false ) {
        $userProfiles = $this->multiget( $usersIds, $shortname );

        foreach( $userProfiles as $id => $profilePage ) {
            if ( !strpos( $profilePage, 'class="page_profile"')) {
                //либо юзер удален, либо заменил id именем, либо...
                if( strpos($profilePage, 'document moved')) {
                    $shortname = $this->getShortName( $profilePage );
                    if( $shortname ) {
                        $this->falseArray[$id] = $shortname;
                    }
                }

                continue;
            }

            try {
                $BadooUser = $this->addUserFromProfile( $profilePage );
            } catch ( Exception $e ) {
                print_r($e->getMessage(). ' ' . $id );
                echo '<br><br><br>';
                continue;

            }
            if ( !$this->parseProfile( $BadooUser, $profilePage )) {
                //todo log
                echo 'не удалось спарсить профиль ', $BadooUser->external_id, '<bd>';
                continue;
            }
            BadooUserFactory::Add($BadooUser);

        }
    }
}
