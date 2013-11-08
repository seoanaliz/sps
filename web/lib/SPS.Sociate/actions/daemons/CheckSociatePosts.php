<?php
/**
 * Created akalie
 */
//каждый час проверяем, нет ли постов в sociate на ближайшие сутки
//кажде сутки - на 2 суток вперед
class CheckWalls
{
    public function Execute()
    {
        set_time_limit( 1000 );
        $targetFeeds = TargetFeedFactory::Get(['_targetFeedId' => SociateHelper::$sociateTargetFeedIds]);
        foreach( $targetFeeds as $tf ) {
            $hours = 0;
            //поверяем на 12 часов вперед, нет ли новых принятых заявок
            while( $hours++ < 12) {
                $timestamp = DateTimeWrapper::Now()->modify('+' . $hours .' hours')->format('U');
                $res = SociateHelper::checkIfIntervalOccupied( $timestamp, $tf->externalId );
                if ( !empty( $res )) {
                    Logger::Warning('we have a sociate post!');
                    ArticleUtility::InsertFakeAQ($tf->targetFeedId, $res['from'], $res['to']);
                    return false;
                }
                sleep( 0.1 );
            }
        }
    }
}
