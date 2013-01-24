<?php
Package::Load('SPS.Common');

/**
 * GetSystemStats Action
 * @package SPS
 * @subpackage Common
 * @author eugeneshulepin
 */
class GetSystemStats {

    const GET_ARTICLES = 'select
            "importedAt"::date as "createdAt"
            , count(*) as "totalArticlesCount"
            , sum (case when "sourceFeedId" = -1 then 1 else 0 end) as "authorCreated"
            , sum (case when "externalId" != \'-1\' then 1 else 0 end) as "imported"
        from "getArticles"
        where "importedAt"::date > @startDate
        and "importedAt"::date <= @endDate
        group by "importedAt"::date
        order by "importedAt"::date desc';

    const GET_QUEUES = 'select
            "startDate"::date as "createdAt"
            , count(*) as "totalQueueCount"
            , sum (case when "statusId" = 5 then 1 else 0 end) as "sent"
        from "getArticleQueues"
        where "startDate"::date > @startDate
        and "startDate"::date <= @endDate
        group by "startDate"::date
        order by "startDate"::date desc';

    const GET_ERRORS = 'select
            "createdAt"::date as "createdAt"
            , sum (case when "auditEventTypeId" = 1 then 1 else 0 end) as "importErrors"
            , sum (case when "auditEventTypeId" = 2 then 1 else 0 end) as "exportErrors"
        from "getAuditEvents"
        where "createdAt"::date > @startDate
        and "createdAt"::date <= @endDate
        group by "createdAt"::date
        order by "createdAt"::date desc';

    /**
     * Entry Point
     */
    public function Execute() {
        $result = array();

        $endDate    = DateTimeWrapper::Now();
        $startDate  = DateTimeWrapper::Now()->modify('-1 month');

        $i = new DateTimeWrapper($endDate->format('c'));

        while ($startDate <= $i) {
            $key = $i->DefaultDateFormat();
            $result[$key] = array(
                "totalArticlesCount" => 0,
                "authorCreated" => 0,
                "imported" => 0,
                "totalQueueCount" => 0,
                "sent" => 0,
                "importErrors" => 0,
                "exportErrors" => 0,
            );

            $i->modify('-1 day');
        }

        $cmd = new SqlCommand(self::GET_ARTICLES, ConnectionFactory::Get());
        $cmd->SetDate('@startDate', $startDate);
        $cmd->SetDate('@endDate', $endDate);
        $Ds = $cmd->Execute();

        while ($Ds->Next()) {
            $date = $Ds->GetDateTime('createdAt');
            $key = $date->DefaultDateFormat();

            $result[$key]['totalArticlesCount'] = $Ds->GetInteger('totalArticlesCount');
            $result[$key]['authorCreated'] = $Ds->GetInteger('authorCreated');
            $result[$key]['imported'] = $Ds->GetInteger('imported');
        }

        $cmd = new SqlCommand(self::GET_QUEUES, ConnectionFactory::Get());
        $cmd->SetDate('@startDate', $startDate);
        $cmd->SetDate('@endDate', $endDate);
        $Ds = $cmd->Execute();

        while ($Ds->Next()) {
            $date = $Ds->GetDateTime('createdAt');
            $key = $date->DefaultDateFormat();

            $result[$key]['totalQueueCount'] = $Ds->GetInteger('totalQueueCount');
            $result[$key]['sent'] = $Ds->GetInteger('sent');
        }

        $cmd = new SqlCommand(self::GET_ERRORS, ConnectionFactory::Get());
        $cmd->SetDate('@startDate', $startDate);
        $cmd->SetDate('@endDate', $endDate);
        $Ds = $cmd->Execute();

        while ($Ds->Next()) {
            $date = $Ds->GetDateTime('createdAt');
            $key = $date->DefaultDateFormat();

            $result[$key]['importErrors'] = $Ds->GetInteger('importErrors');
            $result[$key]['exportErrors'] = $Ds->GetInteger('exportErrors');
        }

        Response::setArray('stats', $result);
    }
}
?>