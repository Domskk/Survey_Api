<?php
include_once "Common.php";
class Get extends Common {
    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function getLogs($date) {
        $filename = "./logs" . $date . ".log";
        $logs = [];
        $remarks = "success";
        $message = "Successfully retrieved logs.";
        
        try {
            $file = new SplFileObject($filename);
            while (!$file->eof()) {
                array_push($logs, $file->fgets());
            }
        } catch (Exception $e) {
            $remarks = "failed";
            $message = $e->getMessage();
        }

        $this->logger('system', 'getLogs', $remarks === 'success' ? 'Retrieved logs' : 'Failed to retrieve logs');

        return $this->generateResponse(array("logs" => $logs), $remarks, $message, 200);
    }

        public function getUserById($userId) {
            $sqlString = "SELECT * FROM users_tbl WHERE user_id = ?";
            $data = [];
            $errmsg = "";
            $code = 0;
        
            try {
                $stmt = $this->pdo->prepare($sqlString);
                $stmt->execute([$userId]);
        
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($result) {
                    $data = $result;
                    $code = 200;
                } else {
                    $errmsg = "User not found.";
                    $code = 404;
                }
            } catch (\PDOException $e) {
                $errmsg = $e->getMessage();
                $code = 500;
            }
        
            $this->logger('user_' . $userId, 'getByUserId' ,$errmsg ?'Failed to retrieve the user' : 'Retrieve user');
            return $this->generateResponse(
                $data,
                $errmsg ? "failed" : "success",
                $errmsg ? $errmsg : "Successfully retrieved user.",
                $code
            );
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
        $this->logger('admin' , 'getAllUsers' ,$errmsg ? "Failed to retrieve the user" : "Retrieve all user");

        return $this->generateResponse($data, $errmsg ? "failed" : "success", $errmsg ? $errmsg : "Successfully retrieved users.", $code);
    }

    public function getSurveys($surveyId = null) {
        $currentSurveyId = $surveyId ?? 'none';
        $condition = "1=1";
        
        if ($surveyId) {
            $condition .= " AND survey_id = " . $surveyId;
        }
    
        $this->logger( 'survey_'.$currentSurveyId, 'getSurveys', $surveyId ? "Retrieve survey with ID: $surveyId" : "Retrieve all surveys");
        $result = $this->getDataByTable('surveys_tbl', $condition, $this->pdo);
        
        if ($result['code'] == 200) {
            return $this->generateResponse($result['data'], "success", "Successfully retrieved surveys.", $result['code']);
        }
        
        $this->logger($currentSurveyId, 'getSurveys', "Failed to retrieve surveys: " . $result['errmsg']);
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
    
    public function getQuestions($questionId = null) {
        $currentquestionId = $questionId ?? 'none';
        $condition = "1=1";
        if ($questionId) {
            $condition .= " AND question_id = " . $questionId;
        }
        $this->logger('question_'.$currentquestionId, 'getQuestions', $questionId ? "Retrieve questions with ID: $questionId" : "Retrieve all questions");
        $result = $this->getDataByTable('questions_tbl', $condition, $this->pdo);

        if ($result['code'] == 200) {
            return $this->generateResponse($result['data'], "success", "Successfully retrieved questions.", $result['code']);
        }
        $this->logger($currentquestionId, 'getQuestions', "Failed to retrieve questions". $result['errmsg']);
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }

    public function getResponses($responseId = null) {
        $condition = "1=1";
        $params = [];

        if ($responseId) {
            $condition .= " AND responses_tbl.response_id = ?";
            $params[] = $responseId;
        }

        $sqlString = "
            SELECT 
                responses_tbl.response_id,
                responses_tbl.response_text,
                responses_tbl.created_at,
                questions_tbl.question_id,
                questions_tbl.question_text
            FROM 
                responses_tbl
            JOIN 
                questions_tbl
            ON 
                responses_tbl.question_id = questions_tbl.question_id
            WHERE $condition";

        $data = [];
        $errmsg = "";
        $code = 0;

        try {
            $this->logger('response_'.$responseId ? $responseId : 'all', 'getResponses', $responseId ? "Retrieve response with ID: $responseId" : "Retrieve all responses");
            $stmt = $this->pdo->prepare($sqlString);
            if ($responseId) {
                $stmt->execute([$responseId]);
            } else {
                $stmt->execute();
            }

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $data = $result;
                $code = 200;
            } else {
                $errmsg = "No responses found.";
                $code = 404;
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 500;
        }
        $this->logger('response_'.$responseId ? $responseId : 'all', 'getResponses', $errmsg ? "Failed to retrieve responses: $errmsg" : "Succesfully retrieve response with questions.");

        return $this->generateResponse(
            $data,
            $errmsg ? "failed" : "success",
            $errmsg ? $errmsg : "Successfully retrieved responses with questions.",
            $code
        );
    }

    public function getAllQuestionsAndResponses() {
        $sqlString = "
            SELECT 
                questions_tbl.question_id,
                questions_tbl.question_text,
                responses_tbl.response_id,
                responses_tbl.response_text,
                responses_tbl.created_at
            FROM 
                questions_tbl
            LEFT JOIN 
                responses_tbl
            ON 
                questions_tbl.question_id = responses_tbl.question_id
            ORDER BY 
                questions_tbl.question_id, responses_tbl.created_at ASC
        ";

        $data = [];
        $errmsg = "";
        $code = 0;

        try {
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $groupedData = [];
                foreach ($result as $row) {
                    $questionId = $row['question_id'];
                    if (!isset($groupedData[$questionId])) {
                        $groupedData[$questionId] = [
                            'question_id' => $row['question_id'],
                            'question_text' => $row['question_text'],
                            'responses' => []
                        ];
                    }

                    if ($row['response_id']) {
                        $groupedData[$questionId]['responses'][] = [
                            'response_id' => $row['response_id'],
                            'response_text' => $row['response_text'],
                            'created_at' => $row['created_at']
                        ];
                    }
                }

                $data = array_values($groupedData);
                $code = 200;
            } else {
                $errmsg = "No questions or responses found.";
                $code = 404;
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 500;
        }

        return $this->generateResponse(
            $data,
            $errmsg ? "failed" : "success",
            $errmsg ? $errmsg : "Successfully retrieved questions and responses.",
            $code
        );
    }
    public function getSurveyAnalytics($startDate = null, $endDate = null) {
        $condition = "1=1";
        
        if ($startDate && $endDate) {
            $condition .= " AND surveys_tbl.created_at BETWEEN :start_date AND :end_date";
        }
        
        $sqlString = "
            SELECT 
                surveys_tbl.survey_id, 
                surveys_tbl.title, 
                surveys_tbl.created_at, 
                COUNT(responses_tbl.response_id) AS response_count
            FROM surveys_tbl
            LEFT JOIN questions_tbl ON surveys_tbl.survey_id = questions_tbl.survey_id
            LEFT JOIN responses_tbl ON questions_tbl.question_id = responses_tbl.question_id
            WHERE $condition
            GROUP BY surveys_tbl.survey_id
            ORDER BY surveys_tbl.created_at DESC;
        ";
        
        $data = [];
        $errmsg = "";
        $code = 0;
        
        try {
            $stmt = $this->pdo->prepare($sqlString);
            
            if ($startDate && $endDate) {
                $stmt->bindParam(':start_date', $startDate);
                $stmt->bindParam(':end_date', $endDate);
            }
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result) {
                $data = $result;
                $code = 200;
            } else {
                $errmsg = "No surveys found within the given date range.";
                $code = 404;
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 500;
        }
        
        return $this->generateResponse($data, $errmsg ? "failed" : "success", $errmsg ? $errmsg : "Successfully retrieved survey analytics.", $code);
    }
    public function getQuestionAnalytics($startDate = null, $endDate = null) {
        $condition = "1=1";
        
        if ($startDate && $endDate) {
            $condition .= " AND responses_tbl.created_at BETWEEN :start_date AND :end_date";
        }
        
        $sqlString = "
            SELECT 
                questions_tbl.question_id, 
                questions_tbl.question_text, 
                COUNT(responses_tbl.response_id) AS response_count
            FROM questions_tbl
            LEFT JOIN responses_tbl ON questions_tbl.question_id = responses_tbl.question_id
            WHERE $condition
            GROUP BY questions_tbl.question_id
            ORDER BY questions_tbl.question_id;
        ";
        
        $data = [];
        $errmsg = "";
        $code = 0;
        
        try {
            $stmt = $this->pdo->prepare($sqlString);
            
            if ($startDate && $endDate) {
                $stmt->bindParam(':start_date', $startDate);
                $stmt->bindParam(':end_date', $endDate);
            }
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result) {
                $data = $result;
                $code = 200;
            } else {
                $errmsg = "No questions found within the given date range.";
                $code = 404;
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 500;
        }
        
        return $this->generateResponse($data, $errmsg ? "failed" : "success", $errmsg ? $errmsg : "Successfully retrieved question analytics.", $code);
    }
    
        
}

?>