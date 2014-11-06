<?php

namespace Intern;

/**
 * TestStudentProvider - Always returns student objects with hard-coded testing data
 *
 * @author Jeremy Booker
 * @package Intern
 */
class TestStudentProvider extends BannerStudentProvider {

    /**
     * Returns a Student object with hard-coded data
     * @return Student
     */
    public function getStudent($studentId)
    {
        $student = new Student();

        $response = $this->getFakeResponse();

        $this->plugValues($student, $response);

        return $student;
    }

    private function getFakeResponse()
    {
        $obj = new \stdClass();

        $obj->banner_id = '900123456';
        $obj->user_name = 'jb67803';
        $obj->email = 'jb67803@appstate.edu';

        // Basic demographics
        $obj->first_name = 'Jeremy';
        $obj->last_name = 'Booker';
        $obj->middle_name = 'Awesome';
        $obj->preferred_name = 'j-dogg';
        $obj->gender = 'M';
        $obj->birth_date = '6/20/1995';

        // Contact info
        $obj->phone = '9192748035';

        // Academic Info
        $obj->level     = 'U'; // 'U' or 'G'
        $obj->campus    = BannerStudentProvider::MAIN_CAMPUS; // TODO verify values in SOAP
        $obj->gpa       = '3.75';

        return $obj;
    }
}

?>