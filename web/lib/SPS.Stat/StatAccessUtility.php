<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.05.13
 * Time: 18:16
 * To change this template use File | Settings | File Templates.
 */
class StatAccessUtility
{
    /**
     *@var $_instance StatAccessUtility
    */
    protected static $_instance;
    protected $rules_array = array();
    /**
     * @var $user Author
     */
    protected $user = null;
    protected $source;

    private function __construct(){}

    public static function GetInstance( $vk_id )
    {
        if ( self::$_instance === null )
            self::$_instance = new self;
       // self::$_instance->SetUser( $vk_id );
        self::$_instance->GetRules( $vk_id );

        return self::$_instance;
    }

    public function SetUser( $id )
    {
        $user = AuthorFactory::GetOne( array( 'vkId' => $id ));
        if(!empty($user)) {
            $this->user = $user;
        }
    }

    protected function GetRules( $vk_id )
    {

        $suaf = StatAuthorityFactory::Get( array( 'user_id' => $vk_id ));

        foreach( $suaf as $access_rule ) {
            self::$_instance->rules_array[$access_rule->source] = $access_rule->rank;
        }

    }

    public static function HasAccessToPrivateGroups( $vk_id, $source )
    {
        $acessUtility = self::GetInstance($vk_id);
        $res = false;
        if( isset( $acessUtility->rules_array[ $source ])) {
            $res = in_array($acessUtility->rules_array[ $source ],
                        array(
                            StatAuthority::STAT_ROLE_ADMIN,
                            StatAuthority::STAT_ROLE_EDITOR
                        )
                   );
        }
        return $res;
    }

    public static function  CanEditGlobalGroups($vk_id, $source) {
        $acessUtility = self::GetInstance($vk_id);
        $res = false;
        if(  isset( $acessUtility->rules_array[ $source ])) {
            $res = in_array($acessUtility->rules_array[ $source ],
                array(
                    StatAuthority::STAT_ROLE_ADMIN,
                    StatAuthority::STAT_ROLE_EDITOR
                )
            );
        }
        return $res;
    }

    public static function  CanManageGlobalGroups($vk_id, $source) {
        $acessUtility = self::GetInstance($vk_id);
        $res = false;
        if( isset( $acessUtility->rules_array[ $source ])) {
            $res = in_array($acessUtility->rules_array[ $source ],
                array(
                    StatAuthority::STAT_ROLE_ADMIN
                )
            );
        }
        return $res;
    }

    public static function GetRankInSource( $vk_id, $source )
    {
        if( !$vk_id ) {
            return StatAuthority::STAT_ROLE_GUEST;
        }

        $acessUtility = self::GetInstance($vk_id);
        if( isset( $acessUtility->rules_array[ $source ]))
            return $acessUtility->rules_array[ $source ];

        return StatAuthority::STAT_ROLE_USER;
    }


}
