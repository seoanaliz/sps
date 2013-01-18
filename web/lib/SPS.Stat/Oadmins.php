<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );


class Oadmins
{
    public static  $white_list = array(
        2701428,
        1715958,
        2814488,
        3969468,
        4181767,
        5155508,
        5274121,
        6810901,
        7203958,
        7875269,
        10497980,
        11716281,
        17662525,
        18379341,
        25766591,
        27575384,
        43032990,
        43916879,
        58540552,
        61514101,
        83475534,
        110337004,
        114080351,
        121069867,
        135339094,
        150220483,
        161113216,
        178503163,

    );



    public static function get_public_month_score( $targetFeedId, $time_from, $time_to )
    {
        $sql =  'SELECT
                          sum(likes) as likes,
                          sum(reposts) as reposts,
                          sum(comments) as comments
                    FROM
                          oadmins_public_points
                    WHERE
                              public_sb_id  =   @targetFeedId
                          AND ts            >=  @time_from
                          AND ts            <=  @time_to';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $cmd->SetInteger('@targetFeedId', $targetFeedId );
        $cmd->SetInteger('@time_from',  $time_from );
        $cmd->SetInteger('@time_to',  $time_to );
        $ds = $cmd->Execute();
        $ds->next();
        return array(
            'likes'    =>  $ds->GetInteger('likes'),
            'reposts'  =>  $ds->GetInteger('reposts'),
            'comments' =>  $ds->GetInteger('comments'),
        );
    }

    public static function count_author_posts_placed_by_editor( $editor_vk_id, $target_feed_id, $time_from, $time_to )
    {
        $sql = 'SELECT COUNT(*)
                FROM
                    "articleQueues" AS a,
                    "authorEvents" AS b
                WHERE
                    a."articleId"=b."articleId"
                    AND "author"=@editor_vk_id
                    AND "sentAt">@time_from
                    AND "sentAt"<@time_to
                    AND "targetFeedId"=@target_feed_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
        $cmd->SetString('@editor_vk_id', $editor_vk_id );
        $cmd->SetInteger('@target_feed_id', $target_feed_id );
        $cmd->SetString('@time_from',  $time_from );
        $cmd->SetString('@time_to',  $time_to );
        $ds = $cmd->Execute();
        $ds->next();
        return $ds->getInteger('count');

    }

    public static function count_all_posts_placed_by_editor( $editor_vk_id, $target_feed_id, $time_from, $time_to )
    {
        $sql = 'SELECT COUNT(*)
                FROM
                    "articleQueues" AS a
                WHERE
                    "author"=@editor_vk_id
                    AND "sentAt">@time_from
                    AND "sentAt"<@time_to
                    AND "targetFeedId"=@target_feed_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
        $cmd->SetString('@editor_vk_id', $editor_vk_id );
        $cmd->SetInteger('@target_feed_id', $target_feed_id );
        $cmd->SetString('@time_from',  $time_from );
        $cmd->SetString('@time_to',  $time_to );
        $ds = $cmd->Execute();
    }

    public static function get_gen_editors_work()
    {

        $time_from = '2012-09-15 00:00:00';
        $time_to = '2012-10-15 00:00:00';
        $editors_list = EditorFactory::Get();

        foreach( $editors_list as $editor )
        {
            echo $editor->vkId, '<br>';
            if ( !in_array( $editor->vkId, self::$white_list ))
                continue;

            echo '<font size="3" color="red">' . $editor->firstName . ' ' . $editor->lastName . '</font><br>';

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($editor->vkId);
            $targetFeedIds = $TargetFeedAccessUtility->getTargetFeedIds(UserFeed::ROLE_EDITOR);

            foreach( $targetFeedIds as $targetFeedId ) {
                $public = TargetFeedFactory::GetById( $targetFeedId );
                echo '<br>';
                $now = '';
                $prev = '';
                $now = self::get_public_month_score($targetFeedId , 1347667200, 1350259200);

                $prev = self::get_public_month_score($targetFeedId , 1344988800, 1347667200);

                echo '   ' . $public->title . '<br>';
                echo 'Р»Р°Р№РєРё: ' . round($now['likes'] / ($prev['likes'] ? $prev['likes'] : 0),1 ) . '%<br>';
                    echo 'СЂРµРїРѕСЃС‚С‹: ' . round( 100 * $now['reposts'] / ($prev['reposts'] ? $prev['reposts'] : 1),1 ) . '%<br>';
                    echo 'РєРѕРјРјРµРЅС‚С‹: ' . round( 100 * $now['comments'] / ($prev['comments'] ? $prev['comments'] : 1),1 ) . '%<br>';
                    $authors = self::count_author_posts_placed_by_editor( $editor->vkId, $targetFeedId, $time_from, $time_to );
                    $all     = self::count_all_posts_placed_by_editor( $editor->vkId, $targetFeedId, $time_from, $time_to );
                    $authors = $authors ? $authors : 0;
                        $all = $all ? $all : 1;
                    echo 'Р°РІС‚РѕСЂСЃРєРёС… РїРѕСЃС‚РѕРІ: ' . $authors . '( ' . round( $authors / $all , 1 ) . '% )<br><br>';

                }
            echo '<br><br>';
        }
    }
}
?>
