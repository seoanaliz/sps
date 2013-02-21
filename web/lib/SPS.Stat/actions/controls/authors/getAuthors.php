<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */
class getAuthors
{
    private $conn;
    private $date_from;
    private $date_to;

    public function execute()
    {

        error_reporting(0);
        $this->conn         =   ConnectionFactory::Get();
        $this->conn         =   ConnectionFactory::Get();
        $user_id            =   AuthVkontakte::IsAuth();
        $public_sb_id       =   Request::getInteger('groupId');
        //РµСЃР»Рё РґРёР°РїР°Р·РѕРЅ РЅРµ Р·Р°РґР°РЅ, РІС‹Р±РёСЂР°РµС‚ РґР°РЅРЅС‹Рµ Р·Р° РїСЂРѕС€Р»С‹Р№ РјРµСЃСЏС†
        $this->date_from    =   Request::getInteger('dateFrom') ? date( 'Y-m-d 00:00:01', Request::getInteger('dateFrom'))
            : date( 'Y-m-01 00:00:01', strtotime('-1 month'));
        $this->date_to      =   Request::getInteger('dateTo')   ? date( 'Y-m-d 00:00:01', Request::getInteger('dateFrom'))
            : date( 'Y-m-01 00:00:01');



        $authors = $this->get_public_authors( $public_sb_id );
        if ( !$authors )
            die( ObjectHelper::ToJSON( array( 'response' => array( 'authors' => array()))));

        $users_line = '';
        $state_data = array();
        foreach( $authors as $author ) {
            $state_data[$author->vkId] = $this->get_sent_authors_posts( $public_sb_id, $author->authorId, $author->vkId );
            $users_line .= $author->vkId . ',';
        }

        $total_posts          =     $this->get_all_authors_app_posts( $public_sb_id );

        $total_posts          =     $this->get_all_authors_sb_posts( $public_sb_id, $total_posts );
        $average_public_data  =     $this->get_average_rate( $public_sb_id );

        $users_info = StatUsers::get_vk_user_info( $users_line );
        foreach( $authors as $author ) {
            if ($author->vkId == 106175502 )
                continue;
            $likes   = ( $state_data[$author->vkId]['avg_likes'] && $average_public_data['avg_likes']) ?
                round( 100 * $state_data[$author->vkId]['avg_likes'] / $average_public_data['avg_likes'], 2) : 0;
            $reposts = ( $state_data[$author->vkId]['avg_reposts'] && $average_public_data['avg_reposts']) ?
                round( 100 * $state_data[$author->vkId]['avg_reposts'] / $average_public_data['avg_reposts'], 2) : 0;
            $total_posts_tmp = isset( $total_posts[$author->authorId] ) ? $total_posts[$author->authorId] : 0;
            if ( !$total_posts_tmp )
                continue;
            $res[] = array(
                'id'        =>  $author->vkId,
                'user'      =>  $users_info[$author->vkId] ,
                'metrick1'  =>  array(
                    'a' =>  $state_data[$author->vkId]['count'] . '( ' . $total_posts_tmp . ' )',
                    'b' =>  $likes,
                    'c' =>  $reposts
                )
            );
        }
        // $sort = $this->compare( 'b' );
        usort( $res, $sort);
        if(!$res )
            $res = array();
        die( ObjectHelper::ToJSON( array( 'response' => array( 'authors' => $res ))));
    }

    public function get_sent_authors_posts( $target_feed_id, $author_id, $author_vk_id )
    {
        //РІС‹Р±СЂР°С‚СЊ РѕС‚РїСЂР°РІР»РµРЅРЅС‹Рµ РїРѕСЃС‚С‹
        $sql = 'SELECT avg("externalLikes")as likes,avg("externalRetweets") as reposts, count(*) FROM
                    "articles" a
                JOIN
                    "articleQueues" b
                USING ("articleId")
                WHERE
                    a."createdAt" < @time_to
                    AND a."createdAt" > @time_from
                    and (   "authorId" = @author_id
                          OR "editor"  = @editor )
                    AND b."targetFeedId"= @target_feed_id
                    and b."sentAt" is not null
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@author_id', $author_id );
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
        $cmd->SetString( '@editor', $author_vk_id );
        $cmd->SetString( '@time_from', $this->date_from );
        $cmd->SetString( '@time_to',   $this->date_to );
//        echo $cmd->GetQuery() . '<br>';
        $ds = $cmd->Execute();
        $ds->Next();
        return array(
            'avg_likes'     =>  $ds->GetInteger( 'likes' ),
            'avg_reposts'   =>  $ds->GetInteger( 'reposts' ),
            'count'         =>  $ds->GetInteger( 'count' ),
        );
    }

    //РІРѕР·РІСЂР°С‰Р°РµС‚ РјР°СЃСЃРёРІ id=>РєРѕР»РёС‡РµСЃС‚РІРѕ СЃРґРµР»Р°РЅРЅС‹С… РїРѕСЃС‚РѕРІ
    public function get_all_authors_app_posts( $target_feed_id )
    {
        //РІС‹Р±СЂР°С‚СЊ СЃРѕР·РґР°РЅРЅС‹Рµ РІ Р°РїРїРµ РїРѕСЃС‚С‹
        $sql = 'SELECT count(*), "authorId" FROM
                    "articles"
                WHERE
                    "createdAt" < @time_to
                    AND "createdAt" > @time_from
                    AND "targetFeedId" = @target_feed_id
                GROUP BY
                    "authorId"
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
        $cmd->SetString( '@time_from', $this->date_from );
        $cmd->SetString( '@time_to',   $this->date_to );
//        echo $cmd->GetQuery() . '<br>';

        $ds = $cmd->Execute();
        $res = array();
        while( $ds->Next()){
            $res[$ds->GetInteger( 'authorId' )]  =  $ds->GetInteger( 'count' );
        }

        return $res;
    }

    public function get_all_authors_sb_posts( $target_feed_id, $prev_res )
    {

        //РІС‹Р±СЂР°С‚СЊ СЃРѕР·РґР°РЅРЅС‹Рµ РІ sb РїРѕСЃС‚С‹
        $sql = 'SELECT count(*), "editor" FROM
                    "articles" a
                JOIN
                    "articleQueues" b
                USING ("articleId")
                WHERE
                    a."createdAt" < @time_to
                    AND a."createdAt" > @time_from
                    AND b."targetFeedId" = @target_feed_id
                GROUP BY
                    "editor"
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
        $cmd->SetString( '@time_from', $this->date_from );
        $cmd->SetString( '@time_to',   $this->date_to );
//        echo $cmd->GetQuery() . '<br>';

        $ds = $cmd->Execute();
        $res = array();

        while( $ds->Next()){
            $vkId = $ds->GetInteger( 'editor' );

            if ( !$vkId)
                continue;
            $author = AuthorFactory::GetOne( array( 'vkId' =>  $vkId));
            $count = $ds->GetInteger( 'count' );
            if( isset( $prev_res[$author->authorId]))
                $prev_res[$author->authorId] += $count;
            else
                $prev_res[$author->authorId] = $count;
        }

        return $prev_res;
    }

    public function get_average_rate( $target_feed_id )
    {
        $sql = 'SELECT avg("externalLikes") as likes, avg("externalRetweets") as reposts, count(*) FROM
                    "articles" a
                JOIN
                    "articleQueues" b
                USING ("articleId")
                WHERE
                    a."createdAt" < @time_to
                    AND a."createdAt" > @time_from
                    AND b."targetFeedId"= @target_feed_id
                    and b."sentAt" is not null
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
        $cmd->SetString( '@time_from', $this->date_from );
        $cmd->SetString( '@time_to',   $this->date_to );
        $ds = $cmd->Execute();
//        echo $cmd->GetQuery() . '<br>';

        $ds->Next();
        return array(
            'avg_likes'     =>  $ds->GetInteger('likes'),
            'avg_reposts'   =>  $ds->GetInteger('reposts'),
            'count'         =>  $ds->GetInteger('count'),
        );
    }

    private function compare( $field, $rev = 1 )
    {
        $rev = $rev ? -1 : 1;
        $code = "return  $rev * strnatcmp(\$a['metrick1']['$field'], \$b['metrick1']['$field']);";
        return create_function('$a,$b', $code );
    }

    public function get_public_authors( $targetFeedId ) {
        $sql = 'SELECT * FROM "userFeed" WHERE "targetFeedId" = @targetFeedId';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@targetFeedId', $targetFeedId );

        $ds = $cmd->Execute();
        $res = array();
        while( $ds->Next()) {

            $res[] = $ds->getValue('vkId');
        }
        if ( empty( $res ))
            return false;
        return AuthorFactory::Get( array( 'vkIdIn' => $res, 'pageSize'=>400));
    }
}
