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

    public function execute()
    {

//        error_reporting(0);
        $this->conn = ConnectionFactory::Get();
        $user_id    =   AuthVkontakte::IsAuth();
        $public_sb_id  =   Request::getInteger('groupId');
        $authors = AuthorFactory::Get( array( '_targetFeedIds' => array( $public_sb_id )));

        if ( !$authors )
            die( ObjectHelper::ToJSON( array( 'response' => array( 'authors' => array()))));
        $users_line = '';
        $state_data = array();
        foreach( $authors as $author ) {
            $state_data[$author->vkId] = $this->get_sent_authors_posts( $public_sb_id, $author->authorId );
            $users_line .= $author->vkId . ',';
        }

        $total_posts          =     $this->get_all_authors_posts( $public_sb_id );
        $average_public_data  =     $this->get_average_rate( $public_sb_id );

        $users_info = StatUsers::get_vk_user_info( $users_line );

        foreach( $authors as $author ) {
            $likes   = ( $state_data[$author->vkId]['avg_likes'] && $average_public_data['avg_likes']) ?
                round( 100 * $state_data[$author->vkId]['avg_likes'] / $average_public_data['avg_likes'], 2) : 0;
            $reposts = ( $state_data[$author->vkId]['avg_reposts'] && $average_public_data['avg_reposts']) ?
                round( 100 * $state_data[$author->vkId]['avg_reposts'] / $average_public_data['avg_reposts'], 2) : 0;
            $total_posts_tmp = isset( $total_posts[$author->authorId] ) ? $total_posts[$author->authorId] : 0;
            if ( !$total_posts_tmp)
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
        $sort = $this->compare( 'b' );
//        print_r($res);
        usort( $res, $sort);
        die( ObjectHelper::ToJSON( array( 'response' => array( 'authors' => $res ))));
    }

    public function get_sent_authors_posts( $target_feed_id, $author_id )
    {
        //выбрать отправленные посты
        $sql = 'SELECT avg("externalLikes")as likes,avg("externalRetweets") as reposts, count(*) FROM
                    "articles" a
                JOIN
                    "articleQueues" b
                USING ("articleId")
                WHERE
                    a."createdAt" < now()- interval \'1 day\'
                    AND a."createdAt" > now()- interval \'111 month\'
                    and "authorId" = @author_id
                    AND a."targetFeedId"= @target_feed_id
                    and b."sentAt" is not null
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@author_id', $author_id );
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
//        echo $cmd->GetQuery() . '<br>';
        $ds = $cmd->Execute();
        $ds->Next();
        return array(
            'avg_likes'     =>  $ds->GetInteger( 'likes' ),
            'avg_reposts'   =>  $ds->GetInteger( 'reposts' ),
            'count'         =>  $ds->GetInteger( 'count' ),
        );
    }

    //возвращает массив id=>количество сделанных постов
    public function get_all_authors_posts( $target_feed_id )
    {
        //выбрать созданные посты
        $sql = 'SELECT count(*), "authorId" FROM
                    "articles" a
                JOIN
                    "articleQueues" b
                USING ("articleId")
                WHERE
                    a."createdAt" < now()-interval \'1 day\'
                    AND a."createdAt" > now()-interval \'111 month\'
                    AND a."targetFeedId" = @target_feed_id
                GROUP BY
                    "authorId"
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
        $ds = $cmd->Execute();
//        echo $cmd->GetQuery() . '<br>';
        $res = array();
        while( $ds->Next()){
            $res[$ds->GetInteger( 'authorId' )]  =  $ds->GetInteger( 'count' );
        }

        return $res;
    }

    public function get_average_rate( $target_feed_id )
    {
        $sql = 'SELECT avg("externalLikes") as likes, avg("externalRetweets") as reposts, count(*) FROM
                    "articles" a
                JOIN
                    "articleQueues" b
                USING ("articleId")
                WHERE
                    a."createdAt" < now()-interval \'1 day\'
                    AND a."createdAt" > now()-interval \'111 month\'
                    AND a."targetFeedId"= @target_feed_id
                    and b."sentAt" is not null
                ';
        $cmd = new SqlCommand( $sql, $this->conn);
        $cmd->SetInt( '@target_feed_id', $target_feed_id );
        $ds = $cmd->Execute();
//        echo $cmd->GetQuery();
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
        $code = "
        return  $rev * strnatcmp(\$a['metrick1']['$field'], \$b['metrick1']['$field']);";
        return create_function('$a,$b', $code );
    }
}
