<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    define( 'TABLE_PUBLICS_POINTS', 'gr50k');
    define( 'T_PUBLICS_LIST',       'publs50k');
    define( 'T_PUBLICS_RELS',       'publ_rels_names');

    define('TABLE_STAT_USERS', 'stat_users');

    define('ADMIN_RANK', 2);

    class StatPublics
    {

        public static function getQuantityLimits()
        {
            $sql = 'SELECT MIN(quantity), MAX(quantity) FROM ' . TABLE_STAT_GROUPS ;#. '  WHERE quantity >40000';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $ds->Next();
            return array(
                'min_quantity'  =>  $ds->getValue('min', TYPE_INTEGER),
                'max_quantity'  =>  $ds->getValue('max', TYPE_INTEGER)
            );
        }

    }
?>
