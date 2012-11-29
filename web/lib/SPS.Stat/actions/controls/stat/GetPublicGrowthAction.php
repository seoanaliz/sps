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
        $this->get_publics_groth();
    }

    protected function get_publics_groth()
    {
        $sql = 'SELECT * FROM stat_our_auditory ORDER BY point_date';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $ds  = $cmd->Execute();
        $res = array();
        while( $ds->Next()) {
            $date         =  $ds->GetString( 'point_date' );
            $res[ $date ] =  array(
                'unique_users'  =>  $ds->GetString( 'unique_users' ),
                'all_users'     =>  $ds->GetString( 'all_users' ),
            );
        }
        if ( count( $res ) <= 1 )
            return $res;
        $prev_unq   = 0;
        $prev_ununq = 0;
        foreach( $res as $date => &$data ) {
            $data['change_unq']     = $prev_unq   ? $data['unique_users'] - $prev_unq   : 0;
            $data['change_unuqunq'] = $prev_ununq ? $data['all_users']    - $prev_ununq : 0;
            $prev_unq   = $data['unique_users'];
            $prev_ununq = $data['all_users'];

            $barter = $this->get_average_barters( $date );
            $data['barters_vis']  = $barter['vis'];
            $data['barters_subs'] = $barter['subs'];
            $data['barters']      = $barter['total'];
        }
        Response::setArray( 'stat_summary' ,$res );
    }

    private function get_average_barters( $date )
    {
        $sql = 'SELECT
                    *
                FROM
                    barter_events
                WHERE
                    posted_at::date = date @date
                    AND (status = 4 OR status = 5)
                    AND post_id IS NOT NULL';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetString( '@date', $date );
        $ds  = $cmd->Execute();
        $vis  = 0;
        $subs = 0;
        $i    = 0;
        while( $ds->Next()) {
            ++$i;
            $start_visitors = $ds->GetInteger( 'start_visitors' );
            $end_visitors   = $ds->GetInteger( 'end_visitors' );
            $start_subscribers = $ds->GetInteger( 'start_subscribers' );
            $end_subscribers   = $ds->GetInteger( 'end_subscribers' );
            if ( $start_subscribers && $end_subscribers )
                $subs += $end_subscribers - $start_subscribers;
            if ( $start_visitors && $end_visitors )
                $vis += $end_visitors - $start_visitors;
        }
        return array( 'subs' => $subs, 'vis' => $vis, 'total' => $i );
    }

}
?>