<?php
/**
 * User: x100up
 * Date: 21.12.12 7:26
 * In Code We Trust
 */
class CounterUtility {
    public function getResettableCounter(){
        $args = func_get_args();

        $lastCount = $args[0];

        $args = array_slice($args, 1);

        $counterKey = md5(implode('.', $args));


    }
}
