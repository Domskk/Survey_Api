<?php
class Delete {

protected $pdo;

public function __construct(\PDO $pdo) {
    $this->pdo = $pdo;
}

public function deleteSurvey($survey_id) {
    $errmsg = "";
    $code = 0;

    try {
        $this->pdo->beginTransaction();

        $delete_responses_sql = "DELETE FROM responses_tbl WHERE question_id IN (SELECT question_id FROM questions_tbl WHERE survey_id = ?)";
        $delete_responses = $this->pdo->prepare($delete_responses_sql);
        $delete_responses->execute([$survey_id]);

        $delete_questions_sql = "DELETE FROM questions_tbl WHERE survey_id = ?";
        $delete_questions = $this->pdo->prepare($delete_questions_sql);
        $delete_questions->execute([$survey_id]);

        $delete_survey_sql = "DELETE FROM surveys_tbl WHERE survey_id = ?";
        $delete_survey = $this->pdo->prepare($delete_survey_sql);
        $delete_survey->execute([$survey_id]);

        $this->pdo->commit();

        return ["data" => null, "message" => "Survey deleted successfully.", "code" => 200];
    } catch (\PDOException $e) {
        $this->pdo->rollBack();
        return ["errmsg" => $e->getMessage(), "code" => 400];
    }
}

public function deleteUser($user_id) {
    $errmsg = "";
    $code = 0;

    try {
        $this->pdo->beginTransaction();

        $delete_responses_sql = "DELETE FROM responses_tbl WHERE user_id = ?";
        $delete_responses = $this->pdo->prepare($delete_responses_sql);
        $delete_responses->execute([$user_id]);

        $delete_user_sql = "DELETE FROM users_tbl WHERE user_id = ?";
        $delete_user = $this->pdo->prepare($delete_user_sql);
        $delete_user->execute([$user_id]);

        $this->pdo->commit();

        return ["data" => null, "message" => "User deleted successfully.", "code" => 200];
    } catch (\PDOException $e) {
        $this->pdo->rollBack();
        return ["errmsg" => $e->getMessage(), "code" => 400];
    }
}
}
?>