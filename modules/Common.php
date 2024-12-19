<?php
class Common{

    protected function logger($user, $method, $action){
        $filename = date("Y-m-d") . ".log";
        $datetime = date("Y-m-d H:i:s");
        $logMessage = "$datetime,$method,$user,$action" . PHP_EOL;
        error_log($logMessage, 3, "./logs/$filename");
    }

    private function generateInsertString($tablename, $body){
        $keys = array_keys($body);
        $fields = implode(",", $keys);
        $parameter_array = [];
        for($i = 0; $i < count($keys); $i++){
            $parameter_array[$i] = "?";
        }
        $parameters = implode(',', $parameter_array);
        $sql = "INSERT INTO $tablename($fields) VALUES ($parameters)";
        return $sql;
    }

    protected function getDataByTable($tableName, $condition, \PDO $pdo){
        $sqlString = "SELECT * FROM $tableName WHERE $condition";
   
        $data = array();
        $errmsg = "";
        $code = 0;

        
        try{
            if($result = $pdo->query($sqlString)->fetchAll()){
                foreach($result as $record){
                    array_push($data, $record);
                }
                $result = null;
                $code = 200;
                return array("code"=>$code, "data"=>$data); 
            }
            else{
                $errmsg = "No data found";
                $code = 404;
            }
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }

        return array("code"=>$code, "errmsg"=>$errmsg);
    }

    protected function getDataBySQL($sqlString, \PDO $pdo){
        $data = array();
        $errmsg = "";
        $code = 0;

        
        try{
            if($result = $pdo->query($sqlString)->fetchAll()){
                foreach($result as $record){
                    array_push($data, $record);
                }
                $result = null;
                $code = 200;
                return array("code"=>$code, "data"=>$data); 
            }
            else{
                $errmsg = "No data found";
                $code = 404;
            }
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }

        return array("code"=>$code, "errmsg"=>$errmsg);
    }


    public function generateResponse($data, $remark, $message, $statusCode) {
        $response = [];
    
        if ($remark === "success") {
            $response = [
                "payload" => $data,
                "status" => [
                    "remark" => $remark,
                    "message" => $message
                ],
                "prepared_by" => "Jaztine",
                "date_generated" => date("Y-m-d H:i:s")
            ];
        } else {
            $response = [
                "payload" => $data,
                "message" => $message,
                "code" => $statusCode
            ];
        }
    
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    

    public function postData($tableName, $body, \PDO $pdo){
        $values = [];
        $errmsg = "";
        $code = 0;


        foreach($body as $value){
            array_push($values, $value);
        }
        
        try{
            $sqlString = $this->generateInsertString($tableName, $body);
            $sql = $pdo->prepare($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }

        
        return array("errmsg"=>$errmsg, "code"=>$code);

    }
    
}
?>