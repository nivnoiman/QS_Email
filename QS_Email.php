<?php
/**
 * QS_Email
 *  This Class is responsible for handling Email on wordpress.
 *  With this Class you can - send emails to users with your own templet.
 *
 * @category    Wordpress
 * @author      NivNoiman
 * Text-Domain: qs_email
 */
class QS_Email {
    /* ###### Properties ###### */
    public $emailData = array(); // returned user data
    protected static $messages;
    /* ###### Magic ###### */
    /**
     * __construct
     * by declared class with values ( path of the templete and values ) - the first action will be the initialize of the class with your data.
     * @param [string] $path [ the path of your tempalte ]
     * @param [array] $args [ array with placehoder and data ]
     */
    function __construct( $path = NULL , $args = NULL ){
        $this->init( $path , $args );
    }
    /**
     * __get
     * get params from class
     * @param [string] $string [ the field we want to get his value ]
     */
    public function __get( $string ){
        if( array_key_exists( $string , $this->emailData ) ){
                return $this->emailData[ $string ];
        }
    }
    /* ###### Functions ###### */
    /**
     * init
     * initialize the class with all the values ( $path and $args )
     * @param [string] $path [ the path of your tempalte ]
     * @param [array] $args [ array with placehoder and data ]
     */
    protected function init( $path = NULL , $args = NULL){
        if( !empty( $path ) && file_exists( $path ) ){
            $this->emailData['path'] = $path;
        } else
            $this->emailData['path'] = '';
        if( !empty( $args ) && is_array( $args ) ){
            foreach( $args as $key => $value ){
                $this->emailData['data'][$key] = $value;
            }
        }
        if( empty( $this->emailData[ 'email' ] ) )
            $this->emailData[ 'email' ] = array();
        if( empty( $this->emailData[ 'data' ] ) )
            $this->emailData[ 'data' ] = array();
    }
    /**
     * add_email
     * Add email to the list "to" list for the mailer
     * @param [string] $email [ email ]
     * @return false on failer | true on success
     */
    public function add_email( $email ){
        if( filter_var( $email , FILTER_VALIDATE_EMAIL) && !in_array( $email , $this->emailData[ 'email' ] )  )
            $this->emailData[ 'email' ][] = $email;
        else{
            selef::$messages['error']['add_email'] = __('The email you try to add to the list is not valid' , 'qs_email');
            return false;
        }
        return true;
    }
    /**
     * set
     * Set Variables
     * @param [string] $key [ the variable key ( ID ) ]
     * @param [string] $value [ the variable data ]
     */
    public function set( $key , $value ){
        if( !is_array( $value ) && array_key_exists( $value , $this->emailData ) && $key != 'email' && $key != 'data' && $key != 'holder' )
            $this->emailData[ $key ] = $value;
        else
            $this->emailData['data'][ $key ] = $value;
    }
    /**
     * set_data
     * Set Variables for data only [ the data will pass to the template ]
     * @param [array] $values [ key - for the template | value - for the view ]
     */
    public function set_data( $values ){
        if( is_array( $values ) )
            foreach( $values as $key => $value )
                $this->emailData['data'][ $key ] = $value;
    }
    /**
     * set_data
     * Set Variables for holders only [ the holders will pass to the template ]
     * @param [array] $values [ key - for the template | value - for the view ]
     */
    public function set_holders( $values ){
        if( is_array( $values ) )
            foreach( $values as $key => $value )
                $this->emailData['holder'][ $key ] = $value;
    }
    /**
     * holderToValue
     * replace all the holders with data
     * @param [string] $string [ the content  ]
     */
    protected function holderToValue( $string ){
        if( !empty( $string ) && !empty( $this->emailData['holder'] ) ){
            foreach( $this->emailData['holder'] as $holder => $value ){
                $string = str_replace( $holder , $value , $string );
            }
        }
        return $string;
    }
    /**
     * send
     * this method will send the mail with all the data 
     */
    public function send(){
        global $emailData;
        $emailData = $this->emailData['data'];

        $to = implode( ",", $this->emailData['email'] );
        $subject = self::holderToValue( $this->emailData['data']['subject'] );
        $headers = array('Content-Type: text/html; charset=UTF-8');

        ob_start();
            include $this->emailData['path'];
        $body = self::holderToValue( ob_get_clean() );
        if( function_exists('wp_mail') )
            wp_mail( $to, $subject, $body, $headers );
        else
            mail( $to, $subject, $body, $headers );
    }
}
?>
