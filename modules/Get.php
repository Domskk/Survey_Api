<?php
include_once "Common.php";

class Get extends Common {
    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function getAllUsers() {
        $sqlString = "SELECT * FROM users_tbl";
        $data = [];
        $errmsg = "";
        $code = 0;
    
        try {
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute();
    
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($result) {
                $data = $result;
                $code = 200;
            } else {
                $errmsg = "No users found.";
                $code = 404;
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 500;
        }
        return $this->generateResponse($data, $errmsg ? "failed" : "success", $errmsg ? $errmsg : "Successfully retrieved users.", $code);
    }
    
    

    public function getSurveys($surveyId = null) {
        $condition = "1=1";
        if ($surveyId) {
            $condition .= " AND survey_id = " . $surveyId;
        }

        $result = $this->getDataByTable('surveys_tbl', $condition, $this->pdo);
        if ($result['code'] == 200) {
            return $this->generateResponse($result['data'], "success", "Successfully retrieved surveys.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }

    public function getQuestions($questionId = null) {
        $condition = "1=1";
        if ($questionId) {
            $condition .= " AND question_id = " . $questionId;
        }
        $result = $this->getDataByTable('questions_tbl', $condition, $this->pdo);

        if ($result['code'] == 200) {
            return $this->generateResponse($result['data'], "success", "Successfully retrieved questions.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }


    public function getResponses($responseId = null) {
        $condition = "1=1";
        if ($responseId) {
            $condition .= " AND response_id = " . $responseId;
        }
        $result = $this->getDataByTable('responses_tbl', $condition, $this->pdo);

        if ($result['code'] == 200) {
            return $this->generateResponse($result['data'], "success", "Successfully retrieved responses.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
}

?>