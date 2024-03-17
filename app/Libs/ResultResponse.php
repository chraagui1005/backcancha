<?php

namespace App\Libs;


class ResultResponse
{
    const SUCCESS_CODE = 200;
    const ERROR_CODE = 300;
    const ERROR_ELEMENT_NOT_FOUND_CODE = 404;

    const TXT_SUCCESS_CODE='Success';
    const TXT_ERROR_CODE='Error';
    const TXT_ERROR_ELEMENT_NOT_FOUND_CODE='Element not found';



    public $statusCode;
    public $message;
    public $data;

    function __construct(){
        $this->statusCode=self::ERROR_CODE;
        $this->message='Error';
        $this->data='';
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $code
     */
    public function setStatusCode($statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }


    /**
     * @return mixed
     */

     public function getData()
     {
         return $this->data;
     }

     /**
     * @param mixed $data
     */

     public function setData($data): void
     {
         $this->data = $data;
     }

     /**
     * @return mixed
     */

}
?>
