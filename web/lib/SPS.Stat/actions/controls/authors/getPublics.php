<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */
class getPublics
{
    public function execute()
    {
        error_reporting(0);

        $publics = TargetFeedFactory::Get();
        foreach ( $publics as $public ) {
            if( $public->type != 'vk'             ||
                $public->externalId ==  25678227  ||
                $public->externalId ==  26776509  ||
                $public->externalId ==  43503789  ||
                $public->externalId ==  346191    ||
                $public->externalId ==  33704958  ||
                $public->externalId ==  38000521  ||
                $public->externalId ==  1792796   ||
                $public->externalId ==  27421965  ||
                $public->externalId ==  34010064  ||
                $public->externalId ==  25749497  ||
                $public->externalId ==  38000555  ||
                $public->externalId ==  35807078  ||
                $public->externalId ==  25817269 )
                continue;
            $res[] = array(
                'group_id'  => $public->targetFeedId,
                'name'      => $public->title );
        }

        die( ObjectHelper::ToJSON( array( 'response' => array( 'groups' => $res ))));
    }
}
