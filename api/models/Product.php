<?php

if($_SERVER['HTTP_HOST'] == DB_HOST_1) {
    require_once 'api/db.php';
} else {
    require_once 'db.php';
}

class Course {        
    /**
     * getAllCourses
     * 
     * @param  array $queryParams
     * @return void
     */
    public function getAllCourses($queryParams = []) {
        $db = DB::getInstance();
        $sql = "SELECT * FROM courses";
        $result = handleQueryParams($sql, $queryParams, $db);
        return $result;
    }
    
    /**
     * getCourseByCode - given a course code, return list of courses and their prerequisites and credit amount
     *
     * @param  string $code - The course code you are trying to find
     * @return void $ A list of all 
     */
    public function getCourseByCode($code, $queryParams = []) {
        $db = DB::getInstance();
        $sql = sprintf("SELECT * FROM courses WHERE code='%s'", $code);
        $result = handleQueryParams($sql, $queryParams, $db);
        return $result;
    }
        
    /**
     * createCourse
     *
     * @param  mixed $name
     * @param  mixed $description
     * @return void
     */
    public function createCourse($courseData) {
        $db = DB::getInstance();

        $code = $courseData['code'];
        $credits = $courseData['credits'];
        $title = $courseData['title'];
        $offered = $courseData['offered'];
        $prerequisites = $courseData['prerequisites'] ?? "None";

        if ($code === null) {
            throw new Exception(handleError(COURSE_UPDATE_NO_VALUES, 400, "Please provide course code"));
        }

        $existingCourse = $this->getCourseByCode($code);

        if ($existingCourse && count($existingCourse) > 0) {
            throw new Exception(handleError(DUPLICATE_COURSE_ERROR, 409));  
        }

        // Course doesn't exist, proceed with the insertion
        $insertSql = sprintf("INSERT INTO courses (code, title, prerequisites, offered, credits) VALUES ('%s', '%s', '%s', '%s', '%s');", $code, $title, $prerequisites, $offered, $credits);

        // Attempt to execute the query
        $result = $db->update($insertSql);
        
        if (!$result) {
            throw new Exception(handleError(COURSE_CREATE_ERROR, 500, $db->error));  
        } 

        // Return new resource
        return $this->getCourseByCode($code);
    }
    
    /**
     * updateCourse - given a courseCode, update the course object in the database
     *
     * @param  mixed $id - the given course code that will be updated
     * @param  mixed $code - new course code
     * @param  mixed $credits - new credit value
     * @param  mixed $prerequisites - new prerequisites string
     * @return string - message depicting result
     */
    public function updateCourse($id, $courseData) {
        $db = DB::getInstance();
        $credits = $courseData['credits'] ?? null;
        $prerequisites = $courseData['prerequisites'] ?? null;
        $title = $courseData['title'] ?? null;
        $offered = $courseData['offered'] ?? null;

        $existingCourse = $this->getCourseByCode($id);
        
        if(!$existingCourse && empty($existingCourse)) {
            throw new Exception(handleError(COURSE_NOT_FOUND, 404));  
        }

        // No values provided to be updated
        if ($credits === null && $prerequisites === null && $offered === null && $title === null) {
            throw new Exception(handleError(COURSE_UPDATE_NO_VALUES, 400));
        }

        // Non-numeric credits check
        if ($credits && !is_numeric($credits)) { 
            throw new Exception(handleError(INVALID_INPUT_ERROR, 400, "Credits should be numberic"));
        }
        
        // Negative Credits
        if ($credits && $credits <= 0) { 
            throw new Exception(handleError(INVALID_INPUT_ERROR, 400, "Credits should be a positive number"));
        }

        // Course exists, proceed with the update
        $updateSql = "UPDATE courses SET";

        if (!empty($credits)) {
            $updateSql .= " credits = '$credits',";
        }
        if (!empty($prerequisites)) {
            $updateSql .= " prerequisites = '$prerequisites',";
        }
        if (!empty($title)) {
            $updateSql .= " title = '$title',";
        }
        if (!empty($offered)) {
            $updateSql .= " offered = '$offered',";
        }
        $updateSql = rtrim($updateSql, ',');

        $updateSql .= " WHERE code = '$id';";
        $result = $db->update($updateSql);

        if (!$result) {
            throw new Exception(handleError(COURSE_UPDATE_ERROR, 500, $db->error));  
        } 

        // Return updated resource
        return $this->getCourseByCode($id);
    }

    /**
     * deleteCourse - given a courseCode, remove from courses table in db
     *
     * @param  mixed $id - the given course code that will be deleted
     * @return void
     */
    public function deleteCourse($id) {
        $db = DB::getInstance();

        $existingCourse = $this->getCourseByCode($id);
        
        if(!$existingCourse && empty($existingCourse)) {
            throw new Exception(handleError(COURSE_NOT_FOUND, 404));  
        }

        $updateSql = sprintf("DELETE FROM courses WHERE code='%s';", $id);
        $result = $db->update($updateSql);

        if (!$result) {
            throw new Exception(handleError(COURSE_UPDATE_ERROR, 500, $db->error));  
        } 

        return $result;
    }
}

?>
