<?php

    /**
     * PgSql Type String[]
     * @package Eaze
     * @subpackage Database
     * @subpackage PgSql
     * @author sergeyfast, shuler
     */
    class PgSqlTypeStringArray implements IComplexType {

        /**
         * @var PgSqlConvert
         */
        private $converter;


        /**
         * @param ISqlConvert $converter
         */
        public function __construct( ISqlConvert $converter ) {
            $this->converter = $converter;
        }


        /**
         * @param  string $operator
         * @param string $field
         * @param  string $value
         * @return string
         */
        public function GetSearchOperatorString( $operator, $field, $value ) {
            switch( $operator ) {
                case SEARCHTYPE_EQUALS:
                    $result = sprintf( '%s = %s', $this->converter->Quote( $operator ), $this->ToDatabase( $value ) );
                    break;
                default:
                    Logger::Error( 'Invalid search type %s', $operator );
                    $result = 'false';
            }

            return $result;
        }


        /**
         * Save PHP value to Database
         * @param array $value
         * @return string
         */
        public function ToDatabase( $value = null ) {
            if ( $value === null ) {
                return 'null';
            }

            $result = '{' . implode( ',', array_map( array( 'PgSqlConvert', 'ToString' ), $value ) ) . '}';

            return $this->converter->ToString( $result );
        }


        /**
         * Validate PHP Value before Save to Database
         * @param array|mixed $value
         * @param array $structure
         * @param array|null $options
         * @return array errors array
         */
        public function Validate( $value, array $structure, $options = null ) {
            $errors = array();

            //format check
            if( !is_array( $value ) && $value !== null ) {
                return array( 'format' => 'format' );
            }

            //nullable check
            if( isset( $structure['nullable'] ) ) {
                switch ( $structure['nullable'] ) {
                    case 'CheckEmpty':
                        if( !is_array( $value ) || empty( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                    case 'No':
                        if( is_null( $value ) ) {
                            $errors['null'] = 'null';
                        }
                        break;
                }
            }

            return $errors;
        }


        /**
         * Get PHP value from Database
         * @param  string $parameter
         * @return array
         */
        public static function FromDatabase( $parameter ) {
            if ( empty( $parameter ) ) {
                return array();
            }

            $value  = trim( $parameter, '{} ' );
            $result = ( strlen( $value ) == 0 ) ? array() : explode( ',', $value );
            $result = array_map( array( 'PgSqlTypeStringArray', 'convertFromDatabase' ), $result );

            return $result;
        }


        /**
         * Get PHP value from Request
         * @param array|mixed $value
         * @return array
         */
        public static function FromRequest( $value ) {
            $value = array_map( array( 'Convert', 'ToString' ), Convert::ToArray( $value ) );

            return $value;
        }


        /**
         * Get Complex Type Name
         * @return string
         */
        public static function GetName() {
            return 'string[]';
        }

        /**
         * converts string value from database array
         *
         * @static
         * @param $value
         * @return null|string
         */
        private static function convertFromDatabase( $value ) {
            if( $value === 'null' || $value === 'NULL' ) {
                return null;
            } else {
                //cut first and last (') symbol, cut double (') symbol
                $result = mb_substr( $value, 1, mb_strlen( $value ) - 2 );
                $result = str_replace( "''", "'", $result );
                return (string) $result;
            }
        }
    }
?>
