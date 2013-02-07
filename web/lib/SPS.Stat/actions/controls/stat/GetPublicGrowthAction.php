<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.11.12
 * Time: 12:06
 * To change this template use File | Settings | File Templates.
 */
class GetPublicGrowthAction
{
    /**
     * Constructor
     */
    public function execute() {
        $creator_id     =   Request::getInteger( 'id' ) ? Request::getInteger( 'id' ) : '';
        $this->get_publics_growth( $creator_id );
//       print_r( $this->get_average_barters( '2012-11-30' ));
    }

    protected function get_publics_growth( $creator_id )
    {
        $type = 'all_publics';
        if ( $creator_id ) {
            $type = "vld_publics";
        }
        $sql = 'SELECT * FROM stat_our_auditory  WHERE type = @type ORDER BY point_date ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetString( '@type', $type );
        $ds  = $cmd->Execute();
        $res = array();
//        echo '>>>>>>>>>>>>>>>>>' . microtime(1) . '<br>';
        while( $ds->Next()) {
            $date         =  $ds->GetString( 'point_date' );
            $res[ $date ] =  array(
                'unique_users'  =>  $ds->GetString( 'unique_users' ),
                'all_users'     =>  $ds->GetString( 'all_users' ),
            );
        }
        $res[date('Y-m-d')] = array(
            'unique_users'  =>  '-',
            'all_users'     =>  '-',
        );
//        echo '<<<<<<<<<<<<<<<<<' . microtime(1) . '<br>';

        if ( count( $res ) <= 1 )
            return $res;
        $prev_unq   = 0;
        $prev_ununq = 0;
        foreach( $res as $date => &$data ) {
            if( $data['all_users'] != '-' ) {
                $data['change_unq']     = $prev_unq   ? $data['unique_users'] - $prev_unq   : 0;
                $data['change_unuqunq'] = $prev_ununq ? $data['all_users']    - $prev_ununq : 0;
                $prev_unq   = $data['unique_users'];
                $prev_ununq = $data['all_users'];
            } else {
                $data['change_unq'] = '-';
                $data['change_unuqunq'] = '-';
            }
            $barter = $this->get_average_barters( $date, $creator_id );
            $data['barters_vis']  = $barter['total_vis'];
            $data['barters_subs'] = $barter['total_sub'];
            $data['barters']      = $barter['total_count'] . '(' . $barter['rel_count']. ')';
        }

        Response::setArray( 'stat_summary' , array_reverse( $res ));
    }

    private function get_average_barters( $date, $creator_id = '' )
    {

        $search_line =  $creator_id ? ' AND  creator_id = @creator_id ' : '';

        $sql = 'SELECT
                    *
                FROM
                    barter_events
                WHERE
                    posted_at::date = date @date
                    AND status in (4,6)
                    AND post_id IS NOT NULL
                    AND start_visitors IS NOT NULL
                    AND end_visitors IS NOT NULL
                    ' . $search_line . '
                ORDER BY detected_at
                    ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetString( '@date', $date );
            $cmd->SetString( '@creator_id', $creator_id );
        $ds  = $cmd->Execute();
        $vis  = 0;
        $subs = 0;
        $i    = 0;

        $publics_posts_data = array();
        while( $ds->Next()) {
            ++$i;
            $start_visitors = $ds->GetInteger( 'start_visitors' );
            $end_visitors   = $ds->GetInteger( 'end_visitors' );
            $start_subscribers = $ds->GetInteger( 'start_subscribers' );
            $end_subscribers   = $ds->GetInteger( 'end_subscribers' );
            $a = $ds->GetDateTime( 'detected_at');
            if ( $a )
                $a = $a->getTimestamp();
            else
                $a = $ds->GetDateTime( 'posted_at')->getTimestamp();
            $b = $ds->GetDateTime( 'deleted_at');
            $b = !empty( $b ) ? $b->getTimestamp() : $a + 3600;
            $publics_posts_data[ $ds->GetInteger( 'target_public' )][] = array(
                'start'     =>  $a,
                'stop'      =>  $b,
                'vis_sto'   =>  $end_visitors,
                'sub_sto'   =>  $end_subscribers,
                'vis_sta'   =>  $start_visitors,
                'sub_sta'   =>  $start_subscribers
            );
        }
        $res          = $this->find_crosses( $publics_posts_data );
        $res['total_count'] = $i;
        return $res;
    }

    private function find_crosses( $public_time_array )
    {
        foreach( $public_time_array as &$public  ) {

            $count = count( $public );
            for( $i = 0; $i <= $count - 2 ; $i++ ) {
                if( !isset ( $public[$i] ) || empty( $public[$i]))
                    continue;
                for( $j = $i + 1; $j < $count; $j++ ) {
                    if( !isset ( $public[$j] ))
                        continue;
                    if( $public[$i]['start'] <= $public[$j]['start'] &&
                                $public[$j]['start'] <= $public[$i]['stop']) {
                        $public[$i]['stop']    = $public[$j]['stop'];
                        $public[$i]['vis_sto'] = $public[$j]['vis_sto'];
                        $public[$i]['sub_sto'] = $public[$j]['sub_sto'];
                        unset( $public[$j] );
                    }
                }
            }
        }
        unset( $public );
        $res = array( 'total_vis' => 0, 'total_sub' => 0 );
        $i = 0;
        foreach( $public_time_array as &$public_f ) {
            foreach( $public_f as $event ) {

                $res['total_vis'] += $event['vis_sto'] - $event['vis_sta'];
                $res['total_sub'] += $event['sub_sto'] - $event['sub_sta'];
                ++$i;
            }
        }
        $res['rel_count'] = $i;
        return $res;
    }
}
?>