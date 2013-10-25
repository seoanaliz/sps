<?
Package::Load( 'SPS.Stat' );
Package::Load( 'SPS.VK' );

/**
 * addPrice Action
 * @package    SPS
 * @subpackage Stat
 */
class GetPublicsStatControl {

    //чайников
    const   USER_LOGIN          = "79523825768";
    const   USER_PASS           = "gprsforyou";
    const   StatPage            = "http://vk.com/stats?gid=";
    const   StatReachPage       = "http://vk.com/stats?act=reach&gid=";
    const   StatActivityPage    = "http://vk.com/stats?act=activity&gid=";
    const   PAUSE               =  1;

    private $grid = array();
    private $cookies;
    private $from;
    private $to;
    private $today;

    //стата - список пабликов
    private $samorost_publics_array = array(
         36959676
        ,35806721
        ,35807148
        ,36959733
        ,35807199
        ,35806476
        ,36959959
        ,36621543
        ,35807284
        ,37140953
        ,38000303
        ,35807044
        ,38000455
        ,36621560
        ,35807216
        ,37140910
        ,36959798
        ,37140977
        ,36959483
        ,35806378
        ,35807213
        ,38000361
        ,36621513
        ,35806186
        ,38000467
        ,38000487
        ,35807190
        ,38000341
        ,38000435
        ,43503681
        ,43503725
        ,35807071
        ,43503694
        ,35807273
        ,38000323
        ,38000382
        ,38000393
        ,43503630
        ,38000555
        ,43503753
        ,43157718
        ,43503575
        ,43503503
        ,43503550
        ,43503460
        ,43503264
        ,43503298
        ,43503235
        ,43503431
        ,43503315
        ,52223807
    );

    private $publics_for_barter = array(

          36959676
        , 43503725
        , 35806721
        , 36959733
        , 35806476
        , 38000455
        , 35807190
        , 36959798
        , 36621543
        , 36959483
        , 37140953
        , 36959959
        , 36621513
        , 38000382
        , 38000467
        , 36621560
        , 38000361
        , 37140910
        , 43503315
        , 43503431
        , 43503264
        , 52223807
        , 43503575
        , 35806186
        , 35807284
        , 35807199
        , 38000303
        , 35807044
        , 35807216
        , 37140977
        , 35806378
        , 35807213
        , 38000487
        , 38000341
        , 38000435
        , 35807148
        , 43503681
        , 35807071
        , 43503694
        , 35807273
        , 38000323
        , 38000393
        , 43503630
        , 38000555
        , 43503753
        , 43157718
        , 43503503
        , 43503550
        , 43503460
        , 43503298
        , 43503235
    );
    //стата - пердполагаемый саморост пабликов
    private $samorost = array(
    );

    //массив регулярок для парса страниц
    private $preg_array = array(
        'members_lost'      =>  '/"Members lost",(.*?\:\[\[.*?]])/',
        'members_growth'    =>  '/f":1,"name":"New members".*?("d"\:\[\[.*?]])/',
        #'vidget_members'    =>  '/{"name":"New members","l".*?,("d".*?]])/', // не во всех пабликах есть, нафиг его
        'unique_visitors'   =>  '/unique visitors.*?,("d".*?]])/',
        'views'             =>  '/Pageviews.*?,("d".*?]])/',
        'full_coverage'     =>  '/Full coverage.*?,("d".*?]])/',
        'followers_coverage'=>  '/Followers coverage.*?,("d".*?]])/',
        'video'             =>  '/Videos.*?,("d".*?]])/',
        'photo'             =>  '/Photos.*?,("d".*?]])/',
        'audio'             =>  '/Audio files.*?,("d".*?]])/',
        'discussion_board'  =>  '/Discussion board.*?,("d".*?]])/',
        'likes'             =>  '/{"name":"Like".*?,("d".*?]])/',
        'reposts'           =>  '/{"name":"Share".*?,("d".*?]])/',
    );

    //массив возможных выборок статы
    private $meth_array = array(
        'barter'            =>  array( 'members_growth' ),
        'reach'             =>  array( 'full_coverage', 'followers_coverage', 'unique_visitors' ),
        'sections_views'    =>  array( 'photo', 'discussion_board', 'video', 'audio' ),
        'like_reposts'      =>  array( 'likes', 'reposts'),
        'followers'         =>  array('followers_coverage'),
        'inner_followers_and_reposts'    =>  array('followers_coverage', 'reposts'),
    );

    public function Execute()
    {
        set_time_limit(1000);
        header('Content-Type: text/html; charset=utf-8');
        $this->today = DateTimeWrapper::Now()->format('d m Y');
        $this->yesterday = DateTimeWrapper::Now()->modify('-1 day')->format('d m Y');
        $methods_num = array_keys($this->meth_array);
        Response::setArray('methods', $methods_num);

        if ( Request::getString('action') == 'updateStata') {
            $this->get_cookies();
            $this->get_all_pages();
            die();
        }
        if ( Request::getString('action') != 'add') {
            return;
        }

        $from = Request::getDateTime( 'from' );
        $to   = Request::getDateTime( 'to' )->modify('+1 day');
        $method = Request::getInteger('method');
        if( !$method === null ) {
            echo('you don\'t choose method');
            return;
        }

        if ( !$from || !$to ) {
            echo('you don\'t enter dates');
            return;
        }

        if ( $to < $from ) {
            echo('wrong time');
            return;
        }

        $method     = $methods_num[$method];
        $this->to   = $to->format('U');
        $this->from = $from->format('U');

        $this->get_cookies();
        $this->make_grid_array();
        $this->grid = array_reverse( $this->grid, 1);
        $result = array();
        $publics_array =  ( $method == 'barter' ) ? $this->publics_for_barter : $this->samorost_publics_array;

        foreach( $publics_array as $public_id ) {
            $sub = 0;
            $page = '';
            $page = $this->get_page($public_id);
            foreach( $this->meth_array[$method] as $field ) {
                $values = $page[$field];
                $result[$public_id][$field] = $this->key_maker_mk2( $values, $sub );
            }
        }

        $public_count = count( $this->samorost_publics_array );
        $days = count( $this->grid );
        $days = $days ? $days : 1;
        //выводим стату по охватам
        if ($method == 'reach' ) {
            echo 'Общая стата по пабликам в среднем за сутки (всего суток было :', $days, ')<br>';
            echo 'паблик |   охват | охват подписчиков | уники <br>';
            $total_full_cov = 0;
            $total_foll_cov = 0;
            $total_unique_visitors = 0;
            foreach( $result as $id => $data ) {
                $full_cov =  array_sum( $data['full_coverage']);
                $foll_cov =  array_sum( $data['followers_coverage']);
                $unique_visitors =  array_sum( $data['unique_visitors']);
                $total_foll_cov += $foll_cov;
                $total_full_cov += $full_cov;
                $total_unique_visitors += $unique_visitors;
                echo $id, ' | ', round( $full_cov / $days )  ,' | ', round( $foll_cov / $days ), ' | ', round( $unique_visitors / $days ),'<br>';
            }
            echo '<br><br>Итого: полный охват(в день) ', round( $total_full_cov / $days ) , ', охват подписчиков: ', round($total_foll_cov / $days ), ', уникальных посетителей', round($total_unique_visitors / $days );
            echo '<br><br>Итого(средний в паблике в день): охват ', round( $total_full_cov / ($days * $public_count ) ) , ', охват подписчиков: ', round($total_foll_cov / ($days * $public_count ) ), ', уникальных посетителей: ', round($total_unique_visitors / ($days * $public_count ) );
        } elseif( $method == 'inner_followers_and_reposts' ) {
            $this->get_followers_and_reposts( $this->from, $this->to, $result);
        } else {
            $this->form_excel_file( $method, $from, $to, $result );
        }
    }

    private function get_cookies()
    {
        $this->cookies = VkHelper::vk_authorize(self::USER_LOGIN, self::USER_PASS);
    }

    #стата - виджет
    public  function form_excel_file($method, $from, $to, $data)
    {
        $to = $to->sub( new DateInterval('P1D'));
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle($method);

        $cell = $aSheet->getCellByColumnAndRow( 0, 1 );
        $cell->setValue( 'паблик' );
        $cell = $aSheet->getCellByColumnAndRow( 1, 1 );
        $second_colum  = $method == 'barter' ? '' : 'раздел';
        if ($second_colum) {
            $cell->setValue( $second_colum );
        }
        $filename = $method . ' ' . $from->format('d-m-Y') . ' to ' . $to->format('d-m-Y') . '.xls';

        $date = new DateTimeWrapper($from->format('d-m-Y'));
        //проставляем даты
        $column = 2;
        foreach( $this->grid as $element ) {
            $cell = $aSheet->getCellByColumnAndRow( $column++, 1);
            $cell->setValue( $date->format('d'));
            $date->modify('+1 day');
        }
        $row_number = 2;

        //перебираем паблики
        foreach( $data as $public_id => $public_data ) {
            $cell = $aSheet->getCellByColumnAndRow( 0, $row_number);
            $cell->setValue( $public_id );
            $samorost = 0;
            if( $method == 'barter' ) {
                $samorost = current($this->samorost);
                next($this->samorost);
            }
            //перебираем отдельные строки данных
            foreach( $public_data as $row_name => $row ) {
                $column = 2;
                $cell = $aSheet->getCellByColumnAndRow( 1, $row_number);
                $cell_item = $method == 'barter' ? '' : $row_name ;
                $cell->setValue( $cell_item );
                //перебираем колонки
                foreach( $this->grid as $k => $v ) {
                    $cell_item = isset( $row[ $k ] ) ? ($row[ $k ] ) : 0;
                    $cell = $aSheet->getCellByColumnAndRow( $column++, $row_number );
                    $cell->setValue( $cell_item );
                }

                $row_number ++;
            }
        }

        $localPath  = Site::GetRealPath('temp://' . $filename . '.xls');
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save($localPath);
        header('Content-Type: application/vnd.ms-excel');
        header ("Content-Length: " . filesize( $localPath ));
        header ("Content-Disposition: attachment; filename=" . $filename);
        header('Cache-Control: max-age=0');
        readfile( $localPath );
        unlink($localPath);
        die();

    }

    private function key_maker_mk2( $values, $sub = 0 )
    {
        $res = array();
        foreach( $values as $date_point ) {
            if ( isset( $this->grid[$date_point[0]] )) {
                $res[$date_point[0]] = $date_point[1] - $sub;
            }
        }
        return $res;
    }

    private function make_grid_array()
    {
        $page = VkHelper::connect( self::StatPage . $this->samorost_publics_array[0], $this->cookies );

        preg_match( $this->preg_array['views'], $page, $values );
        $values = json_decode( '{' . $values[1] . '}' )->d;
        if( count( $values ) < 2 )
            die('нет данных');
        $res = array();
        foreach( $values as $entry ) {
            if( $entry[0] > $this->to )
                continue;
            if( $entry[0] < $this->from )
                break;
            $res[$entry[0]] = 1;
        }
        $this->grid = $res;
        sleep(self::PAUSE);
        return;
    }

    private function get_followers_and_reposts(  $from_ts, $to_ts, $result ) {
        $total_sums_for_publics = array();
        foreach ( $result as $public_data ) {
            foreach ( $public_data as $field => $points) {
                foreach ( $points as $ts=>$value ) {
                    if ( $ts >= $from_ts && $ts <= $to_ts ) {
                        $tmp_month = date('m', $ts );
                        if ( !isset( $total_sums_for_publics[$tmp_month][$field] )) {
                            $total_sums_for_publics[$tmp_month][$field] = 0;
                        }
                        $total_sums_for_publics[$tmp_month][$field] += $value;
                    }
                }
            }
        }
        foreach( $total_sums_for_publics as $month => $row) {
            echo 'in ' . date('F', mktime(0, 0, 0, $month, 1, 2000))
                . ' followers coverage is ' . $row['followers_coverage'] . ', '
                . 'and ' . $row['reposts'] . ' reposts<br>';
        }
        die();
    }

    private function get_page($publicId ) {
        $filename = Site::GetRealPath('temp://stat_' . $publicId . '_'. $this->today. '.txt');
        $yFilename = Site::GetRealPath('temp://stat_' . $publicId . '_'. $this->yesterday . '.txt');
        if( file_exists( $yFilename))
            unlink($yFilename);

        if ( file_exists( $filename ) || filesize($filename) > 100 ) {
            return json_decode( file_get_contents($filename), $assoc = true);
        } else {
            if ( !$this->cookies ) {
                $this->cookies = $this->get_cookies();
            }
            $tryes = 0;
            $result = [];
            while( $tryes < 4) {
                $tryes ++;
                try {
                    $page  = VkHelper::connect( self::StatPage . $publicId, $this->cookies );
                    $page .= VkHelper::connect( self::StatReachPage . $publicId, $this->cookies );
                    $page .= VkHelper::connect( self::StatActivityPage . $publicId, $this->cookies );
                    #file_put_contents( $filename.'2131', $page);
                    foreach( $this->preg_array as $name => $pattern ) {
                        $values = [];
                        if ( !preg_match( $pattern, $page, $values ))
                            throw new Exception('cant find pattern! ' . $pattern . '<br>' . 'in ' . $publicId);
                        $values = json_decode( '{' . $values[1] . '}' )->d;
                        $result[$name] = $values;
                    }
                    #file_put_contents( $filename.'21312', $result);
                    unset($name);
                    unset($pattern);
                    file_put_contents( $filename, json_encode($result));
                    return $result;
                } catch (Exception $e ) {
                    print_r($e->getMessage());
                    $page = '';
                    sleep(0.4);
                    die();
                }
            }
        }
    }

    private function get_all_pages( ) {
        foreach( $this->samorost_publics_array as $publicId) {
            $this->get_page($publicId);
        }
    }

}