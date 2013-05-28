<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.05.13
 * Time: 18:16
 * To change this template use File | Settings | File Templates.
 */
class StatUserAccessUtility
{
    /**
     *@var $_instance StatUserAccessUtility
    */
    protected static $_instance;
    protected $rules_array = array();
    /**
     * @var $user StatUser
     */
    protected $user;
    protected $source;

    private function __construct(){}

    public static function GetInstance( $vk_id, $idFromVk = true )
    {
        if ( self::$_instance === null )
            self::$_instance = new self;
        self::$_instance->SetUser( $vk_id, $idFromVk);
        self::$_instance->GetRules( $vk_id );

        return self::$_instance;
    }

    public function SetUser( $id, $idFromVk = true )
    {
        if( $id ) {
            if( $idFromVk ) {
                self::$_instance->user = StatUserFactory::GetOne( array( 'user_id' => $id ));
            } else {
                self::$_instance->user = StatUserFactory::GetOne( array( 'id' => $id ));
            }
        }
    }

    protected function GetRules( $user_id )
    {
        if ( $user_id ) {
            $suaf = StatUsersAuthorityFactory::Get( array( 'user_id' => $this->user->id ));
            foreach( $suaf as $access_rule ) {
                self::$_instance->rules_array[$access_rule->source] = $access_rule->rank;
            }
        }
    }

    public static function HasAccessToPrivateGroups( $vk_id, $source )
    {
        $acessUtility = self::GetInstance($vk_id);
        $res = false;
        if( $acessUtility->user && isset( $acessUtility->rules_array[ $source ])) {
            $res = in_array($acessUtility->rules_array[ $source ],
                        array(
                            StatUsersAuthority::STAT_ROLE_ADMIN,
                            StatUsersAuthority::STAT_ROLE_EDITOR
                        )
                   );
        }
        return $res;
    }

    public static function  CanEditGlobalGroups($vk_id, $source) {
        $acessUtility = self::GetInstance($vk_id);
        $res = false;
        if( $acessUtility->user && isset( $acessUtility->rules_array[ $source ])) {
            $res = in_array($acessUtility->rules_array[ $source ],
                array(
                    StatUsersAuthority::STAT_ROLE_ADMIN,
                    StatUsersAuthority::STAT_ROLE_EDITOR
                )
            );
        }
        return $res;
    }

    public static function GetRankInSource( $vk_id, $source)
    {
        $acessUtility = self::GetInstance($vk_id);
        if( $acessUtility->user && isset( $acessUtility->rules_array[ $source ]))
            return $acessUtility->rules_array[ $source ];
        return false;
    }


}
