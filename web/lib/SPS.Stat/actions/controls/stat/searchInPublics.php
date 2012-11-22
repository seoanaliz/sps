<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 22.11.12
 * Time: 17:31
 * To change this template use File | Settings | File Templates.
 */

class searchInPublics
{
    public function Execute()
    {
        $search     =   Request::getString( 'search' );

        die( ObjectHelper::ToJSON( array( 'response' => StatPublics::search_public( $search ))));

    }

}
