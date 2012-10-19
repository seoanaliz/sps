<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 16.10.12
 * Time: 11:42
 * To change this template use File | Settings | File Templates.
 */
Package::Load( 'SPS.Stat' );



class PublicsGrowFixer
{
    //по этим дням проходит разделение месяца на недели
    private $break_days = array( 1, 8, 15, 22 );

    public function Execute()
    {
        try {
            $publics = StatPublics::get_our_publics_list();
            foreach( $publics as $public ) {
                $this->get_all_posts( $public['id'], $public['sb_id'] );
            }
        } catch( Exception $e ) {
            echo $e -> getMessage();
        }
    }

    public function get_all_posts( $public_id, $public_sb_id, $stop_date = '' )
    {
        set_time_limit( 10000 );
        $collect_trig = 0;
        $offset = 0;
        $params = array(
            'owner_id' => '-' . $public_id,
            'count' =>  100,
            'access_token'  => $this->get_service_at()
        );
        $new_data_point = -1;
        $likes    = -1;
        $reposts  = 0;
        $comments = 0;
        while( true ) {
            $params['offset']   =   $offset;
            $posts = VkHelper::api_request( 'wall.get', $params, 0 );
            if( isset( $posts->error )) {
                echo '<br>++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>';
                    print_r( $posts);
                echo '<br>++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>';
                return false;
            }
            echo '<br>', count($posts), '<br>';
            sleep(0.5);
            unset( $posts[0] );
            if( count( $posts ) < 1 )
                return false;
            foreach( $posts as $post ) {
                if ( $post->date <= $stop_date )
                    return false;

                if( in_array( date( 'j', $post->date ), $this->break_days )
                    && wrapper::morning( $post->date ) != $new_data_point ) {
                        $collect_trig = 1;

                        $new_data_point = wrapper::morning( $post->date );
                        $stat = array(
                                'likes'    =>  $likes + 1,
                                'reposts'  =>  $reposts,
                                'comments' =>  $comments );
                        if( $likes != -1 )$this->save_point( $public_sb_id, $new_data_point, $stat );
                        echo  'saving data<br>';
                        echo  date( 'H:i d-m-Y', $new_data_point ),'<br>';
                        echo $likes . ' ' . $reposts . ' ' . $comments . ' <br>';
                        $likes    = -1;
                        $reposts  = 0;
                        $comments = 0;
                }

                if( !$collect_trig )
                    continue;

                $likes    += $post->likes->count;
                $reposts  += $post->reposts->count;
                $comments += $post->comments->count;
            }

            $offset += 100;
            echo '<br>new lap <br>';

        }
    }

    public static function get_service_at()
    {
        //todo создать пару служебных аппов, зарегать ботов на него, табличку
        //прозвон ат, эксепшены...
        return 'b52f1331bc6a4323bc6a43232cbc45657bbbc6abc7f7bbdec127517221b577e1e51fb5b';
    }

    public function save_point( $public_sb_id, $time, $stats )
    {
        $sql = 'INSERT INTO oadmins_public_points
                VALUES ( @public_sb_id,@ts, @likes, @reposts, @comments )';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@ts',           $time );
        $cmd->SetInteger('@likes',        $stats['likes']);
        $cmd->SetInteger('@reposts',      $stats['reposts']);
        $cmd->SetInteger('@comments',     $stats['comments']);
        $cmd->SetInteger('@public_sb_id', $public_sb_id );
        echo $cmd->GetQuery();
        $ds = $cmd->Execute();
    }
}
