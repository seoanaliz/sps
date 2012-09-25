<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 17.09.12
 * Time: 18:07
 * To change this template use File | Settings | File Templates.
 */
class MessageSender
{
    public function Execute()
    {
        $users = $this->get_users_wo_ban();
        print_r($users);
        foreach( $users as $user_id ) {
            $messages = $this->get_queued_messages( $user_id );
            foreach( $messages as $message ) {
                $rec_id = MesDialogs::get_opponent($user_id, $message['dialog_id']);
                echo '<br>' . $rec_id .'<br>';
                $delivery = MesDialogs::writeMessage( $user_id, $rec_id, $message['message_text'] );
                if (isset($delivery->error_msg)) {
                    if ( $delivery->errore_code == 7 )
                    {
                        StatUsers::set_mes_limit_ts( $user_id );
                        continue;
                    }
                    //todo обработак ошибок, логирование
                }

                MesDialogs::mark_message_as_sent( $message['message_id'] );
            }
        }

        //для каждого выбрать по одному, самому раннему, неотправленному сообщению
            //gjckfnm cjj,otubt
    }

    public function get_queued_messages( $user_id )
    {
        $sql = 'SELECT a.id, b.text, dialog_id
                FROM ' . TABLE_MES_QUEUES . ' as a
                LEFT JOIN ' . TABLE_MES_TEXTS . ' as b
                ON a.id = b.id
                WHERE a.user_id = @user_id AND sent = FALSE
                ORDER BY created_time
                LIMIT 21';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInteger( '@user_id', $user_id );
        echo $cmd->GetQuery();
        $ds = $cmd->Execute();
        $messages = array();
        while( $ds->Next()) {
            $messages[] = array(
                'message_id'    =>  $ds->GetInteger( 'id' ),
                'message_text'  =>  $ds->GetValue( 'text' ),
                'dialog_id'     =>  $ds->GetValue( 'dialog_id' ),
            );
        }
        return $messages;
    }

    //выбрать пользователей с неисчерпанным лимитом сообщений
    public function get_users_wo_ban()
    {
        $now = time();
        $sql = 'SELECT user_id FROM ' . TABLE_STAT_USERS . ' WHERE  @now - mes_block_ts > 84600';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInteger( '@now', $now );
        $ds = $cmd->Execute();
        $result = array();
        while( $ds->Next()) {
            $result[] = $ds->GetInteger('user_id');
        }
        return $result;
    }


}
