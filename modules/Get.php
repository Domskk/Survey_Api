<?php
include_once "Common.php";

// class Get extends Common {
//     protected $pdo;

//     public function __construct(\PDO $pdo) {
//         $this->pdo = $pdo;
//     }
//     public function getAllUsers() {
//         $sqlString = "SELECT * FROM users_tbl";
//         $data = [];
//         $errmsg = "";
//         $code = 0;
    
//         try {
//             $stmt = $this->pdo->prepare($sqlString);
//             $stmt->execute();
    
//             $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
//             if ($result) {
//                 $data = $result;
//                 $code = 200;
//             } else {
//                 $errmsg = "No users found.";
//                 $code = 404;
//             }
//         } catch (\PDOException $e) {
//             $errmsg = $e->getMessage();
//             $code = 500;
//         }
//         return $this->generateResponse($data, $errmsg ? "failed" : "success", $errmsg ? $errmsg : "Successfully retrieved users.", $code);
//     }
    
    

//     public function getSurveys($surveyId = null) {
//         $condition = "1=1";
//         if ($surveyId) {
//             $condition .= " AND survey_id = " . $surveyId;
//         }

//         $result = $this->getDataByTable('surveys_tbl', $condition, $this->pdo);
//         if ($result['code'] == 200) {
//             return $this->generateResponse($result['data'], "success", "Successfully retrieved surveys.", $result['code']);
//         }
//         return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
//     }

//     public function getQuestions($questionId = null) {
//         $condition = "1=1";
//         if ($questionId) {
//             $condition .= " AND question_id = " . $questionId;
//         }
//         $result = $this->getDataByTable('questions_tbl', $condition, $this->pdo);

//         if ($result['code'] == 200) {
//             return $this->generateResponse($result['data'], "success", "Successfully retrieved questions.", $result['code']);
//         }
//         return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
//     }


//     public function getResponses($responseId = null) {
//         $condition = "1=1";
//         if ($responseId) {
//             $condition .= " AND response_id = " . $responseId;
//         }
//         $result = $this->getDataByTable('responses_tbl', $condition, $this->pdo);

//         if ($result['code'] == 200) {
//             return $this->generateResponse($result['data'], "success", "Successfully retrieved responses.", $result['code']);
//         }
//         return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
//     }
// }

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
            $condition .= " AND responses_tbl.response_id = ?";
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