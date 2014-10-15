<?php
/**
 * Description of Response
 *
 * @author admin-97
 */
namespace library;
class Response {
    //put your code here
    const SUCCESS = 200;
    const ERROR_FAILED = 400;
    const ERROR_AUTH = 401;
    const ERROR_MAINTENANCE = 503;
    
    public static function success()
    {
        http_response_code(self::SUCCESS);
    }
    
    public static function json($data = array())
    {
        http_response_code(self::SUCCESS);
        if($data) {
            $data['version'] = NOW_VERSION;
            $data['send_date'] = time();
            echo json_encode($data);
        }
    }
    
    public static function auth()
    {
        http_response_code(self::ERROR_AUTH);
    }
    
    public static function error($error="")
    {
        $params = isset($_POST['params']) ? 'params : ' . $_POST['params'] : null;
        if($error) {
            if(is_string($error)) {
                error_log($error);
            }
            switch(get_class($error)){
                case 'library\NotParamsException':
                    break;
                case 'library\LoginFailedException':
                    return self::auth();
                default:
                    error_log($error);
                    if($params) {
                        error_log($params);
                    }
            }
        }
        http_response_code(self::ERROR_FAILED);
    }
    
    public static function maintenance()
    {
        http_response_code(self::ERROR_MAINTENANCE);
    }
}
